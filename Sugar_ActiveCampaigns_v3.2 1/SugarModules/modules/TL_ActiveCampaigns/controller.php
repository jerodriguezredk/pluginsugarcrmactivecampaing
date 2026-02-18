<?php

if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once('modules/TL_ActiveCampaigns/ActiveCampaign_class.php');
require_once('modules/TL_ActiveCampaigns/license/SugarActiveOutfittersLicense.php');
require_once('modules/Schedulers/Scheduler.php');
require_once('modules/TL_ActiveCampaigns/PluginLogger.php');

class TL_ActiveCampaignsController extends SugarController
{

    public function validateLicense()
    {
        global $current_user, $currentModule, $sugar_config, $sugar_version;

        $validate_license = SugarActiveOutfittersLicense::isValid($currentModule, $current_user->id, false);
        if ($validate_license !== true) {
            PluginLogger::log('License validation failed for module: ' . $currentModule);
            if (is_admin($current_user)) {
                echo '<script type="text/javascript">
				var app = window.parent.SUGAR.App;
				app.router.navigate("#bwc/index.php?module=' . $currentModule . '&action=license", {trigger:true, replace:true});
				</script>';
            }
        }
        return true;
    }

    public function action_config()
    {
        $this->validateLicense();
        $this->view = 'setting';
    }

    public function action_store_setting()
    {
        PluginLogger::log('---Store Configuration---');
        $response = [];
        $active_campaign_obj = new ActiveCampaign_class();
        $statsTableSync = $this->create_stats_table();
        $webhookCreated = false;
        $fieldCreated = false;
        if ($statsTableSync) {
            $webhookCreated = $active_campaign_obj->create_webook();
        }
        if ($statsTableSync && $webhookCreated) {
            $fieldCreated = $active_campaign_obj->create_custom_fields();
        }
        if ($statsTableSync && $webhookCreated && $fieldCreated) {
            $administration = new Administration();
            $this->createScheduler($administration);
            foreach ($_REQUEST as $key => $val) {
                if ($key != 'button' && $key != 'msg' && $key != 'source_form' && $key != 'return_action' && $key != 'return_module' && $key != 'module' && $key != 'action') {
                    $administration->saveSetting($_REQUEST['module'], $key, $val);
                }
            }
            if (!isset($_REQUEST['target_list_mapped'])) {
                $administration->saveSetting($_REQUEST['module'], "target_list_mapped", array());
            }
            if (!isset($_REQUEST['ac_lists_mapped'])) {
                $administration->saveSetting($_REQUEST['module'], "ac_lists_mapped", array());
            }
            if (!isset($_REQUEST['existing_sync'])) {
                $administration->saveSetting($_REQUEST['module'], "existing_sync", array());
            }
            if (!isset($_REQUEST['new_sync'])) {
                $administration->saveSetting($_REQUEST['module'], "new_sync", array());
            }
            if (!isset($_REQUEST['account_existing_sync'])) {
                $administration->saveSetting($_REQUEST['module'], 'account_existing_sync', '');
            }
            if (!isset($_REQUEST['account_new_sync'])) {
                $administration->saveSetting($_REQUEST['module'], 'account_new_sync', '');
            }
            if (!isset($_REQUEST['contacts_existing_sync'])) {
                $administration->saveSetting($_REQUEST['module'], 'contacts_existing_sync', '');
            }
            if (!isset($_REQUEST['contacts_new_sync'])) {
                $administration->saveSetting($_REQUEST['module'], 'contacts_new_sync', '');
            }
            if (!isset($_REQUEST['leads_existing_sync'])) {
                $administration->saveSetting($_REQUEST['module'], 'leads_existing_sync', '');
            }
            if (!isset($_REQUEST['leads_new_sync'])) {
                $administration->saveSetting($_REQUEST['module'], 'leads_new_sync', '');
            }
            if (!isset($_REQUEST['contracts_existing_sync'])) {
                $administration->saveSetting($_REQUEST['module'], 'contracts_existing_sync', '');
            }
            if (!isset($_REQUEST['contracts_new_sync'])) {
                $administration->saveSetting($_REQUEST['module'], 'contracts_new_sync', '');
            }
            if (!isset($_REQUEST['lead_score_sync'])) {
                $administration->saveSetting($_REQUEST['module'], 'lead_score_sync', '');
            }
            if (!isset($_REQUEST['leadsscore_flag'])) {
                $administration->saveSetting($_REQUEST['module'], 'leadsscore_flag', 0);
            }
            if (!isset($_REQUEST['leadsscore_date'])) {
                $administration->saveSetting($_REQUEST['module'], 'leadsscore_date', '');
            }
            $administration->saveSetting($_REQUEST['module'], 'webhoook_created', $webhookCreated);
            $administration->saveSetting($_REQUEST['module'], 'custom_field_created', $fieldCreated);
            $response['status'] = true;
            $response['message'] = $mod_strings["LBL_CONFIGURATION_STORED"] ?? "Configuration stored successfully";
            PluginLogger::log('Configuration stored successfully.');
        } else {
            $msg = '';
            if ($statsTableSync && $webhookCreated) {
                $msg = 'Failed to Create Custom fields in ActiveCampaign, Ignoring configuration store.';
                PluginLogger::log($msg);
            } else if ($statsTableSync) {
                $msg = 'Failed to Create Webhook in ActiveCampaign, Ignoring configuration store.';
                PluginLogger::log($msg);
            } else {
                $msg = 'Failed to Create Stats Table in ActiveCampaign, Ignoring configuration store.';
                PluginLogger::log($msg);
            }
            $response['status'] = false;
            if($msg){
                $response['message'] = $msg;
            }else{
                $response['message'] = $mod_strings["LBL_SOMETHING_WENT_WRONG"] ?? "Something went wrong!";
            }
        }
        echo json_encode($response);
        exit();
    }

    public function action_mapping()
    {
        $this->validateLicense();
        $this->view = 'mapping';
    }

    public function action_logs()
    {
        $this->validateLicense();
        $this->view = 'logs';
    }

    public function action_clearlogs()
    {
        $this->validateLicense();
        PluginLogger::clearLogs();
        PluginLogger::log('');
        header("Location: index.php?module=TL_ActiveCampaigns&action=logs");
        exit;
    }

    public function action_downloadlog()
    {
        $this->validateLicense();
        require_once('modules/TL_ActiveCampaigns/PluginLogger.php');
        $logs = PluginLogger::getLogs(50000);
        if (!empty($logs)) {
            $contents = implode(PHP_EOL, $logs);
            header('Content-Type: text/plain');
            header('Content-Disposition: attachment; filename="ActiveCampaign.log"');
            header('Content-Length: ' . strlen($contents));
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Expires: 0');
            ob_clean();
            flush();
            echo $contents;
            exit;
        } else {
            $GLOBALS['log']->fatal("Download failed: No logs found.");
            PluginLogger::log('Download failed: No logs found.');
            SugarApplication::redirect("index.php?module=TL_ActiveCampaigns&action=logs");
        }
    }

    public function action_store_mapping()
    {
        $response = [];
        if (isset($_POST["sugarContactFields"]) && isset($_POST["campaignFieldsContact"])) {
            $campaignFields = $_POST["campaignFieldsContact"];
            $sugarFields = $_POST["sugarContactFields"];
            $field_mapping_array = "contact_mapping_array";
        } elseif (isset($_POST["sugarLeadFields"]) && isset($_POST["campaignFieldsLead"])) {
            $campaignFields = $_POST["campaignFieldsLead"];
            $sugarFields = $_POST["sugarLeadFields"];
            $field_mapping_array = "lead_mapping_array";
        } elseif (isset($_POST["sugarAccountFields"]) && isset($_POST["campaignFieldsAccount"])) {
            $campaignFields = $_POST["campaignFieldsAccount"];
            $sugarFields = $_POST["sugarAccountFields"];
            $field_mapping_array = "account_mapping_array";
        } elseif (isset($_POST["sugarContractFields"]) && isset($_POST["campaignFieldsContract"])) {
            $campaignFields = $_POST["campaignFieldsContract"];
            $sugarFields = $_POST["sugarContractFields"];
            $field_mapping_array = "contract_mapping_array";
        }
        if (isset($campaignFields) && isset($sugarFields)) {
            $campaignMappedFields = array_combine($campaignFields, $sugarFields);
            $frmData = serialize($campaignMappedFields);
            $frmEnc = urlencode($frmData);
            $administration = new Administration();
            $administration->saveSetting("TL_ActiveCampaigns", $field_mapping_array, $frmEnc);
            $response['status'] = true;
            $response['message'] = $mod_strings["LBL_CONFIGURATION_STORED"] ?? "Configuration stored successfully";;
        } else {
            $response['status'] = false;
            $response['message'] = $mod_strings["LBL_SOMETHING_WENT_WRONG"] ?? "Something went wrong!";;
        }
        echo json_encode($response);
        exit();
    }

    public function action_check_api()
    {
        global $mod_strings;
        PluginLogger::log('---Authenticate API Credentials---');
        $response = [];
        $api_url = $_REQUEST['api_url'];
        $api_key = $_REQUEST['api_key'];
        if ($api_url != '' && $api_key != '') {
            $active_campaign_obj = new ActiveCampaign_class();
            $result = $active_campaign_obj->check_connectivity($api_url, $api_key);
            if (isset($result['status']) && $result['status'] === true) {
                $administration = new Administration();
                $administration->saveSetting($_REQUEST['module'], "api_url", $api_url);
                $administration->saveSetting($_REQUEST['module'], "api_key", $api_key);
                $administration->saveSetting($_REQUEST['module'], "ac_authenticated", true);
                $response['status'] = true;
                $response['message'] = $result['message'];
                PluginLogger::log('Connection Success');
            } else if (isset($result['message'])) {
                $response['status'] = false;
                $response['message'] = $result['message'];
                PluginLogger::log('Authentication failed with message: ' . $result['message']);
                PluginLogger::log($result);
            } else {
                $response['status'] = false;
                $response['message'] = $mod_strings["LBL_SOMETHING_WENT_WRONG"] ?? "Something went wrong!";
                PluginLogger::log('Authentication failed with an unknown reason');
                PluginLogger::log($result);
            }
        } else {
            $response['status'] = false;
            $response['message'] = $mod_strings["LBL_REQUIRED_FIELD_IS_EMPTY"] ?? "One of the required field is empty!";
            PluginLogger::log('One of the required field is empty!');
        }
        echo json_encode($response);
        exit();
    }

    private function create_stats_table()
    {
        global $db;
        $success = false;
        PluginLogger::log('---Sync Stats Table---');
        try {
            $table = AcConstants::AC_MIGRATION_STATS_TABLE;
            $sql = "CREATE TABLE IF NOT EXISTS `$table` (
                    `id` INT NOT NULL AUTO_INCREMENT,
                    `ac_list_id` VARCHAR(500) NULL,
                    `ac_contact_id` VARCHAR(500) NULL,
                    `crm_tl_name` VARCHAR(500) NULL,
                    `crm_tl_id` VARCHAR(500) NULL,
                    `related_id` VARCHAR(500) NULL,
                    `related_module` VARCHAR(500) NULL,
                    PRIMARY KEY (`id`));
                ";
            $db->query($sql);
            $success = true;
        } catch (Exception $e) {
            PluginLogger::log('Error creating stats table: ' . $e->getMessage());
            $GLOBALS['log']->fatal('Error creating stats table: ' . $e->getMessage());
        } finally {
            PluginLogger::log('Stats table creation process completed.');
        }
        return $success;
    }

    private function createScheduler($administration)
    {
        if (!isset($administration->settings['TL_ActiveCampaigns_SchedulerForExistingAccounts']) && isset($_REQUEST['account_existing_sync'])) {
            $functionName = 'function::CreateCampaignAccount';
            $bean = BeanFactory::newBean('Schedulers');
            $sql = new SugarQuery();
            $sql->select('id');
            $sql->from($bean);
            $sql->where()->queryAnd()->equals('deleted', 0)->equals('job', $functionName);
            $result = $sql->execute();
            $count = count($result);
            if ($count >= 1) {
                $create_scheduler = false;
            } else {
                $create_scheduler = true;
            }
            if ($create_scheduler) {
                $sj = new Scheduler();
                $sj->job = $functionName;
                $sj->name = 'ActiveCampaigns - Sync Existing Accounts';
                $sj->job_interval = '*/15::*::*::*::*';
                $sj->status = 'Active';
                $sj->date_time_start = date('Y-m-d h:i:s');
                $sj->save();
                $administration->saveSetting($_REQUEST['module'], 'SchedulerForExistingAccounts', true);
            }
        }
        if (!isset($administration->settings['TL_ActiveCampaigns_SchedulerForExistingContacts']) && isset($_REQUEST['contacts_existing_sync'])) {
            $functionName = 'function::CreateCampaignContact';
            $bean = BeanFactory::newBean('Schedulers');
            $sql = new SugarQuery();
            $sql->select('id');
            $sql->from($bean);
            $sql->where()->queryAnd()->equals('deleted', 0)->equals('job', $functionName);
            $result = $sql->execute();
            $count = count($result);
            if ($count >= 1) {
                $create_scheduler = false;
            } else {
                $create_scheduler = true;
            }
            if ($create_scheduler) {
                $sj = new Scheduler();
                $sj->job = $functionName;
                $sj->name = 'ActiveCampaigns - Sync Existing Contacts';
                $sj->job_interval = '*/15::*::*::*::*';
                $sj->status = 'Active';
                $sj->date_time_start = date('Y-m-d h:i:s');
                $sj->save();
                $administration->saveSetting($_REQUEST['module'], 'SchedulerForExistingContacts', true);
            }
        }
        if (!isset($administration->settings['TL_ActiveCampaigns_SchedulerForExistingLeads']) && isset($_REQUEST['leads_existing_sync'])) {
            $functionName = 'function::CreateCampaignLead';
            $bean = BeanFactory::newBean('Schedulers');
            $sql = new SugarQuery();
            $sql->select('id');
            $sql->from($bean);
            $sql->where()->queryAnd()->equals('deleted', 0)->equals('job', $functionName);
            $result = $sql->execute();
            $count = count($result);
            if ($count >= 1) {
                $create_scheduler = false;
            } else {
                $create_scheduler = true;
            }
            if ($create_scheduler) {
                $sj = new Scheduler();
                $sj->job = $functionName;
                $sj->name = 'ActiveCampaigns - Sync Existing Leads';
                $sj->job_interval = '*/15::*::*::*::*';
                $sj->status = 'Active';
                $sj->date_time_start = date('Y-m-d h:i:s');
                $sj->save();
                $administration->saveSetting($_REQUEST['module'], 'SchedulerForExistingLeads', true);
            }
        }
        if (!isset($administration->settings['TL_ActiveCampaigns_SchedulerForExistingLeadScore']) && isset($_REQUEST['lead_score_sync'])) {
            $functionName = 'function::GetExistingLeadScores';
            $bean = BeanFactory::newBean('Schedulers');
            $sql = new SugarQuery();
            $sql->select('id');
            $sql->from($bean);
            $sql->where()->queryAnd()->equals('deleted', 0)->equals('job', $functionName);
            $result = $sql->execute();
            $count = count($result);
            if ($count >= 1) {
                $create_scheduler = false;
            } else {
                $create_scheduler = true;
            }
            if ($create_scheduler) {
                $sj = new Scheduler();
                $sj->job = $functionName;
                $sj->name = 'ActiveCampaigns - Update Existing Lead Score Values';
                $sj->job_interval = '*/15::*::*::*::*';
                $sj->status = 'Active';
                $sj->date_time_start = date('Y-m-d h:i:s');
                $sj->save();
                $administration->saveSetting($_REQUEST['module'], 'SchedulerForExistingLeads', true);
            }
        }
        if (!isset($administration->settings['TL_ActiveCampaigns_SchedulerForExistingContracts']) && isset($_REQUEST['contracts_existing_sync'])) {
            $functionName = 'function::CreateCampaignContract';
            $bean = BeanFactory::newBean('Schedulers');
            $sql = new SugarQuery();
            $sql->select('id');
            $sql->from($bean);
            $sql->where()->queryAnd()->equals('deleted', 0)->equals('job', $functionName);
            $result = $sql->execute();
            $count = count($result);
            if ($count >= 1) {
                $create_scheduler = false;
            } else {
                $create_scheduler = true;
            }
            if ($create_scheduler) {
                $sj = new Scheduler();
                $sj->job = $functionName;
                $sj->name = 'ActiveCampaigns - Sync Existing Contracts';
                $sj->job_interval = '*/15::*::*::*::*';
                $sj->status = 'Active';
                $sj->date_time_start = date('Y-m-d h:i:s');
                $sj->save();
                $administration->saveSetting($_REQUEST['module'], 'SchedulerForExistingContracts', true);
            }
        }
    }
}
