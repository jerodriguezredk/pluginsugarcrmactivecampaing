<?php

use Sugarcrm\Sugarcrm\Cache\Exception;

require_once("modules/TL_ActiveCampaigns/AcConstants.php");
require_once('modules/TL_ActiveCampaigns/ActiveCampaign_class.php');
require_once('modules/TL_ActiveCampaigns/license/SugarActiveOutfittersLicense.php');

class ImportAccounts
{
    private $account_bean = array();
    function __construct($args)
    {
        global $current_user;
        $currentModule = "TL_ActiveCampaigns";
        $validate_license = SugarActiveOutfittersLicense::isValid($currentModule, $current_user->id, false);

        if ($validate_license === true) {
            $this->get_account_beans($args);
        }
    }

    public function import_to_ac($api_creds, $ac_list_id, $crm_tl_id, $crm_tl_name)
    {
        $primary_email = $this->account_bean->emailAddress->getPrimaryAddress($this->account_bean);
        if ($primary_email) {
            $account_data = $this->get_account_data($ac_list_id, $primary_email);
            if ($account_data) {
                $data_to_import = json_encode($account_data);
                try {
                    $campaign_obj = new ActiveCampaign_class();
                    $output = $campaign_obj->create_campaign_contact($account_data);

                    if (isset($output["result_code"]) && (int) $output["result_code"]) {
                        $this->save_stats($ac_list_id, $output["subscriber_id"], $crm_tl_name, $crm_tl_id, $this->account_bean->id, $this->account_bean->module_dir);
                        $this->account_bean->active_campaign_id_c = $output["subscriber_id"];
                        $this->account_bean->save();
                    } else {
                        $GLOBALS['log']->fatal("Unable to import account in active campaign. " . basename(__FILE__) . ":" . __LINE__);
                        $GLOBALS['log']->fatal($output["error"] ?? "Something went wrong while syncing active campaign accounts. " . basename(__FILE__) . ":" . __LINE__);
                        $GLOBALS['log']->fatal(print_r($output, true));

                    }
                } catch (Exception $e) {
                    $GLOBALS['log']->fatal('ActiveCampaign Exception: ' . basename(__FILE__) . ":" . __LINE__);
                    $GLOBALS['log']->fatal($e->getMessage());
                }
            } else {
                $GLOBALS['log']->fatal("account data not found for active campaign. " . basename(__FILE__) . ":" . __LINE__);
            }
        } else {
            $GLOBALS['log']->fatal("Email of account is not fond. " . basename(__FILE__) . ":" . __LINE__);
        }
        return true;
    }

    private function get_account_data($ac_list_id, $primary_email)
    {
        $account_data = array();
        $custom_fileds = array();

        $f_mapped = $this->get_account_fields_mappings();
        if ($f_mapped) {
            $frmDec = urldecode($f_mapped);
            $field_mapping_array = unserialize($frmDec);

            foreach ($field_mapping_array as $key => $info) {
                if ($key == "email") {
                    $account_data += array(
                        $key => $primary_email
                    );
                } elseif ($key == "last_name") {
                    $account_data += array(
                        $key => $this->account_bean->name
                    );
                } elseif ($key == "first_name") {
                    $account_data += array(
                        $key => $this->account_bean->name
                    );
                } elseif ($key == "phone") {
                    $account_data += array(
                        $key => $this->account_bean->phone_office
                    );
                } else {
                    if ($key != AcConstants::CRM_SOURCE && $key != AcConstants::CRM_RECORD_ID) {
                        $account_data += array(
                            "field[%$key%,0]" => $this->account_bean->{$info},
                        );
                    }
                }
            }
            $account_data += array(
                        "field[%" . AcConstants::CRM_SOURCE . "%,0]" => $this->account_bean->module_dir,
                        "field[%" . AcConstants::CRM_RECORD_ID . "%,0]" => $this->account_bean->id,
                    );
            
            $account_data += array(
                "p[$ac_list_id]"      => (int) $ac_list_id,
                "status[$ac_list_id]" => 1,
            );
        } else {
            $GLOBALS['log']->fatal("Account fields mapping with active field mapping not found. " . basename(__FILE__) . ":" . __LINE__);
        }
        return $account_data;
    }


    private function get_account_beans($args)
    {
        $obj = new Account();
        $obj->retrieve($args['related_id']);
        $this->account_bean = $obj;
    }

    private function get_account_fields_mappings()
    {
        $val = "";
        $Administration = new Administration();
        $focus = $Administration->retrieveSettings();
        if (isset($focus->settings['TL_ActiveCampaigns_account_mapping_array']) && !empty($focus->settings['TL_ActiveCampaigns_account_mapping_array'])) {
            $val = (string) $focus->settings['TL_ActiveCampaigns_account_mapping_array'];
        }
        return $val;
    }

    private function save_stats($ac_list_id, $ac_contact_id, $crm_tl_name, $crm_tl_id, $record_id, $module_name)
    {
        try {
            global $db;
            $sql = "INSERT INTO " . AcConstants::AC_MIGRATION_STATS_TABLE . " (ac_list_id, ac_contact_id, crm_tl_name,crm_tl_id,related_id,related_module)
            VALUES ('$ac_list_id','$ac_contact_id','$crm_tl_name','$crm_tl_id','$record_id','$module_name');";
            $inserted = $db->query($sql);
            if ($inserted) {
                //$GLOBALS['log']->fatal("Inserted in stats");
            } else {
                $GLOBALS['log']->fatal("Unable to insert in Stats table. " . basename(__FILE__) . ":" . __LINE__);
            }
        } catch (Exception $e) {
            $GLOBALS['log']->fatal("ActiveCampaign Exception: " . basename(__FILE__) . ":" . __LINE__);
            $GLOBALS['log']->fatal($e->getMessage());
        }
        return true;
    }
}
