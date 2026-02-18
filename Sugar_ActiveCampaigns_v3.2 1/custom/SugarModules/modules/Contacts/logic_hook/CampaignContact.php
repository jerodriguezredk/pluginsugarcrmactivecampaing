<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once('modules/TL_ActiveCampaigns/ActiveCampaign_class.php');
require_once('modules/TL_ActiveCampaigns/import_to_ac_after_save.php');
require_once('modules/TL_ActiveCampaigns/license/SugarActiveOutfittersLicense.php');
require_once('modules/TL_ActiveCampaigns/PluginLogger.php');

class CampaignContact
{
    function save_contact($bean, $event, $arguments)
    {
        global $current_user;
        $currentModule = "TL_ActiveCampaigns";
        $validate_license = SugarActiveOutfittersLicense::isValid($currentModule, $current_user->id, false);
        if ($validate_license === true) {
            PluginLogger::log(" <----> Save Contact Event Triggered for " . $bean->name . "(" . $bean->id . ") <----> ");
            $field_mapping_array = [];
            $contacts_sync = [];
            $new_contacts_sync = false;
            $from_target_list = false;
            $ac_list_id = [];
            $Administration = new Administration();
            $focus = $Administration->retrieveSettings();
            if (isset($bean->new_rel_relname) && $bean->new_rel_relname == "prospect_lists") {
                $obj = new ImportToAcAfterSave();
                $contacts_sync = $obj->is_new_sync_on_in_tl($focus, $bean->new_rel_id);
                if (isset($contacts_sync['new_sync']) && filter_var($contacts_sync['new_sync'], FILTER_VALIDATE_BOOLEAN)) {
                    $from_target_list = true;
                    $new_contacts_sync = true;
                }
            } else {
                $new_contacts_sync = $this->is_new_sync_on($focus);
            }
            if ($new_contacts_sync) {
                if (isset($bean->fetched_row['id']) && $bean->id !== $bean->fetched_row['id'] && !empty($bean->active_campaign_id_c)) { //New Record creating in SugarCRM which is creating via Active Campaign
                    //Its new Record in SugarCRM which is coming from ActiveCampaign
                } else if (!isset($bean->fetched_row) || (is_array($bean->fetched_row) && !isset($bean->fetched_row['id'])) || (isset($bean->fetched_row['id']) && $bean->id !== $bean->fetched_row['id'] && empty($bean->active_campaign_id_c))) {  //New Record creating in SugarCRM which is not yet synced to Active Campaign
                    $field_mapping_array = $this->contact_mapped_array($focus);
                    if ($from_target_list) {
                        $ac_list_id = $contacts_sync['mapped'] ?? [];
                    } else {
                        $ac_list_id = $this->maped_ac_list_id($focus);
                    }
                    if (is_array($field_mapping_array) && count($field_mapping_array) > 0) {
                        $primary_email = $bean->emailAddress->getPrimaryAddress($bean);
                        if ($primary_email) {
                            if (!empty($ac_list_id) && is_array($ac_list_id)) {
                                foreach ($ac_list_id as $ac_lict_id) {
                                    $contact_data = $this->prepare_contact_data($field_mapping_array, $primary_email, $bean, $ac_lict_id);
                                    $campaign_obj = new ActiveCampaign_class();
                                    $contact_sync = $campaign_obj->create_campaign_contact($contact_data);
                                    if (isset($contact_sync["result_code"]) && (int)$contact_sync["result_code"]) {
                                        $bean->active_campaign_id_c = $contact_sync["subscriber_id"];
                                        $bean->sync_status_c = true;
                                        PluginLogger::log("Contact is created in Active Campaign with ID: " . $bean->active_campaign_id_c);
                                    } else {
                                        PluginLogger::log($contact_sync["error"] ?? "Something went wrong while creating active campaign contact ");
                                        PluginLogger::log($contact_sync);
                                    }
                                }
                            } else {
                                PluginLogger::log("Active Campaign Lists is not mapped for contacts");
                            }
                        } else {
                            PluginLogger::log("Primary Email of contact is not found.");
                        }
                    } else {
                        PluginLogger::log("Active Campaign fields have not mapped with CRM Contact fields.");
                    }
                } else if ($bean->id == $bean->fetched_row['id']) { //Existing Record in SugarCRM which is already synced to Active Campaign
                    global $timedate;
                    $CurrenrDateTime = $timedate->getInstance()->nowDb();
                    $date_modified = $bean->fetched_row['date_modified'];
                    $check = strtotime($CurrenrDateTime) - strtotime($date_modified);
                    $diff = ceil($check / 60);
                    $diff = abs($diff);
                    if ($diff > 5) { //If the contact is updated after 10 minutes of the last update
                        $field_mapping_array = $this->contact_mapped_array($focus);
                        $ac_list_id = $this->maped_ac_list_id($focus);
                        if (is_array($field_mapping_array) && count($field_mapping_array) > 0) {
                            $primary_email = $bean->emailAddress->getPrimaryAddress($bean);
                            if ($primary_email) {
                                if (!empty($ac_list_id) && is_array($ac_list_id)) {
                                    foreach ($ac_list_id as $ac_lict_id) {
                                        $contact_data = $this->prepare_contact_data($field_mapping_array, $primary_email, $bean, $ac_lict_id);
                                        $campaign_obj = new ActiveCampaign_class();
                                        $contact_sync = $campaign_obj->create_campaign_contact($contact_data);
                                        if (isset($contact_sync["result_code"]) && (int)$contact_sync["result_code"]) {
                                            $subscriber_id = $contact_sync["subscriber_id"];
                                            if(!empty($bean->active_campaign_id_c)){
                                                PluginLogger::log("Contact is updated in Active Campaign with ID: " . $subscriber_id);
                                            }else{
                                                PluginLogger::log("Contact is created in Active Campaign with ID: " . $subscriber_id);
                                            }
                                            $bean->active_campaign_id_c = $subscriber_id;
                                            $bean->sync_status_c = true;
                                        } else {
                                            PluginLogger::log($contact_sync["error"] ?? "Something went wrong while creating active campaign contact. ");
                                            PluginLogger::log($contact_sync);
                                        }
                                    }
                                } else {
                                    PluginLogger::log("Active Campaign Lists is not mapped for contacts");
                                }
                            } else {
                                PluginLogger::log("Primary Email of Contact is not found.");
                            }
                        } else {
                            PluginLogger::log("Active Campaign fields have not mapped with CRM Contact fields.");
                        }
                    } else {
                        PluginLogger::log("Contact is not updated in Active Campaign as it is updated within 5 minutes of last update. ");
                    }
                } else {
                    PluginLogger::log("Something went wrong. Unable to identify the contact record as a new or existing.");
                }
            } else {
                PluginLogger::log("New Contact Syncing is not turned ON.");
            }
            PluginLogger::log(" <--------> ");
        } else {
            PluginLogger::log('License validation failed while contact syncing');
        }
    }

    private function prepare_contact_data($field_mapping_array, $primary_email, $bean, $ac_list_id)
    {
        $contact_data = array();
        foreach ($field_mapping_array as $key => $info) {
            if ($key == "email") {
                $contact_data += array(
                    $key => $primary_email
                );
            } elseif ($key == "last_name") {
                $contact_data += array(
                    $key => $bean->last_name
                );
            } elseif ($key == "first_name") {
                $contact_data += array(
                    $key => $bean->first_name
                );
            } elseif ($key == "phone") {
                $contact_data += array(
                    $key => $bean->phone_mobile
                );
            } else {
                if ($key != AcConstants::CRM_SOURCE && $key != AcConstants::CRM_RECORD_ID) {
                    $contact_data += array(
                        "field[%$key%,0]" => $bean->{$info},
                    );
                }
            }
        }
        $contact_data += array(
            "field[%" . AcConstants::CRM_SOURCE . "%,0]" => $bean->module_dir,
            "field[%" . AcConstants::CRM_RECORD_ID . "%,0]" => $bean->id,
        );
        if ($ac_list_id != '') {
            $contact_data += array(
                "p[{$ac_list_id}]" => $ac_list_id,
                "status[{$ac_list_id}]" => 1,
            );
        }
        return $contact_data;
    }

    private function is_new_sync_on($focus)
    {
        $return = false;
        if (isset($focus->settings['TL_ActiveCampaigns_contacts_new_sync']) && $focus->settings['TL_ActiveCampaigns_contacts_new_sync'] == "on") {
            $return = true;
        }
        return $return;
    }

    private function contact_mapped_array($focus)
    {
        $return = [];
        if (isset($focus->settings['TL_ActiveCampaigns_contact_mapping_array']) && !empty($focus->settings['TL_ActiveCampaigns_contact_mapping_array'])) {
            $val = (string)$focus->settings['TL_ActiveCampaigns_contact_mapping_array'];
            $frmDec = urldecode($val);
            $return = unserialize($frmDec);
        }
        return $return;
    }

    public function delete_contact_from_ac($bean, $event, $arguments)
    {
        $api_key = '';
        $api_url = '';
        if (isset($bean->active_campaign_id_c) && !empty($bean->active_campaign_id_c)) {
            $Administration = new Administration();
            $focus = $Administration->retrieveSettings();
            if (isset($focus->settings['TL_ActiveCampaigns_api_url'])) {
                $api_url = $focus->settings['TL_ActiveCampaigns_api_url'];
            }
            if (isset($focus->settings['TL_ActiveCampaigns_api_key'])) {
                $api_key = $focus->settings['TL_ActiveCampaigns_api_key'];
            }
            if ($api_url && $api_key) {
                $campaign_obj = new ActiveCampaign_class();
                $del = $campaign_obj->post_data("api/3/contacts/$bean->active_campaign_id_c", "", 'DELETE');
                if (!$del['response']) {
                    PluginLogger::log("Contact is deleted from active campaign. ");
                } else {
                    PluginLogger::log("Unable to delete contact from active campaign. ");
                }
            } else {
                PluginLogger::log("API Credentials not found. ");
            }
        } else {
            PluginLogger::log("Active campaign id not found. ");
        }
    }

    public function link_to_active_campaign($bean, $event, $arguments)
    {
        if (strpos($_SERVER['REQUEST_URI'], '/ProspectLists/') !== false && strpos($_SERVER['REQUEST_URI'], '/contact') !== false) {
            $obj = new ImportToAcAfterSave();
            $obj->save_to_active_campaign($bean, $bean->module_dir);
        }
    }

    private function maped_ac_list_id($focus)
    {
        $return = array();
        if (isset($focus->settings['TL_ActiveCampaigns_contacts_sync']) && !empty($focus->settings['TL_ActiveCampaigns_contacts_sync'])) {
            $return[] = $focus->settings['TL_ActiveCampaigns_contacts_sync'];
        }
        return $return;
    }
}