<?php

use Sugarcrm\Sugarcrm\Cache\Exception;

require_once('custom/modules/ProspectLists/logic_hook/import_contacts.php');
require_once('custom/modules/ProspectLists/logic_hook/import_accounts.php');
require_once('custom/modules/ProspectLists/logic_hook/import_leads.php');
require_once("modules/TL_ActiveCampaigns/AcConstants.php");
require_once('modules/TL_ActiveCampaigns/license/SugarActiveOutfittersLicense.php');

class ManageRelations
{
    public function start_process($bean, $event, $arguments)
    {
        global $current_user;
        $currentModule = "TL_ActiveCampaigns";
        $validate_license = SugarActiveOutfittersLicense::isValid($currentModule, $current_user->id, false);

        if ($validate_license === true) {
            $Administration = new Administration();
            $focus = $Administration->retrieveSettings();
            $ac_list_ids = $this->is_tl_mapped_with_ac_list($focus, $bean->id);

            if ($ac_list_ids['new_sync']) {
                if ($ac_list_ids['mapped']) {
                    if (isset($bean->contacts) && !empty($bean->contacts)) {
                        $this->import_in_ac_lists($ac_list_ids['mapped'], $bean, $arguments);
                    } else if (isset($bean->accounts) && !empty($bean->accounts)) {
                        $this->import_in_ac_lists($ac_list_ids['mapped'], $bean, $arguments);
                    } else if (isset($bean->leads) && !empty($bean->leads)) {
                        $this->import_in_ac_lists($ac_list_ids['mapped'], $bean, $arguments);
                    }
                } else {
                    $GLOBALS['log']->debug("'$bean->name' Target list is not mapped with any one list of Active Campaign. " . basename(__FILE__) . ":" . __LINE__);
                }
            } else {
                $GLOBALS['log']->fatal("Syncing New Records is not enabled for target list '$bean->name'. " . basename(__FILE__) . ":" . __LINE__);
            }
        }
    }

    private function is_tl_mapped_with_ac_list($focus, $tl_id)
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

    private function is_already_imported($ac_list_id, $crm_tl_id, $related_id, $related_module)
    {
        global $db;
        $found = false;
        $sql = "SELECT 
                    id
                FROM
                " . AcConstants::AC_MIGRATION_STATS_TABLE . "
                WHERE
                    ac_list_id = '$ac_list_id' AND crm_tl_id = '$crm_tl_id'
                        AND related_id = '$related_id'
                        AND related_module = '$related_module'";
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

    private function import_in_ac_lists($ac_list_ids, $bean, $arguments)
    {
        $related_id = $arguments['related_id'];
        $related_module = $arguments['related_module'];
        $obj = null;
        if (strtolower($related_module)  == "contacts") {
            $contact = new Contact();
            $contact->retrieve($related_id);
            if($contact && !empty($contact->active_campaign_id_c)){
                $obj = new ImportContacts($arguments);
            }
        } else if (strtolower($related_module)  == "accounts") {
            $account = new Account();
            $account->retrieve($related_id);
            if($account && !empty($account->active_campaign_id_c)){
                $obj = new ImportAccounts($arguments);
            }
        } else if (strtolower($related_module)  == "leads") {
            $lead = new Lead();
            $lead->retrieve($related_id);
            if($lead && !empty($lead->active_campaign_id_c)){
                $obj = new ImportLeads($arguments);
            }
        }
        if($obj !== null){
            foreach ($ac_list_ids as $ac_list_id) {
                $is_imported = $this->is_already_imported($ac_list_id, $bean->id, $related_id, $related_module);
                if (!$is_imported) {
                    $api_creds = $this->get_ac_api_key_url();
                    if ($api_creds) {
                        $obj->import_to_ac($api_creds, $ac_list_id, $bean->id, $bean->name);
                    } else {
                        $GLOBALS['log']->fatal("API KEY and API URL not found. " . basename(__FILE__) . ":" . __LINE__);
                    }
                } else {
                    $GLOBALS['log']->fatal("Contact already exists in Active Campaign. " . basename(__FILE__) . ":" . __LINE__);
                }
            }
            return true;
        }
        return false;
    }
}
