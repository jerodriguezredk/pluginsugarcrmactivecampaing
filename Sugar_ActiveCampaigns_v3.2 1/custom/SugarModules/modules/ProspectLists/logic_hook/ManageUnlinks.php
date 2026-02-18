<?php

use Sugarcrm\Sugarcrm\Cache\Exception;

require_once("modules/TL_ActiveCampaigns/AcConstants.php");
require_once('modules/TL_ActiveCampaigns/ActiveCampaign_class.php');
require_once('modules/TL_ActiveCampaigns/license/SugarActiveOutfittersLicense.php');

class ManageUnlinks
{
    public function start_process($bean, $event, $arguments)
    {
        global $current_user;
        $currentModule = "TL_ActiveCampaigns";
        $validate_license = SugarActiveOutfittersLicense::isValid($currentModule, $current_user->id, false);

        if ($validate_license === true) {
            $Administration = new Administration();
            $focus = $Administration->retrieveSettings();
            $ac_list_ids = $this->ac_lists_mapped_with($focus, $bean->id);
            if ($ac_list_ids) {
                if (isset($bean->contacts) || isset($bean->accounts) || isset($bean->leads)) {
                    $this->unlink_from_ac_lists($ac_list_ids, $arguments);
                }

                if ($arguments['related_module'] == "Users" && $arguments['link'] == "") {
                    $target_list_is = $arguments['id'];
                    $mapped_list = $focus->settings['TL_ActiveCampaigns_target_list_mapped'];
                    $ac_lists_mapped = $focus->settings['TL_ActiveCampaigns_ac_lists_mapped'];
                    $existing_sync = $focus->settings['TL_ActiveCampaigns_existing_sync'];
                    $new_sync = $focus->settings['TL_ActiveCampaigns_new_sync'];

                    if (($key = array_search($target_list_is, $mapped_list)) !== false) {
                        array_splice($mapped_list, $key, 1);
                        array_splice($ac_lists_mapped, $key, 1);
                        array_splice($existing_sync, $key, 1);
                        array_splice($new_sync, $key, 1);
                    }

                    $Administration->saveSetting("TL_ActiveCampaigns", "target_list_mapped", $mapped_list);
                    $Administration->saveSetting("TL_ActiveCampaigns", "ac_lists_mapped", $ac_lists_mapped);
                    $Administration->saveSetting("TL_ActiveCampaigns", "existing_sync", $existing_sync);
                    $Administration->saveSetting("TL_ActiveCampaigns", "new_sync", $new_sync);
                }
            } else {
                $GLOBALS['log']->fatal("'$bean->name' Target list is not mapped with any one list of Active Campaign. " . basename(__FILE__) . ":" . __LINE__);
            }
        }
    }

    private function ac_lists_mapped_with($focus, $tl_id)
    {
        $ac_mapped = array();
        if (isset($focus->settings['TL_ActiveCampaigns_target_list_mapped']) && isset($focus->settings['TL_ActiveCampaigns_ac_lists_mapped'])) {
            $mapped_list = $focus->settings['TL_ActiveCampaigns_target_list_mapped'];
            $ac_lists_mapped = $focus->settings['TL_ActiveCampaigns_ac_lists_mapped'];
            foreach ($mapped_list as $key => $val) {
                if ($val == $tl_id) {
                    if (isset($ac_lists_mapped[$key])) {
                        foreach ($ac_lists_mapped[$key] as $ac_list) {
                            $ac_mapped[] = $ac_list;
                        }
                    }
                }
            }
        }
        return $ac_mapped;
    }

    private function unlink_from_ac_lists($ac_list_ids, $arguments)
    {
        if ($ac_list_ids) {
            foreach ($ac_list_ids as $ac_list_id) {
                $api_creds = $this->get_ac_api_key_url();
                if ($api_creds) {
                    $this->unlink_record($ac_list_id, $arguments, $api_creds);
                } else {
                    $GLOBALS['log']->fatal("API KEY and API URL not found. " . basename(__FILE__) . ":" . __LINE__);
                }
            }
            return true;
        } else {
            $GLOBALS['log']->fatal("Active Campaign mapped lists, with target list '{$arguments['name']}' is not found.' " . basename(__FILE__) . ":" . __LINE__);
            return false;
        }
    }

    private function unlink_record($ac_list_id, $arguments, $api_creds)
    {
        $ac_contact = $this->get_record_from_migrated_stats($ac_list_id, $arguments);
        if ($ac_contact) {
            $data = array(
                "contactList" => array(
                    "list" => $ac_contact['ac_list_id'],
                    "contact" => $ac_contact['ac_contact_id'],
                    "status" => 2
                )
            );
            $data = json_encode($data);

            $campaign_obj = new ActiveCampaign_class();
            $res1 = $campaign_obj->post_data("api/3/contactLists",$data); 

           /* $data = json_encode($data);

            $res1 = Helper::getInstance()->post_data("api/3/contactLists", $data, $api_creds);*/
            if (isset($res1['response']['contacts'][0])) {
                $GLOBALS['log']->fatal("Record is Unlinked in Active Campaign... " . basename(__FILE__) . ":" . __LINE__);
                $this->delete_from_migrate_stats($ac_list_id, $arguments, $ac_contact['ac_list_id']);
            } else {
                $GLOBALS['log']->fatal('Unable to unlink contact in active campaign. ' . basename(__FILE__) . ":" . __LINE__);
            }
            return true;
        }
    }

    private function get_record_from_migrated_stats($ac_list_id, $arguments)
    {
        global $db;
        $return =  array();
        $tl_name = $arguments['name'];
        $tl_id = $arguments['id'];
        $related_id = $arguments['related_id'];
        $module = $arguments['related_module'];
        try {
            $sql = "select * from " . AcConstants::AC_MIGRATION_STATS_TABLE . " 
                    where ac_list_id='$ac_list_id' AND crm_tl_name='$tl_name' 
                    AND crm_tl_id='$tl_id' AND related_id='$related_id' AND related_module='$module';";
            $result = $db->query($sql);
            if ($result->num_rows > 0) {
                $return =  $db->fetchByAssoc($result);
            }
        } catch (Exception $e) {
            $GLOBALS['log']->fatal("ActiveCampaign Exception: " . basename(__FILE__) . ":" . __LINE__);
            $GLOBALS['log']->fatal($e->getMessage());
            $return =  array();
        }
        return $return;
    }

    private function delete_from_migrate_stats($ac_list_id, $arguments, $list_id)
    {
        global $db;
        $tl_name = $arguments['name'];
        $tl_id = $arguments['id'];
        $related_id = $arguments['related_id'];
        $module = $arguments['related_module'];
        $sql = "Delete from " . AcConstants::AC_MIGRATION_STATS_TABLE . " 
                where ac_list_id='$ac_list_id' AND crm_tl_name='$tl_name' 
                AND crm_tl_id='$tl_id' AND related_id='$related_id' AND 
                related_module='$module' AND ac_list_id='$list_id';";
        $result = $db->query($sql);
        if ($result) {
            //$GLOBALS['log']->fatal("Record is deleted.");
        } else {
            $GLOBALS['log']->fatal("Unable to Delete Record from '" . AcConstants::AC_MIGRATION_STATS_TABLE . "' table. " . basename(__FILE__) . ":" . __LINE__);
        }
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
}
