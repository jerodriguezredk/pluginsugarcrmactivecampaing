<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once('modules/TL_ActiveCampaigns/ActiveCampaign_class.php');
require_once('modules/TL_ActiveCampaigns/import_to_ac_after_save.php');
require_once('modules/TL_ActiveCampaigns/license/SugarActiveOutfittersLicense.php');
require_once('modules/TL_ActiveCampaigns/PluginLogger.php');

class CampaignContract
{
    function save_contract($bean, $event, $arguments)
    {
        global $current_user;
        $currentModule = "TL_ActiveCampaigns";
        $validate_license = SugarActiveOutfittersLicense::isValid($currentModule, $current_user->id, false);
        if ($validate_license === true) {
            PluginLogger::log(" <----> Save Contract Event Triggered for " . $bean->name . "(" . $bean->id . ") <----> ");
            $field_mapping_array = [];
            $contracts_sync = [];
            $new_contracts_sync = false;
            $from_target_list = false;
            $ac_list_id = [];
            $Administration = new Administration();
            $focus = $Administration->retrieveSettings();
            if (isset($bean->new_rel_relname) && $bean->new_rel_relname == "prospect_lists") {
                $obj = new ImportToAcAfterSave();
                $contracts_sync = $obj->is_new_sync_on_in_tl($focus, $bean->new_rel_id);
                if (isset($contracts_sync['new_sync']) && filter_var($contracts_sync['new_sync'], FILTER_VALIDATE_BOOLEAN)) {
                    $from_target_list = true;
                    $new_contracts_sync = true;
                }
            } else {
                $new_contracts_sync = $this->is_new_sync_on($focus);
            }
            if ($new_contracts_sync) {
                $isNew = !isset($bean->fetched_row['id']) || $bean->id !== $bean->fetched_row['id'];
                $isSynced = !empty($bean->active_campaign_id_c);
                $isExisting = $bean->id === ($bean->fetched_row['id'] ?? null);
                $is_existing_sync_on = $this->is_existing_sync_on($focus);
                global $timedate;
                $CurrentDateTime = $timedate->getInstance()->nowDb();
                $date_entered  = $bean->fetched_row['date_entered'] ?? null;
                $created_within_1m = false;
                if ($date_entered) {
                    $dateEnteredObj = new DateTime($date_entered);
                    $oneMonthAgo = new DateTime($CurrentDateTime);
                    $oneMonthAgo->modify('-1 month');
                    $created_within_1m = $dateEnteredObj >= $oneMonthAgo;
                }
                if (($isNew && !$isSynced) || ($isExisting && !$isSynced && ($is_existing_sync_on || $created_within_1m))) { //New Record creating in SugarCRM which is not yet synced to Active Campaign
                    $field_mapping_array = $this->contract_mapped_array($focus);
                    if ($from_target_list) {
                        $ac_list_id = $contracts_sync['mapped'] ?? [];
                    } else {
                        $ac_list_id = $this->maped_ac_list_id($focus);
                    }
                    if (is_array($field_mapping_array) && count($field_mapping_array) > 0) {
                        $primary_email = null;
                        if (!empty($bean->account_id)) {
                            $account = BeanFactory::getBean('Accounts', $bean->account_id);
                            if ($account && isset($account->emailAddress)) {
                                $primary_email = $account->emailAddress->getPrimaryAddress($account);
                            }
                        }
                        if ($primary_email) {
                            if (!empty($ac_list_id) && is_array($ac_list_id)) {
                                foreach ($ac_list_id as $ac_lict_id) {
                                    $contract_data = $this->contract_data_to_sync($field_mapping_array, $primary_email, $bean, $ac_lict_id);
                                    $campaign_obj = new ActiveCampaign_class();
                                    $contract_sync = $campaign_obj->create_campaign_contact($contract_data);
                                    if (isset($contract_sync["result_code"]) && (int)$contract_sync["result_code"]) {
                                        $bean->active_campaign_id_c = $contract_sync["subscriber_id"];
                                        $bean->sync_status_c = true;
                                        PluginLogger::log("Contract is created in Active Campaign with ID: " . $bean->active_campaign_id_c);
                                    } else {
                                        PluginLogger::log($contract_sync["error"] ?? "Something went wrong while creating active campaign contract.");
                                        PluginLogger::log($contract_sync);
                                    }
                                }
                            } else {
                                PluginLogger::log("Active Campaign Lists is not mapped for Contracts");
                            }
                        } else {
                            PluginLogger::log("Primary Email of Contract account is not found.");
                        }
                    } else {
                        PluginLogger::log("Active Campaign fields have not mapped with CRM Contracts fields.");
                    }
                } else if ($isExisting && $isSynced) { //Existing Record in SugarCRM which is already synced to Active Campaign
                    $field_mapping_array = $this->contract_mapped_array($focus);
                    $ac_list_id = $this->maped_ac_list_id($focus);
                    if (is_array($field_mapping_array) && count($field_mapping_array) > 0) {
                        $primary_email = $bean->emailAddress->getPrimaryAddress($bean);
                        if ($primary_email) {
                            if (!empty($ac_list_id) && is_array($ac_list_id)) {
                                foreach ($ac_list_id as $ac_lict_id) {
                                    $contract_data = $this->contract_data_to_sync($field_mapping_array, $primary_email, $bean, $ac_lict_id);
                                    $campaign_obj = new ActiveCampaign_class();
                                    $contract_sync = $campaign_obj->create_campaign_contact($contract_data);
                                    if (isset($contract_sync["result_code"]) && (int)$contract_sync["result_code"]) {
                                        $subscriber_id = $contract_sync["subscriber_id"];
                                        if(!empty($bean->active_campaign_id_c)){
                                            PluginLogger::log("Contract is updated in Active Campaign with ID: " . $subscriber_id);
                                        }else{
                                            PluginLogger::log("Contract is created in Active Campaign with ID: " . $subscriber_id);
                                        }
                                        $bean->active_campaign_id_c = $subscriber_id;
                                        $bean->sync_status_c = true;
                                    } else {
                                        PluginLogger::log($contract_sync["error"] ?? "Something went wrong while creating active campaign contract.");
                                        PluginLogger::log("Contract Data: ");
                                        PluginLogger::log($contract_data);
                                        PluginLogger::log("AC Response: ");
                                        PluginLogger::log($contract_sync);
                                    }
                                }
                            }else{
                                PluginLogger::log("Active Campaign Lists is not mapped for Contracts");
                            }
                        } else {
                            PluginLogger::log("Primary Email of Contract is not found.");
                        }
                    } else {
                        PluginLogger::log("Active Campaign fields have not mapped with CRM Contracts fields.");
                    }
                } else {
                    PluginLogger::log("Something went wrong. Unable to identify the contract record as a new or existing. Flags → " . "isNew=" . var_export($isNew, true) . ", isExisting=" . var_export($isExisting, true) . ", isSynced=" . var_export($isSynced, true) . ", sync_on=" . var_export($is_existing_sync_on, true) . ", created_within_24h=" . var_export($created_within_1m, true));
                }
            } else {
                PluginLogger::log("New Contract Syncing is not turned ON.");
            }
            PluginLogger::log(" <--------> ");
        } else {
            PluginLogger::log('License validation failed while contract syncing');
        }
    }

    private function contract_data_to_sync($field_mapping_array, $primary_email, $bean, $ac_list_id)
    {
        $contract_data = array();
        foreach ($field_mapping_array as $key => $info) {
            if ($key == "email") {
                $contract_data += array(
                    $key => $primary_email
                );
            } else {
                if ($key != AcConstants::CRM_SOURCE && $key != AcConstants::CRM_RECORD_ID) {
                    $value = $bean->{$info};
                    $fieldType = $this->get_field_type($key);
                    if(!empty($value) && ($fieldType === 'datetime' || $fieldType === 'datetimecombo' || $fieldType === 'datetimecombo2')) {
                        $timestamp = strtotime($value);
                        if ($timestamp !== false) {
                            $value = date('Y-m-d\TH:i:s', $timestamp);
                        } else {
                            PluginLogger::log("Skipped invalid datetime for Contract field '{$key}': '{$value}'");
                            continue;
                        }
                    }else if(!empty($value) && ($fieldType === 'date')){
                        $timestamp = strtotime($value);
                        if ($timestamp !== false) {
                            $value = date('Y-m-d', $timestamp);
                        } else {
                            PluginLogger::log("Skipped invalid datetime for Contract field '{$key}': '{$value}'");
                            continue;
                        }
                    } elseif ($fieldType === 'date' || $fieldType === 'datetime' || $fieldType === 'datetimecombo' || $fieldType === 'datetimecombo2') {
                        PluginLogger::log("Skipped empty date values for Contract field '{$key}'");
                        continue;
                    }
                    $contract_data += array(
                        "field[%$key%,0]" => $value,
                    );
                }
            }
        }
        $contract_data += array(
            "field[%" . AcConstants::CRM_SOURCE . "%,0]" => $bean->module_dir,
            "field[%" . AcConstants::CRM_RECORD_ID . "%,0]" => $bean->id,
        );
        if ($ac_list_id != '') {
            $contract_data += array(
                "p[{$ac_list_id}]" => $ac_list_id,
                "status[{$ac_list_id}]" => 1,
            );
        }
        return $contract_data;
    }

    private function get_field_type($field_name)
    {
        if (empty($field_name)) {
            return null;
        }
        $contract = BeanFactory::newBean('Contracts');
        $fieldDefs = $contract->getFieldDefinitions();
        if (!isset($fieldDefs[$field_name])) {
            return null;
        }
        $fieldDef = $fieldDefs[$field_name];
        $dateTypes = [
            'date',
            'datetime',
            'datetimecombo',
            'datetimecombo2',
        ];
        return $fieldDef['type'];
    }

    private function is_new_sync_on($focus)
    {
        $return = false;
        if (isset($focus->settings['TL_ActiveCampaigns_contracts_new_sync']) && $focus->settings['TL_ActiveCampaigns_contracts_new_sync'] == "on") {
            $return = true;
        }
        return $return;
    }

    private function is_existing_sync_on($focus)
    {
        $return = false;
        if (isset($focus->settings['TL_ActiveCampaigns_SchedulerForExistingContracts']) && $focus->settings['TL_ActiveCampaigns_SchedulerForExistingContracts'] == "on") {
            $return = true;
        }
        return $return;
    }

    private function contract_mapped_array($focus)
    {
        $return = array();
        if (isset($focus->settings['TL_ActiveCampaigns_contract_mapping_array']) && !empty($focus->settings['TL_ActiveCampaigns_contract_mapping_array'])) {
            $val = (string)$focus->settings['TL_ActiveCampaigns_contract_mapping_array'];
            $frmDec = urldecode($val);
            $return = unserialize($frmDec);
        }
        return $return;
    }

    public function delete_contract_from_ac($bean, $event, $arguments)
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
                    PluginLogger::log("Contract is deleted from active campaign. " . basename(__FILE__) . ":" . __LINE__);
                } else {
                    PluginLogger::log("Unable to delete Contract from active campaign. " . basename(__FILE__) . ":" . __LINE__);
                }
            } else {
                PluginLogger::log("API Credentials not found. " . basename(__FILE__) . ":" . __LINE__);
            }
        } else {
            PluginLogger::log("Active campaign id not found. " . basename(__FILE__) . ":" . __LINE__);
        }
    }

    public function link_to_active_campaign($bean, $event, $arguments)
    {
        if (strpos($_SERVER['REQUEST_URI'], '/ProspectLists/') !== false && strpos($_SERVER['REQUEST_URI'], '/contracts') !== false) {
            $obj = new ImportToAcAfterSave();
            $obj->save_to_active_campaign($bean, $bean->module_dir);
        }
    }

    private function maped_ac_list_id($focus)
    {
        $return = array();
        if (isset($focus->settings['TL_ActiveCampaigns_contracts_sync']) && !empty($focus->settings['TL_ActiveCampaigns_contracts_sync'])) {
            $return[] = $focus->settings['TL_ActiveCampaigns_contracts_sync'];
        }
        return $return;
    }
}