<?php
require_once 'modules/TL_ActiveCampaigns/ActiveCampaign_class.php';
require_once "modules/TL_ActiveCampaigns/AcConstants.php";
require_once 'modules/TL_ActiveCampaigns/license/SugarActiveOutfittersLicense.php';
require_once('modules/TL_ActiveCampaigns/PluginLogger.php');

global $current_user, $db;
$currentModule = "TL_ActiveCampaigns";
$validate_license = SugarActiveOutfittersLicense::isValid($currentModule, $current_user->id, false);

if ($validate_license === true) {
    PluginLogger::log('Proceeding with existing target lists sync.');
    $Administration = new Administration();
    $focus = $Administration->retrieveSettings();

    if (isset($focus->settings['TL_ActiveCampaigns_target_list_mapped']) && isset($focus->settings['TL_ActiveCampaigns_ac_lists_mapped'])) {
        $mapped_list = $focus->settings['TL_ActiveCampaigns_target_list_mapped'];
        $ac_lists_mapped = $focus->settings['TL_ActiveCampaigns_ac_lists_mapped'];
        $existing_sync = $focus->settings['TL_ActiveCampaigns_existing_sync'];

        foreach ($mapped_list as $key => $tl_id) {

            if (isset($existing_sync[$key])) {

                if (isset($ac_lists_mapped[$key])) {

                    $target_list_record = new ProspectList();
                    $target_list_record->retrieve($tl_id);
                    $crm_tl_name = $target_list_record->name;
                    foreach ($ac_lists_mapped[$key] as $key => $ac_list_id) {
                        ///////////create related contacts in activecampaign//////
                        if ($target_list_record->load_relationship('contacts')) {
                            $sql = "SELECT
                                        pl.*
                                    FROM
                                        prospect_lists_prospects pl
                                    WHERE
                                        pl.prospect_list_id = '$tl_id' AND
                                        pl.related_id NOT IN(
                                            SELECT
                                                related_id
                                            FROM
                                                tl_migrated_stats ms
                                            WHERE
                                                ms.ac_list_id = '$ac_list_id' AND pl.deleted = 0
                                                AND ms.related_module = 'Contacts'
                                        )
                                        AND pl.related_type = 'Contacts'
                                    LIMIT 0,300";

                            $result = $db->query($sql);
                            $data_arr = array();
                            while ($row = $result->fetch_assoc()) {
                                if ($row['related_type'] == "Contacts") {
                                    $contact = BeanFactory::retrieveBean("Contacts", $row['related_id']);
                                    if (!empty($contact)) {
                                        $contact_id = $contact->id;
                                        $primary_email = $contact->emailAddress->getPrimaryAddress($contact);
                                        if ($primary_email) {
                                            //$flag = get_db_record($contact_id, $tl_id, $ac_list_id, "Contacts");
                                            //if (!$flag) {
                                            $contact_data = mapped_contacs($contact, $ac_list_id, $primary_email, "contact");
                                            if ($contact_data) {
                                                $data_to_import = json_encode($contact_data);

                                                try {
                                                    $campaign_obj = new ActiveCampaign_class();
                                                    $output = $campaign_obj->create_campaign_contact($contact_data);
                                                    if (isset($output["result_code"]) && $output["result_code"] == '1') {
                                                        save_stats($ac_list_id, $output["subscriber_id"], $crm_tl_name, $tl_id, $contact->id, "Contacts");
                                                        $kntact = new Contact();
                                                        $kntact->retrieve($contact->id);
                                                        $kntact->active_campaign_id_c = $output["subscriber_id"];
                                                        $kntact->save();
                                                    } else {
                                                        PluginLogger::log("Unable to import contact in active campaign. " . basename(__FILE__) . ":" . __LINE__);
                                                        PluginLogger::log($output["error"] ?? "Something went wrong while syncing existing target list.");
                                                        PluginLogger::log($output);
                                                    }
                                                } catch (Exception $e) {
                                                    PluginLogger::log("ActiveCampaign Exception: " . basename(__FILE__) . ":" . __LINE__);
                                                    PluginLogger::log($e->getMessage());
                                                }
                                            }
                                            //}
                                        }
                                    }

                                }
                            }
                        }

                        /////////create related accounts in active campiagn///////
                        if ($target_list_record->load_relationship('accounts')) {

                            //$accounts = $target_list_record->accounts->getBeans();
                            $sql = "SELECT
                                        pl.*
                                    FROM
                                        prospect_lists_prospects pl
                                    WHERE
                                        pl.prospect_list_id = '$tl_id' AND
                                        pl.related_id NOT IN(
                                            SELECT
                                                related_id
                                            FROM
                                                tl_migrated_stats ms
                                            WHERE
                                                ms.ac_list_id = '$ac_list_id' AND pl.deleted = 0
                                                AND ms.related_module = 'Accounts'
                                        )
                                        AND pl.related_type = 'Accounts'
                                    LIMIT 0,300";
                            $result = $db->query($sql);
                            $data_arr = array();
                            while ($row = $result->fetch_assoc()) {
                                if ($row['related_type'] == "Accounts") {
                                    $account = BeanFactory::retrieveBean("Accounts", $row['related_id']);
                                    if (!empty($account)) {
                                        $account_id = $account->id;
                                        $primary_email = $account->emailAddress->getPrimaryAddress($account);
                                        if ($primary_email) {
                                            // $flag = get_db_record($account_id, $tl_id, $ac_list_id, "Accounts");
                                            // if (!$flag) {
                                            $account_data = mapped_accounts($account, $ac_list_id, $primary_email);
                                            if ($account_data) {

                                                try {
                                                    $campaign_obj = new ActiveCampaign_class();
                                                    $output = $campaign_obj->create_campaign_contact($account_data);
                                                    if (isset($output["result_code"]) && $output["result_code"] == '1') {
                                                        save_stats($ac_list_id, $output["subscriber_id"], $crm_tl_name, $tl_id, $account->id, "Accounts");
                                                        $aknt = new Account();
                                                        $aknt->retrieve($account->id);
                                                        $aknt->active_campaign_id_c = $output["subscriber_id"];
                                                        $aknt->save();
                                                    }else{
                                                        PluginLogger::log($output["error"] ?? "Something went wrong while syncing existing target list. " . basename(__FILE__) . ":" . __LINE__);
                                                        PluginLogger::log($output);
                                                    }
                                                } catch (Exception $e) {
                                                    PluginLogger::log("ActiveCampaign Exception: " . basename(__FILE__) . ":" . __LINE__);
                                                    PluginLogger::log($e->getMessage());
                                                }
                                            }
                                            // }
                                        }
                                    }
                                }
                            }
                        }

                        /////////create related leads in active campiagn///////
                        if ($target_list_record->load_relationship('leads')) {

                            //$leads = $target_list_record->leads->getBeans();
                            $sql = "SELECT
                                        pl.*
                                    FROM
                                        prospect_lists_prospects pl
                                    WHERE
                                    pl.prospect_list_id = '$tl_id' AND
                                        pl.related_id NOT IN(
                                            SELECT
                                                related_id
                                            FROM
                                                tl_migrated_stats ms
                                            WHERE
                                                ms.ac_list_id = '$ac_list_id' AND pl.deleted = 0
                                                AND ms.related_module = 'Leads'
                                        )
                                        AND pl.related_type = 'Leads'
                                    LIMIT 0,300";
                            $result = $db->query($sql);
                            $data_arr = array();
                            while ($row = $result->fetch_assoc()) {
                                if ($row['related_type'] == "Leads") {
                                    $lead = BeanFactory::retrieveBean("Leads", $row['related_id']);
                                    if (!empty($lead)) {
                                        $lead_id = $lead->id;
                                        $primary_email = $lead->emailAddress->getPrimaryAddress($lead);
                                        if ($primary_email) {
                                            // $flag = get_db_record($lead_id, $tl_id, $ac_list_id, "Leads");
                                            // if (!$flag) {
                                            $lead_data = mapped_contacs($lead, $ac_list_id, $primary_email, "lead");
                                            if ($lead_data) {

                                                try {
                                                    $campaign_obj = new ActiveCampaign_class();
                                                    $output = $campaign_obj->create_campaign_contact($lead_data);
                                                    if (isset($output["result_code"]) && $output["result_code"] == '1') {
                                                        save_stats($ac_list_id, $output["subscriber_id"], $crm_tl_name, $tl_id, $lead->id, "Leads");
                                                        $leed = new Lead();
                                                        $leed->retrieve($lead->id);
                                                        $leed->active_campaign_id_c = $output["subscriber_id"];
                                                        $leed->save();
                                                    }else{
                                                        PluginLogger::log($output["error"] ?? "Something went wrong while syncing existing target list.. " . basename(__FILE__) . ":" . __LINE__);
                                                        PluginLogger::log($output);
                                                    }
                                                } catch (Exception $e) {
                                                    PluginLogger::log("ActiveCampaign Exception: " . basename(__FILE__) . ":" . __LINE__);
                                                    PluginLogger::log($e->getMessage());
                                                }
                                            }
                                            // }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }else{
        PluginLogger::log('Target list mapping or ActiveCampaign lists mapping not found in settings. Skipping existing target list sync.');
    }
} else {
    PluginLogger::log('License validation failed for TL_ActiveCampaigns module. Skipping existing target list sync. And deactivating the scheduler.');
    $query = "UPDATE schedulers SET status = 'Inactive' WHERE job = 'function::CreateCampaignTargetList'";
    $db->query($query);
}

function get_ac_api_key_url()
{
    $api_creds = array();
    $Administration = new Administration();
    $focus = $Administration->retrieveSettings();
    if (
        (isset($focus->settings['TL_ActiveCampaigns_api_url']) && (!empty($focus->settings['TL_ActiveCampaigns_api_url']))) && (isset($focus->settings['TL_ActiveCampaigns_api_key']) && (!empty($focus->settings['TL_ActiveCampaigns_api_key'])))
    ) {
        $api_creds['api_key'] = $focus->settings['TL_ActiveCampaigns_api_key'];
        $api_creds['api_url'] = $focus->settings['TL_ActiveCampaigns_api_url'];
    }

    return $api_creds;
}

function save_stats($ac_list_id, $ac_contact_id, $crm_tl_name, $crm_tl_id, $record_id, $module_name)
{
    try {
        global $db;
        $sql = "INSERT INTO " . AcConstants::AC_MIGRATION_STATS_TABLE . " (ac_list_id, ac_contact_id, crm_tl_name,crm_tl_id,related_id,related_module)
        VALUES ('$ac_list_id','$ac_contact_id','$crm_tl_name','$crm_tl_id','$record_id','$module_name');";
        $inserted = $db->query($sql);
        if ($inserted) {
            //$GLOBALS['log']->fatal("Inserted in stats");
        } else {
            PluginLogger::log("Active Campaign: Unable to insert in Stats table. " . basename(__FILE__) . ":" . __LINE__);
        }
    } catch (Exception $e) {
        PluginLogger::log("ActiveCampaign Exception: " . basename(__FILE__) . ":" . __LINE__);
        PluginLogger::log($e->getMessage());
    }
    return true;
}

function mapped_contacs($contact, $ac_list_id, $primary_email, $array_index)
{
    $f_mapped = "";
    $contact_data = array();
    $custom_fileds = array();
    $Administration = new Administration();
    $focus = $Administration->retrieveSettings();
    if (isset($focus->settings['TL_ActiveCampaigns_' . $array_index . '_mapping_array']) && !empty($focus->settings['TL_ActiveCampaigns_contact_mapping_array'])) {
        $f_mapped = (string) $focus->settings['TL_ActiveCampaigns_contact_mapping_array'];
    }
    if ($f_mapped) {
        $frmDec = urldecode($f_mapped);
        $field_mapping_array = unserialize($frmDec);

        foreach ($field_mapping_array as $key => $info) {
            if ($key == "email") {
                $contact_data += array(
                    $key => $primary_email,
                );
            } elseif ($key == "last_name") {
                $contact_data += array(
                    $key => $contact->last_name,
                );
            } elseif ($key == "first_name") {
                $contact_data += array(
                    $key => $contact->first_name,
                );
            } elseif ($key == "phone") {
                $contact_data += array(
                    $key => $contact->phone_mobile,
                );
            } else {
                if ($key != AcConstants::CRM_SOURCE && $key != AcConstants::CRM_RECORD_ID) {
                    $contact_data += array(
                        "field[%$key%,0]" => $contact->{$info},
                    );
                }
            }
        }

        $contact_data += array(
            "field[%" . AcConstants::CRM_SOURCE . "%,0]" => $contact->module_dir,
            "field[%" . AcConstants::CRM_RECORD_ID . "%,0]" => $contact->id,
        );

        $contact_data += array(
            "p[$ac_list_id]" => (int) $ac_list_id,
            "status[$ac_list_id]" => 1,
        );
    } else {
        $GLOBALS['log']->fatal("Contacts fields mapping with active campaign field mapping not found. " . basename(__FILE__) . ":" . __LINE__);
    }

    return $contact_data;
}

function mapped_accounts($account, $ac_list_id, $primary_email)
{
    $f_mapped = "";
    $contact_data = array();
    $custom_fileds = array();
    $Administration = new Administration();
    $focus = $Administration->retrieveSettings();
    if (isset($focus->settings['TL_ActiveCampaigns_account_mapping_array']) && !empty($focus->settings['TL_ActiveCampaigns_account_mapping_array'])) {
        $f_mapped = (string) $focus->settings['TL_ActiveCampaigns_account_mapping_array'];
    }
    if ($f_mapped) {
        $frmDec = urldecode($f_mapped);
        $field_mapping_array = unserialize($frmDec);

        foreach ($field_mapping_array as $key => $info) {
            if ($key == "email") {
                $contact_data += array(
                    $key => $primary_email,
                );
            } elseif ($key == "last_name") {
                $contact_data += array(
                    $key => $account->name,
                );
            } elseif ($key == "first_name") {
                $contact_data += array(
                    $key => $account->name,
                );
            } elseif ($key == "phone") {
                $contact_data += array(
                    $key => $account->phone_office,
                );
            } else {
                if ($key != AcConstants::CRM_SOURCE && $key != AcConstants::CRM_RECORD_ID) {
                    $contact_data += array(
                        "field[%$key%,0]" => $account->{$info},
                    );
                }
            }
        }

        $contact_data += array(
            "field[%" . AcConstants::CRM_SOURCE . "%,0]" => $account->module_dir,
            "field[%" . AcConstants::CRM_RECORD_ID . "%,0]" => $account->id,
        );

        $contact_data += array(
            "p[$ac_list_id]" => (int) $ac_list_id,
            "status[$ac_list_id]" => 1,
        );
    } else {
        $GLOBALS['log']->fatal("Accounts fields mapping with active campaign field mapping not found. " . basename(__FILE__) . ":" . __LINE__);
    }

    return $contact_data;
}

function get_db_record($contact_id, $tl_id, $ac_list_id, $relatedModule)
{
    global $db;
    $found = false;
    $sql = "SELECT
                id
            FROM
            " . AcConstants::AC_MIGRATION_STATS_TABLE . "
            WHERE
                ac_list_id = '$ac_list_id' AND crm_tl_id = '$tl_id'
                    AND related_id = '$contact_id'
                    AND related_module = '$relatedModule'";
    $result = $db->query($sql);
    if ($result) {
        $id = $db->fetchByAssoc($result);
        if ($id['id']) {
            $found = true;
        } else {
            $found = false;
        }
    }
    return $found;
}
