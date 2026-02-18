<?php
require_once("modules/TL_ActiveCampaigns/AcConstants.php");
require_once('modules/TL_ActiveCampaigns/PluginLogger.php');

class ImportToAcAfterSave
{
    /** Link with active campaing after save login hook running */
    public function save_to_active_campaign($bean, $module)
    {
        global $current_user, $db;
        PluginLogger::log("ImportToAcAfterSave: save_to_active_campaign called for module: $module, bean id: {$bean->id}, bean name: {$bean->name}");
        $tg_id = $bean->new_rel_id;
        $tg_name = $this->name_of_tg($bean->new_rel_id);
        $ac_list_ids = $this->is_tl_mapped_with_ac_list($bean->new_rel_id);
        if ($ac_list_ids['new_sync']) {
            if ($ac_list_ids['mapped']) {
                $related_id = $bean->id;
                $related_module = $bean->module_dir;
                if (strtolower($module)  == "contacts") {
                    $obj = new ImportContacts(array('related_module' => $bean->module_dir, 'related_id' => $bean->id));
                } else if (strtolower($module)  == "accounts") {
                    $obj = new ImportAccounts(array('related_module' => $bean->module_dir, 'related_id' => $bean->id));
                } else if (strtolower($module)  == "leads") {
                    $obj = new ImportLeads(array('related_module' => $bean->module_dir, 'related_id' => $bean->id));
                } else {
                    PluginLogger::log("ActiveCampaign: calling function from unrelated module. " . basename(__FILE__) . ":" . __LINE__);
                    return;
                }

                foreach ($ac_list_ids['mapped'] as $ac_list_id) {
                    $is_imported = $this->is_already_imported($ac_list_id, $bean->id, $related_id, $related_module);
                    if (!$is_imported) {
                        $api_creds = $this->get_ac_api_key_url();
                        if ($api_creds) {
                            $obj->import_to_ac($api_creds, $ac_list_id, $tg_id, $tg_name);
                        } else {
                            PluginLogger::log("API KEY and API URL not found. " . basename(__FILE__) . ":" . __LINE__);
                        }
                    } else {
                        PluginLogger::log("Contact already exists in Active Campaign. " . basename(__FILE__) . ":" . __LINE__);
                    }
                }
            } else {
                PluginLogger::log("'$tg_name' Target list is not mapped with any one list of Active Campaign. " . basename(__FILE__) . ":" . __LINE__);
            }
        } else {
            PluginLogger::log("Syncing New Records is not enabled for target list '$tg_name'. " . basename(__FILE__) . ":" . __LINE__);
        }
    }

    private function is_tl_mapped_with_ac_list($tl_id)
    {
        $ac_mapped = array();
        $is_new_sync_on = false;
        $mapped_key = "";
        $Administration = new Administration();
        $focus = $Administration->retrieveSettings();
        if (isset($focus->settings['TL_ActiveCampaigns_target_list_mapped']) && isset($focus->settings['TL_ActiveCampaigns_ac_lists_mapped'])) {
            $mapped_list = $focus->settings['TL_ActiveCampaigns_target_list_mapped'];
            $ac_lists_mapped = $focus->settings['TL_ActiveCampaigns_ac_lists_mapped'];
            foreach ($mapped_list as $key => $val) {
                if ($val == $tl_id) {
                    $mapped_key = $key;
                    if (isset($ac_lists_mapped[$key])) {
                        foreach ($ac_lists_mapped[$key] as $ac_list) {
                            $ac_mapped[] = $ac_list;
                        }
                    }
                }
            }
            if (
                !isset($focus->settings['TL_ActiveCampaigns_new_sync'][$mapped_key]) ||
                empty($focus->settings['TL_ActiveCampaigns_new_sync'][$mapped_key])
            ) {
                $is_new_sync_on = false;
            } else {
                $is_new_sync_on = true;
            }
        }
        return array('mapped' => $ac_mapped, 'new_sync' => $is_new_sync_on);
    }

    private function name_of_tg($id)
    {
        global $db;
        $sql = "select name from prospect_lists where id='$id';";
        $result = $db->query($sql);
        $result = $db->fetchByAssoc($result);
        return $result['name'] ?? '';
    }

    private function is_already_imported($ac_list_id, $crm_tl_id, $related_id, $related_module)
    {
        global $db;
        $found = false;
        $sql = "SELECT id FROM ac_migrated_stats WHERE ac_list_id = '$ac_list_id' AND crm_tl_id = '$crm_tl_id' AND related_id = '$related_id' AND related_module = '$related_module'";
        $result = $db->query($sql);
        if ($result) {
            $id = $db->fetchByAssoc($result);
            if ($id['id']) {
                $found = true;
            }
        }
        return $found;
    }

    private function get_ac_api_key_url()
    {
        $api_creds = array();
        $Administration = new Administration();
        $this->focus = $Administration->retrieveSettings();
        if (
            (isset($this->focus->settings['TL_ActiveCampaigns_api_url']) && (!empty($this->focus->settings['TL_ActiveCampaigns_api_url']))) && (isset($this->focus->settings['TL_ActiveCampaigns_api_key']) && (!empty($this->focus->settings['TL_ActiveCampaigns_api_key'])))
        ) {
            $api_creds['api_key'] = $this->focus->settings['TL_ActiveCampaigns_api_key'];
            $api_creds['api_url'] = $this->focus->settings['TL_ActiveCampaigns_api_url'];
        }

        return $api_creds;
    }

    public function is_new_sync_on_in_tl($focus, $tl_id)
    {
        $ac_mapped = array();
        $is_new_sync_on = false;
        $mapped_key = "";
        if (isset($focus->settings['TL_ActiveCampaigns_target_list_mapped']) && isset($focus->settings['TL_ActiveCampaigns_ac_lists_mapped'])) {
            $mapped_list = $focus->settings['TL_ActiveCampaigns_target_list_mapped'];
            $ac_lists_mapped = $focus->settings['TL_ActiveCampaigns_ac_lists_mapped'];
            foreach ($mapped_list as $key => $val) {
                if ($val == $tl_id) {
                    $mapped_key = $key;
                    if (isset($ac_lists_mapped[$key])) {
                        foreach ($ac_lists_mapped[$key] as $ac_list) {
                            $ac_mapped[] = $ac_list;
                        }
                    }
                }
            }
            if (
                !isset($focus->settings['TL_ActiveCampaigns_new_sync'][$mapped_key]) ||
                empty($focus->settings['TL_ActiveCampaigns_new_sync'][$mapped_key])
            ) {
                $is_new_sync_on = false;
            } else {
                $is_new_sync_on = true;
            }
        }
        return array('mapped' => $ac_mapped, 'new_sync' => $is_new_sync_on);
    }
}
