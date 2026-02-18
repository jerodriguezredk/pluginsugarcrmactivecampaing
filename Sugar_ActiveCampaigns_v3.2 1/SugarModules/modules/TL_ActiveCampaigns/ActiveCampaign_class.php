<?php

use Sugarcrm\Sugarcrm\Cache\Exception;
use Sugarcrm\Sugarcrm\Security\HttpClient\ExternalResourceClient;
use Sugarcrm\Sugarcrm\Security\HttpClient\RequestException;
use Sugarcrm\Sugarcrm\Util\Uuid;

require_once("modules/TL_ActiveCampaigns/AcConstants.php");
require_once('modules/TL_ActiveCampaigns/PluginLogger.php');

class ActiveCampaign_class
{
    private $api_url;
    private $api_key;
    private $focus;

    function __construct()
    {
        $this->api_url = "";
        $this->api_key = "";

        $Administration = new Administration();
        $this->focus = $Administration->retrieveSettings();
        if (isset($this->focus->settings['TL_ActiveCampaigns_api_url'])) {
            $this->api_url = $this->focus->settings['TL_ActiveCampaigns_api_url'];
        } else {
            $this->api_url = '';
        }

        if (isset($this->focus->settings['TL_ActiveCampaigns_api_key'])) {
            $this->api_key = $this->focus->settings['TL_ActiveCampaigns_api_key'];
        } else {
            $this->api_key = '';
        }
    }

    public function check_connectivity($api_url = null, $api_key = null)
    {
        global $mod_strings;
        try {
            if ($api_url)
                $this->api_url = $api_url;
            if ($api_key)
                $this->api_key = $api_key;
            if ($this->api_url == "" && $this->api_key == '') {
                $response['status'] = false;
                $response['message'] = $mod_strings["LBL_REQUIRED_FIELD_IS_EMPTY"] ?? "One of the required field is empty!";
            } else {
                $params = array(
                    'api_key' => $this->api_key,
                    'api_action' => 'account_view',
                    'api_output' => 'json',
                );
                $query = "";
                foreach ($params as $key => $value) {
                    $query .= urlencode($key) . '=' . urlencode($value) . '&';
                }
                $query = rtrim($query, '& ');
                $url = rtrim($this->api_url, '/ ');
                $api_url = $url . '/admin/api.php?' . $query;
                $result = $this->get_call($api_url);
                $GLOBALS['log']->debug("Connecting with ActiveCampaign using URL: " . $this->api_url);
                $GLOBALS['log']->debug($result);
                if (isset($result["result_code"]) && $result["result_code"] == 1) {
                    $GLOBALS['log']->debug("ActiveCampaign connected successfully.");
                    $response['status'] = true;
                    $response['message'] = $mod_strings["LBL_AUTHENTICATION_COMPLETED"] ?? "Authentication completed Successfully!";;
                } else {
                    $GLOBALS['log']->debug("Failed to connect with ActiveCampaign");
                    $response['status'] = false;
                    $response['message'] = $mod_strings["LBL_AUTHENTICATION_ERROR"] ?? "Error in Authentication. Please check your API Keys";;
                }

            }
        } catch (Exception $e) {
            $GLOBALS['log']->fatal("ActiveCampaign Exception: " . basename(__FILE__) . ":" . __LINE__);
            $GLOBALS['log']->debug($e->getMessage() . " : " . $e->getCode());
            PluginLogger::log("ActiveCampaign Exception: " . basename(__FILE__) . ":" . __LINE__);
            PluginLogger::log($e->getMessage() . " : " . $e->getCode());
            $response['status'] = false;
            $response['message'] = $e->getMessage() . " : " . $e->getCode();
        }
        return $response;
    }

    public function get_all_lists($limit, $offset)
    {
        $data = null;
        $status = false;
        try {
            do {
                $result = $this->get_data("api/3/lists?limit=" . $limit . "&offset=" . $offset);
                $offset = $offset + 100;

                if (isset($result['lists']) && count($result['lists']) > 0) {
                    $lists = $result['lists'];
                    foreach ($lists as $value) {
                        if (isset($value['id'])) {
                            $list_arr[] = array(
                                "id" => $value['id'],
                                "name" => $value['name']
                            );
                        }
                    }
                }
            } while (isset($result['lists']) && count($result['lists']) == 100);
            $status = true;
            $data = $list_arr;
        } catch (\Exception $e) {
            $status = false;
            $data = $e->getMessage();
            $GLOBALS['log']->fatal('ActiveCampaign Exception: While loading ActiveCampaign Lists. ' . basename(__FILE__) . ":" . __LINE__);
            $GLOBALS['log']->fatal($e->getMessage());
            PluginLogger::log('ActiveCampaign Exception: While loading ActiveCampaign Lists. ' . basename(__FILE__) . ":" . __LINE__);
            PluginLogger::log($e->getMessage());
        }
        return array('status' => $status, 'data' => $data);
    }

    public function create_all_campaign_contacts()
    {
        if (isset($this->focus->settings['TL_ActiveCampaigns_contacts_sync'])) {
            $contacts_sync = $this->focus->settings['TL_ActiveCampaigns_contacts_sync'];
        } else {
            $contacts_sync = '';
        }
        if (isset($this->focus->settings['TL_ActiveCampaigns_contact_mapping_array'])) {
            $val = (string)$this->focus->settings['TL_ActiveCampaigns_contact_mapping_array'];
            $frmDec = urldecode($val);
            $field_mapping_array = unserialize($frmDec);
        } else {
            $field_mapping_array = '';
        }
        $next_offset = 0;
        $counter = 0;
        if ($contacts_sync) {
            if ($field_mapping_array != '') {
                do {
                    $bean = BeanFactory::getBean('Contacts');
                    $contacts = $bean->get_list(
                        $order_by = "",
                        $where = "contacts_cstm.active_campaign_id_c IS NUll OR contacts_cstm.active_campaign_id_c = '' AND contacts.deleted = 0",
                        $offset = $next_offset,
                        $limit = 100,
                        $max = 100,
                        $show_deleted = 0
                    );
                    if (isset($contacts['list']) && count($contacts['list']) > 0) {
                        $counter++;
                        $next_offset = $contacts['next_offset'];
                        foreach ($contacts['list'] as $contact) {
                            $contact_data = array();
                            $custom_array = array();
                            $primary_email = $contact->emailAddress->getPrimaryAddress($contact);
                            if ($primary_email) {
                                foreach ($field_mapping_array as $key => $info) {
                                    if ($key == "email") {
                                        $contact_data += array(
                                            $key => $primary_email
                                        );
                                    } elseif ($key == "last_name") {
                                        $contact_data += array(
                                            $key => $contact->last_name
                                        );
                                    } elseif ($key == "first_name") {
                                        $contact_data += array(
                                            $key => $contact->first_name
                                        );
                                    } elseif ($key == "phone") {
                                        $contact_data += array(
                                            $key => $contact->phone_mobile
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
                                if ($contacts_sync != '') {
                                    $contact_data += array(
                                        "p[{$contacts_sync}]" => $contacts_sync,
                                        "status[{$contacts_sync}]" => 1,
                                    );
                                }
                                $contact_sync = $this->create_campaign_contact($contact_data);
                                if (isset($contact_sync['result_code']) && (int)$contact_sync['result_code']) {
                                    $subscriber_id = $contact_sync["subscriber_id"] ?? '';
                                    $contact_id = $contact->id;
                                    $bean = BeanFactory::retrieveBean("Contacts", $contact_id);
                                    $bean->active_campaign_id_c = $subscriber_id;
                                    $bean->save();
                                } else {
                                    PluginLogger::log($contact_sync["error"] ?? "Something Went wrong while creating campaign contact " . basename(__FILE__) . ":" . __LINE__);
                                    PluginLogger::log(print_r($contact_sync, true));
                                }
                            } else {
                                PluginLogger::log("Email of contact is not found. " . basename(__FILE__) . ":" . __LINE__);
                            }
                        }
                    }
                    if ($counter == 3) {
                        break;
                    }

                } while (isset($contacts['list']) && count($contacts['list']) > 0);
            } else {
                PluginLogger::log("Contact fields mapping with active field mapping not found. " . basename(__FILE__) . ":" . __LINE__);
            }
        } else {
            PluginLogger::log("No active campaign list found for Contacts. " . basename(__FILE__) . ":" . __LINE__);
        }
    }

    public function create_all_campaign_accounts()
    {
        $ac_list_id = '';
        if (isset($this->focus->settings['TL_ActiveCampaigns_account_sync'])) {
            $ac_list_id = $this->focus->settings['TL_ActiveCampaigns_account_sync'];
        }

        if (isset($this->focus->settings['TL_ActiveCampaigns_account_mapping_array'])) {
            $val = (string)$this->focus->settings['TL_ActiveCampaigns_account_mapping_array'];
            $frmDec = urldecode($val);
            $field_mapping_array = unserialize($frmDec);
        } else {
            $field_mapping_array = '';
        }

        $next_offset = 0;
        $counter = 0;
        if ($ac_list_id) {
            if ($field_mapping_array != '') {
                do {
                    $bean = BeanFactory::getBean('Accounts');
                    $accounts = $bean->get_list(
                        $order_by = "",
                        $where = "accounts_cstm.active_campaign_id_c IS NUll OR accounts_cstm.active_campaign_id_c = '' AND accounts.deleted = 0",
                        $offset = $next_offset,
                        $limit = 100,
                        $max = 100,
                        $show_deleted = 0
                    );
                    if (isset($accounts['list']) && count($accounts['list']) > 0) {
                        $counter++;
                        $next_offset = $accounts['next_offset'];
                        foreach ($accounts['list'] as $acnt) {
                            $account_data = array();
                            $custom_array = array();
                            $primary_email = $acnt->emailAddress->getPrimaryAddress($acnt);
                            if ($primary_email) {
                                foreach ($field_mapping_array as $key => $info) {
                                    if ($key == "email") {
                                        $account_data += array(
                                            $key => $primary_email
                                        );
                                    } elseif ($key == "last_name") {
                                        $account_data += array(
                                            $key => $acnt->name
                                        );
                                    } elseif ($key == "first_name") {
                                        $account_data += array(
                                            $key => $acnt->name
                                        );
                                    } elseif ($key == "phone") {
                                        $account_data += array(
                                            $key => $acnt->phone_office
                                        );
                                    } else {
                                        if ($key != AcConstants::CRM_SOURCE && $key != AcConstants::CRM_RECORD_ID) {
                                            $account_data += array(
                                                "field[%$key%,0]" => $acnt->{$info},
                                            );
                                        }
                                    }
                                }

                                $account_data += array(
                                    "field[%" . AcConstants::CRM_SOURCE . "%,0]" => $acnt->module_dir,
                                    "field[%" . AcConstants::CRM_RECORD_ID . "%,0]" => $acnt->id,
                                );

                                if ($ac_list_id != '') {
                                    $account_data += array(
                                        "p[{$ac_list_id}]" => $ac_list_id,
                                        "status[{$ac_list_id}]" => 1,
                                    );
                                }
                                $contact_sync = $this->create_campaign_contact($account_data);
                                if (isset($contact_sync["result_code"]) && (int)$contact_sync["result_code"]) {
                                    $subscriber_id = $contact_sync["subscriber_id"] ?? "";
                                    $account_id = $acnt->id;
                                    $bean = BeanFactory::retrieveBean("Accounts", $account_id);
                                    $bean->active_campaign_id_c = $subscriber_id;
                                    $bean->save();
                                } else {
                                    PluginLogger::log($contact_sync["error"] ?? "Something Went wrong while creating campaign contact. " . basename(__FILE__) . ":" . __LINE__);
                                    PluginLogger::log(print_r($contact_sync, true));
                                }
                            } else {
                                PluginLogger::log("Email of account is not found. " . basename(__FILE__) . ":" . __LINE__);
                            }
                        }
                    }

                    if ($counter == 3) {
                        break;
                    }
                } while (isset($accounts['list']) && count($accounts['list']) > 0);
            } else {
                PluginLogger::log("Accounts fields mapping with active field mapping not found. " . basename(__FILE__) . ":" . __LINE__);
            }
        } else {
            PluginLogger::log("No active campaign list found for Accounts. " . basename(__FILE__) . ":" . __LINE__);
        }
    }

    public function create_all_campaign_leads()
    {
        $ac_list_id = '';
        if (isset($this->focus->settings['TL_ActiveCampaigns_leads_sync'])) {
            $ac_list_id = $this->focus->settings['TL_ActiveCampaigns_leads_sync'];
        }

        if (isset($this->focus->settings['TL_ActiveCampaigns_lead_mapping_array'])) {
            $val = (string)$this->focus->settings['TL_ActiveCampaigns_lead_mapping_array'];
            $frmDec = urldecode($val);
            $field_mapping_array = unserialize($frmDec);
        } else {
            $field_mapping_array = '';
        }

        $next_offset = 0;
        $counter = 0;
        if ($ac_list_id) {
            if ($field_mapping_array != '') {
                do {
                    $bean = BeanFactory::getBean('Leads');
                    $leads = $bean->get_list(
                        $order_by = "",
                        $where = "leads_cstm.active_campaign_id_c IS NUll OR leads_cstm.active_campaign_id_c = '' AND leads.deleted = 0",
                        $offset = $next_offset,
                        $limit = 100,
                        $max = 100,
                        $show_deleted = 0
                    );
                    if (isset($leads['list']) && count($leads['list']) > 0) {
                        $counter++;
                        $next_offset = $leads['next_offset'];
                        foreach ($leads['list'] as $acnt) {
                            $account_data = array();
                            $custom_array = array();
                            $primary_email = $acnt->emailAddress->getPrimaryAddress($acnt);
                            if ($primary_email) {
                                foreach ($field_mapping_array as $key => $info) {
                                    if ($key == "email") {
                                        $account_data += array(
                                            $key => $primary_email
                                        );
                                    } elseif ($key == "last_name") {
                                        $account_data += array(
                                            $key => $acnt->last_name
                                        );
                                    } elseif ($key == "first_name") {
                                        $account_data += array(
                                            $key => $acnt->first_name
                                        );
                                    } elseif ($key == "phone") {
                                        $account_data += array(
                                            $key => $acnt->phone_mobile
                                        );
                                    } else {
                                        if ($key != AcConstants::CRM_SOURCE && $key != AcConstants::CRM_RECORD_ID) {
                                            $account_data += array(
                                                "field[%$key%,0]" => $acnt->{$info},
                                            );
                                        }
                                    }
                                }


                                $account_data += array(
                                    "field[%" . AcConstants::CRM_SOURCE . "%,0]" => $acnt->module_dir,
                                    "field[%" . AcConstants::CRM_RECORD_ID . "%,0]" => $acnt->id,
                                );

                                if ($ac_list_id != '') {
                                    $account_data += array(
                                        "p[{$ac_list_id}]" => $ac_list_id,
                                        "status[{$ac_list_id}]" => 1,
                                    );
                                }

                                $contact_sync = $this->create_campaign_contact($account_data);
                                if (isset($contact_sync["result_code"]) && (int)$contact_sync["result_code"]) {
                                    $subscriber_id = $contact_sync["subscriber_id"] ?? "";
                                    $account_id = $acnt->id;
                                    $bean = BeanFactory::retrieveBean("Leads", $account_id);
                                    $bean->active_campaign_id_c = $subscriber_id;
                                    $bean->save();
                                } else {
                                    PluginLogger::log($contact_sync["error"] ?? "Something Went wrong while creating campaign contact.. " . basename(__FILE__) . ":" . __LINE__);
                                    PluginLogger::log(print_r($contact_sync, true));
                                }
                            } else {
                                PluginLogger::log("Email of lead is not found. " . basename(__FILE__) . ":" . __LINE__);
                            }
                        }
                    }

                    if ($counter == 3) {
                        break;
                    }
                } while (isset($leads['list']) && count($leads['list']) > 0);
            } else {
                PluginLogger::log("Leads fields mapping with active field mapping not found. " . basename(__FILE__) . ":" . __LINE__);
            }
        } else {
            PluginLogger::log("No active campaign list found for Leads. " . basename(__FILE__) . ":" . __LINE__);
        }
    }

    public function create_all_campaign_contracts()
    {
        $ac_list_id = '';
        if (isset($this->focus->settings['TL_ActiveCampaigns_contracts_sync'])) {
            $ac_list_id = $this->focus->settings['TL_ActiveCampaigns_contracts_sync'];
        }
        if (isset($this->focus->settings['TL_ActiveCampaigns_contract_mapping_array'])) {
            $val = (string)$this->focus->settings['TL_ActiveCampaigns_contract_mapping_array'];
            $frmDec = urldecode($val);
            $field_mapping_array = unserialize($frmDec);
        } else {
            $field_mapping_array = '';
        }
        $next_offset = 0;
        $limit = 100;
        $accountsCache = [];
        if ($ac_list_id) {
            if ($field_mapping_array != '') {
                $seed = BeanFactory::newBean('Contracts');
                $query = new SugarQuery();
                do {
                    $query->from($seed, [
                        'team_security' => false,
                    ]);
                    $where = $query->where();
                    $where->queryOr()->isNull('contracts_cstm.active_campaign_id_c')->equals('contracts_cstm.active_campaign_id_c', '');
                    $where->equals('contracts.deleted', 0);
                    $query->limit($limit);
                    $query->offset($next_offset);
                    $contracts = $seed->fetchFromQuery($query, [], ['skipSecondaryQuery' => true]);
                    if (empty($contracts)) {
                        PluginLogger::log("No more Contracts found for syncing " . basename(__FILE__) . ":" . __LINE__);
                        break;
                    }
                    $next_offset += count($contracts);
                    foreach ($contracts as $contract) {
                        $contract_data = array();
                        $primary_email = null;
                        if (!empty($contract->account_id)) {
                            $accountId = $contract->account_id;
                            if (array_key_exists($accountId, $accountsCache)) {
                                $primary_email = $accountsCache[$accountId];
                            } else {
                                $account = BeanFactory::getBean('Accounts', $accountId);
                                if ($account && isset($account->emailAddress)) {
                                    $primary_email = $account->emailAddress->getPrimaryAddress($account);
                                }
                                $accountsCache[$accountId] = $primary_email;
                            }
                        }
                        if ($primary_email) {
                            foreach ($field_mapping_array as $key => $info) {
                                if ($key == "email") {
                                    $contract_data += array(
                                        $key => $primary_email
                                    );
                                } else {
                                    if ($key != AcConstants::CRM_SOURCE && $key != AcConstants::CRM_RECORD_ID) {
                                        $value = $contract->{$info};
                                        $fieldType = $this->get_field_type('Contracts', $key);
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
                                "field[%" . AcConstants::CRM_SOURCE . "%,0]" => $contract->module_dir,
                                "field[%" . AcConstants::CRM_RECORD_ID . "%,0]" => $contract->id,
                            );
                            if ($ac_list_id != '') {
                                $contract_data += array(
                                    "p[{$ac_list_id}]" => $ac_list_id,
                                    "status[{$ac_list_id}]" => 1,
                                );
                            }
                            $contact_sync = $this->create_campaign_contact($contract_data);
                            if (isset($contact_sync["result_code"]) && (int)$contact_sync["result_code"]) {
                                $subscriber_id = $contact_sync["subscriber_id"] ?? "";
                                $account_id = $contract->id;
                                $bean = BeanFactory::retrieveBean("Contracts", $account_id);
                                $bean->active_campaign_id_c = $subscriber_id;
                                $bean->save();
                            } else {
                                PluginLogger::log($contact_sync["error"] ?? "Something Went wrong while creating campaign contact.. " . basename(__FILE__) . ":" . __LINE__);
                                PluginLogger::log(print_r($contact_sync, true));
                            }
                        } else {
                            PluginLogger::log("Email of contract account is not found. " . basename(__FILE__) . ":" . __LINE__);
                        }
                    }
                    if (count($contracts) < $limit) {
                        break;
                    }
                } while (isset($contracts) && count($contracts) > 0);
            } else {
                PluginLogger::log("Contracts fields mapping with active field mapping not found. " . basename(__FILE__) . ":" . __LINE__);
            }
        } else {
            PluginLogger::log("No active campaign list found for Contracts. " . basename(__FILE__) . ":" . __LINE__);
        }
    }


    private function get_field_type($module_name, $field_name)
    {
        if (empty($module_name) || empty($field_name)) {
            return null;
        }
        $contract = BeanFactory::newBean($module_name);
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

    public function create_campaign_contact($post_array)
    {
        $params = array(
            'api_key' => $this->api_key,
            'api_action' => 'contact_sync',
            'api_output' => 'json',
        );
        $query = "";
        foreach ($params as $key => $value) {
            $query .= urlencode((string)$key) . '=' . urlencode((string)$value) . '&';
        }
        $query = rtrim($query, '& ');
        $post_data = "";
        foreach ($post_array as $key => $value) {
            $post_data .= urlencode((string)$key) . '=' . urlencode((string)($value ?? '')) . '&';
        }
        $post_data = rtrim($post_data, '& ');
        $url = rtrim($this->api_url, '/ ');
        $api_url = $url . '/admin/api.php?' . $query;
        $contact_sync = $this->post_call($api_url, $post_data);
        return $contact_sync;
    }

    public function get_ac_updated_contact($limit, $offset)
    {

        $var = date("Y-m-d");
        $var_subtracted_date = date("Y-m-d", strtotime("-1 day", strtotime($var)));
        $scoreValue = 0;
        $field_array = array();
        $result = $this->get_data("api/3/contacts?limit=" . $limit . "&offset=" . $offset . "&filters[updated_after]=" . $var_subtracted_date);

        if (isset($result['scoreValues']) && count($result['scoreValues']) > 0) {
            foreach ($result['scoreValues'] as $value) {
                if (isset($value['scoreValue'])) {
                    $contact_id = $value['contact'];
                    $scoreValue = $value['scoreValue'];
                    $this->update_sugar_score($contact_id, $scoreValue);
                }
            }

            if (isset($result['meta']) && $result['meta']['total'] >= $offset) {
                $offset = $offset + $limit;
                $this->get_ac_updated_contact($limit, $offset);
            }
        }
        return $scoreValue;
    }

    public function update_existing_lead_score($limit, $offset)
    {
        $Administration = new Administration();
        $Administration->retrieveSettings();
        $leadscore_date = isset($Administration->settings['TL_ActiveCampaigns_leadsscore_date']) ? $Administration->settings['TL_ActiveCampaigns_leadsscore_date'] : '';
        $flag = isset($Administration->settings['TL_ActiveCampaigns_leadsscore_flag']) ? $Administration->settings['TL_ActiveCampaigns_leadsscore_flag'] : '';
        if (!isset($flag) || empty($flag))
            $flag = 0;
        if (!isset($leadscore_date) || empty($leadscore_date)) {
            $leadscore_date = date("Y-m-d");
        }
        $var_subtracted_date = date("Y-m-d", strtotime("-2 days", strtotime($leadscore_date)));
        $scoreValue = 0;
        $result = $this->get_data("api/3/contacts?limit=" . $limit . "&offset=" . $offset . "&filters[updated_after]=" . $var_subtracted_date, "&filters[updated_before]=" . $leadscore_date);
        if (isset($result['scoreValues']) && count($result['scoreValues']) > 0) {
            foreach ($result['scoreValues'] as $value) {
                if (isset($value['scoreValue'])) {
                    $contact_id = $value['contact'];
                    $scoreValue = $value['scoreValue'];
                    $lead_check = $this->check_lead_score($contact_id);
                    if ($lead_check) {
                        $this->update_sugar_score($contact_id, $scoreValue);
                    }

                }
            }

            if (isset($result['meta']) && $result['meta']['total'] >= $offset) {
                $offset = $offset + $limit;
                $this->update_existing_lead_score($limit, $offset);
            }
        } else {
            $flag++;
        }
        if (!empty($flag) && $flag >= 50) {
            global $db;
            $query = "UPDATE schedulers SET status = 'Inactive' WHERE job = 'function::GetExistingLeadScores'";
            $db->query($query);
        }
        $Administration->saveSetting("TL_ActiveCampaigns", "leadsscore_flag", $flag);
        $Administration->saveSetting("TL_ActiveCampaigns", "leadsscore_date", $var_subtracted_date);
        return $scoreValue;
    }

    public function check_lead_score($id)
    {
        global $db;
        $sql = "SELECT 
                    *
                FROM
                    leads_cstm
                WHERE
                    active_campaign_id_c = $id";
        $lead_result = $db->query($sql);

        if (mysqli_num_rows($lead_result) > 0) {
            while ($row = $db->fetchByAssoc($lead_result)) {
                if ($row['ac_lead_score_c'] != '' && $row['ac_lead_score_c'] != 0) {
                    return 0;
                } else {
                    return 1;
                }
            }
        } else {
            $sql = "SELECT 
                    *
                FROM
                    contacts_cstm
                WHERE
                    active_campaign_id_c = $id";

            $contact_result = $db->query($sql);

            if (mysqli_num_rows($contact_result) > 0) {
                while ($row = $db->fetchByAssoc($contact_result)) {
                    if ($row['ac_lead_score_c'] != '' && $row['ac_lead_score_c'] != 0) {
                        return 0;
                    } else {
                        return 1;
                    }
                }
            } else {
                $sql = "SELECT 
                    *
                FROM
                    accounts_cstm
                WHERE
                    active_campaign_id_c = $id";

                $accounts_result = $db->query($sql);

                if (mysqli_num_rows($accounts_result) > 0) {
                    while ($row = $db->fetchByAssoc($accounts_result)) {
                        if ($row['ac_lead_score_c'] != '' && $row['ac_lead_score_c'] != 0) {
                            return 0;
                        } else {
                            return 1;
                        }
                    }
                } else {
                    return 0;
                }
            }
        }
    }

    public function update_sugar_score($id, $score)
    {
        global $db;
        $sql = "SELECT 
                    A.id, AC.id_c, AC.active_campaign_id_c
                FROM
                    leads_cstm AC
                LEFT JOIN
                    leads A 
                ON 
                    AC.id_c = A.id
                WHERE
                    AC.active_campaign_id_c = $id
                AND 
                    A.deleted = 0";

        $lead_result = $db->query($sql);

        if (mysqli_num_rows($lead_result) > 0) {
            while ($row = $db->fetchByAssoc($lead_result)) {
                $bean = new Lead();
                $bean->db->query('UPDATE leads, leads_cstm SET ac_lead_score_c="' . $score . '" WHERE leads.id=leads_cstm.id_c AND leads.id="' . $row['id'] . '"');
            }
        } else {
            $sql = "SELECT 
                    A.id, AC.id_c, AC.active_campaign_id_c
                FROM
                    contacts_cstm AC
                LEFT JOIN
                    contacts A 
                ON 
                    AC.id_c = A.id
                WHERE
                    AC.active_campaign_id_c = $id
                AND 
                    A.deleted = 0";

            $contact_result = $db->query($sql);
            if (mysqli_num_rows($contact_result) > 0) {
                while ($row = $db->fetchByAssoc($contact_result)) {
                    $bean = new Contact();

                    $bean->db->query('UPDATE contacts, contacts_cstm SET ac_lead_score_c="' . $score . '" WHERE contacts.id=contacts_cstm.id_c AND contacts.id="' . $row['id'] . '"');
                }
            } else {
                $sql = "SELECT 
                        A.id, AC.id_c, AC.active_campaign_id_c
                    FROM
                        accounts_cstm AC
                    LEFT JOIN
                        accounts A 
                    ON 
                        AC.id_c = A.id
                    WHERE
                        AC.active_campaign_id_c = $id
                    AND 
                        A.deleted = 0";

                $accounts_result = $db->query($sql);
                if (mysqli_num_rows($accounts_result) > 0) {
                    while ($row = $db->fetchByAssoc($accounts_result)) {
                        $bean = new Account();
                        $bean->db->query('UPDATE accounts, accounts_cstm SET ac_lead_score_c="' . $score . '" WHERE accounts.id=accounts_cstm.id_c AND accounts.id="' . $row['id'] . '"');
                    }
                }
            }
        }

    }

    public function get_custom_fields()
    {

        $field_array = array();
        $result = $this->get_data("api/3/fields?limit=500");
        if (isset($result['fields']) && count($result['fields']) > 0) {
            foreach ($result['fields'] as $value) {
                if (isset($value["title"]) && isset($value["perstag"])) {
                    $field_array[$value["perstag"]] = $value["title"];
                }

            }
        }
        return $field_array;
    }

    public function create_webook()
    {
        $created = false;
        try {
            PluginLogger::log('---Create Webhooks in Active Campaign---');
            $campaign_stats_url = $this->url() . "/rest/v11_4/TL_ActiveCampaigns/CampaignStats";
            //$sync_hook_url = $this->url() . "/rest/v11_4/TL_ActiveCampaigns/SyncAcContacts";
            //$update_hook_url = $this->url() . "/rest/v11_4/TL_ActiveCampaigns/UpdateAcContacts";
            //PluginLogger::log('New Sync URL: ' . $sync_hook_url);
            //PluginLogger::log('Update Sync URL: ' . $update_hook_url);
            $res = $this->get_data("api/3/webhooks/");
            $statsWebhookExists = false;
            foreach ($res['webhooks'] as $hook) {
                if(isset($hook['url']) && $hook['url'] === $campaign_stats_url){
                    $statsWebhookExists = true;
                }
            }
            if (!$statsWebhookExists) {
                $new_hook_data = $this->get_hook_data($campaign_stats_url);
                $new_hook_data['webhook']['events'] = array('open', 'sent', 'click');
                $new_hook_data['webhook']['sources'] = array('public', 'admin', 'api', 'system');
                $hookCreationResponse = $this->post_data("api/3/webhooks/", $new_hook_data);
                if (isset($hookCreationResponse['response']['webhook']['id'])) {
                    $created = true;
                    PluginLogger::log('New webhook added for campaign stats sync: ' . $campaign_stats_url);
                }else{
                    PluginLogger::log('Failed to create new webhook for Campaign Stats URL: ' . $campaign_stats_url);
                    PluginLogger::log($hookCreationResponse);
                }
            }else{
                $created = true;
                PluginLogger::log('Stats URL: ' . $campaign_stats_url . ' already exists in Active Campaign');
            }
        } catch (Exception $e) {
            PluginLogger::log("ActiveCampaign Exception: Webhook creation failed " . basename(__FILE__) . ":" . __LINE__);
            PluginLogger::log($e->getMessage());
            return false;
        }
        return $created;
    }

    private function get_hook_data($hook_url)
    {
        return array(
            "webhook" => array(
                "name" => "Sync contact to SugarCRM",
                "url" => $hook_url,
                "events" => array(),
                "sources" => array(
                    "public",
                    "admin",
                )
            )
        );
    }

    private function endsWith($haystack, $needle)
    {
        return substr_compare($haystack, $needle, -strlen($needle)) === 0;
    }

    private function url()
    {
        if (isset($_SERVER['HTTPS'])) {
            $protocol = ($_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "off") ? "https" : "http";
        } else {
            $protocol = 'http';
        }
        if ($_SERVER['SERVER_NAME'] == "localhost") {
            $root_url = $protocol . "://" . "localhost/" . explode("/", $_SERVER['REQUEST_URI'])[1];
        } else if (strpos($_SERVER['SERVER_NAME'], "ngrok") !== false) {
            $root_url = $protocol . "://" . $_SERVER['HTTP_HOST'] . "/" . explode("/", $_SERVER['REQUEST_URI'])[1];
        } else {
            $root_url = $protocol . "://" . $_SERVER['HTTP_HOST'];
        }
        return $root_url;
    }

    public function create_custom_fields()
    {
        PluginLogger::log('---Create Custom Fields in Active Campaign---');
        $created = false;
        $createdCount = 0;
        $fieldsToCreate = [
            [
                "type" => "hidden",
                "title" => "Source",
                "descript" => "source",
                "isrequired" => 0,
                "perstag" => "crm_source",
                "defval" => "",
                "visible" => 1,
                "ordernum" => 1
            ],
            [
                "type" => "hidden",
                "title" => "CRM Record ID",
                "descript" => "crm record id",
                "isrequired" => 0,
                "perstag" => "crm_record_id",
                "defval" => "",
                "visible" => 1,
                "ordernum" => 2
            ]
        ];
        $headers = [
            "accept" => "application/json",
            "content-type" => "application/json"
        ];
        try {
            $existingFieldsRes = $this->get_data("api/3/fields", "GET", $headers);
            $existingPerstags = [];
            $existingTitles = [];
            if (!empty($existingFieldsRes['fields']) && is_array($existingFieldsRes['fields'])) {
                foreach ($existingFieldsRes['fields'] as $field) {
                    if (!empty($field['perstag'])) {
                        $existingPerstags[] = strtolower($field['perstag']);
                    }
                    if (!empty($field['title'])) {
                        $existingTitles[] = strtolower($field['title']);
                    }
                }
            }
            foreach ($fieldsToCreate as $fieldData) {
                $title = strtolower($fieldData['title']);
                $perstag = strtolower($fieldData['perstag']);
                if (in_array($title, $existingTitles)) {
                    PluginLogger::log("Field with title '{$fieldData['title']}' already exists — skipping.");
                    $createdCount++;
                    continue;
                }
                if (in_array($perstag, $existingPerstags)) {
                    PluginLogger::log("Field with perstag '{$fieldData['perstag']}' already exists — skipping.");
                    $createdCount++;
                    continue;
                }
                $postData = [
                    "field" => array_merge($fieldData, [
                        "type" => "hidden",
                        "isrequired" => 0,
                        "defval" => "",
                        "visible" => 1
                    ])
                ];
                $response = $this->post_data("api/3/fields/", $postData, "POST", $headers);
                if (isset($response['response']['field']['id'])) {
                    PluginLogger::log("Custom field '{$fieldData['title']}' created successfully.");
                    $created = true;
                    $createdCount++;
                } else {
                    PluginLogger::log("Failed to create custom field '{$fieldData['title']}'.");
                    PluginLogger::log($response);
                }
            }
            PluginLogger::log("created count " . $createdCount);
            if($createdCount < 2){
                $created = false;
            }else{
                $created = true;
            }
            if(!$created){
                PluginLogger::log("Failed to Create Custom fields in ActiveCampaign, Ignoring configuration store.");
            }
        } catch (Exception $e) {
            PluginLogger::log("ActiveCampaign Exception: Custom fields creation failed. " . basename(__FILE__) . ":" . __LINE__);
            PluginLogger::log($e->getMessage());
        }
        return $created;
    }

    public function get_data($url)
    {
        $result = array();
        try {
            $url = $this->get_request_url($url);
            $response = (new ExternalResourceClient(3600, 10))->get($url, ["Api-Token" => $this->api_key]);
            $httpCode = $response->getStatusCode();
            if ($httpCode >= 400) {
                PluginLogger::log('An error occurred while requesting ActiveCampagin datain modules/TL_ActiveCampaigns/ActiveCampaign_class::get_data() ' . basename(__FILE__) . ":" . __LINE__);
                PluginLogger::log("Request failed with status: " . $httpCode);
            } else {
                $bodyContents = $response->getBody()->getContents();
                $result = JSON::decode($bodyContents, false, true);
            }
        } catch (RequestException $e) {
            $GLOBALS['log']->fatal('ActiveCampaign Exception: An error occurred while requesting ActiveCampaign datail modules/TL_ActiveCampaigns/ActiveCampaign_class::get_data() ' . basename(__FILE__) . ":" . __LINE__);
            $GLOBALS['log']->fatal('Code: ' . $e->getCode());
            $GLOBALS['log']->fatal('Error: ' . $e->getMessage());
            PluginLogger::log('ActiveCampaign Exception: An error occurred while requesting ActiveCampaign datail modules/TL_ActiveCampaigns/ActiveCampaign_class::get_data() ' . basename(__FILE__) . ":" . __LINE__);
            PluginLogger::log('Code: ' . $e->getCode());
            PluginLogger::log('Error: ' . $e->getMessage());
        }
        return $result;
    }

    public function post_data($url, $values, $request = 'POST', $headers = array())
    {
        $result = array();
        $error = '';
        try {
            $url = $this->get_request_url($url);
            if ($request == 'DELETE') {
                $response = (new ExternalResourceClient(3600, 10))->delete($url, ["Api-Token" => $this->api_key]);
            } else {
                $postObject = $values;
                if (is_array($values)) {
                    $postObject = JSON::encode($values);
                }
                $headers["Api-Token"] = $this->api_key;
                $response = (new ExternalResourceClient(3600, 10))->post($url, $postObject, $headers);
            }
            $httpCode = $response->getStatusCode();
            if ($httpCode >= 400) {
                $error = $response->getReasonPhrase();
                /*$GLOBALS['log']->fatal('An error occurred while posting ActiveCampagin data in modules/TL_ActiveCampaigns/ActiveCampaign_class::post_data()');
                $GLOBALS['log']->fatal("URL: " . $url);
                $GLOBALS['log']->fatal("Type: " . $request);
                $GLOBALS['log']->fatal("Request failed with status: " . $httpCode);*/
            }
            $bodyContents = $response->getBody()->getContents();
            $result = JSON::decode($bodyContents, false, true);
        } catch (RequestException $e) {
            $error = $e->getMessage();
            $GLOBALS['log']->fatal('ActiveCampaign Exception: An error occurred while posting ActiveCampaign data in modules/TL_ActiveCampaigns/ActiveCampaign_class::post_data() ' . basename(__FILE__) . ":" . __LINE__);
            $GLOBALS['log']->fatal('Code: ' . $e->getCode());
            $GLOBALS['log']->fatal('Error: ' . $e->getMessage());
            PluginLogger::log('ActiveCampaign Exception: An error occurred while posting ActiveCampaign data in ' . basename(__FILE__) . ":" . __LINE__);
            PluginLogger::log('Code: ' . $e->getCode());
            PluginLogger::log('Error: ' . $e->getMessage());
        }
        return array('error' => $error, 'response' => $result);

    }

    public function get_call($api_url)
    {
        $result = array();
        try {
            $response = (new ExternalResourceClient(3600, 10))->get($api_url);
            $httpCode = $response->getStatusCode();
            if ($httpCode >= 400) {
                $GLOBALS['log']->fatal('An error occurred while requesting ActiveCampagin datain modules/TL_ActiveCampaigns/ActiveCampaign_class::get_call() ' . basename(__FILE__) . ":" . __LINE__);
                $GLOBALS['log']->fatal("Request failed with status: " . $httpCode);
                PluginLogger::log('An error occurred while requesting ' . basename(__FILE__) . ":" . __LINE__);
                PluginLogger::log("Request failed with status: " . $httpCode);
            } else {
                $bodyContents = $response->getBody()->getContents();
                $result = JSON::decode($bodyContents, false, true);
            }
        } catch (RequestException $e) {
            $GLOBALS['log']->fatal('ActiveCampaign Exception: An error occurred while requesting ActiveCampaign data in modules/TL_ActiveCampaigns/ActiveCampaign_class::get_call() ' . basename(__FILE__) . ":" . __LINE__);
            $GLOBALS['log']->fatal('Code: ' . $e->getCode());
            $GLOBALS['log']->fatal('Error: ' . $e->getMessage());
            PluginLogger::log('ActiveCampaign Exception: An error occurred while requesting ActiveCampaign data ' . basename(__FILE__) . ":" . __LINE__);
            PluginLogger::log('Code: ' . $e->getCode());
            PluginLogger::log('Error: ' . $e->getMessage());
        }
        return $result;
    }

    public function post_call($api_url, $post)
    {
        $result = array();
        try {
            $postObject = $post;
            if (is_array($post)) {
                $postObject = JSON::encode($post);
            }
            $response = (new ExternalResourceClient(3600, 10))->post($api_url, $postObject);
            $httpCode = $response->getStatusCode();
            if ($httpCode >= 400) {
                $GLOBALS['log']->fatal('An error occurred while posting ActiveCampagin data in modules/TL_ActiveCampaigns/ActiveCampaign_class::post_call() ' . basename(__FILE__) . ":" . __LINE__);
                $GLOBALS['log']->fatal("Request failed with status: " . $httpCode);
                PluginLogger::log('An error occurred while posting ActiveCampagin data ' . basename(__FILE__) . ":" . __LINE__);
                PluginLogger::log("Request failed with status: " . $httpCode);
            } else {
                $bodyContents = $response->getBody()->getContents();
                $result = JSON::decode($bodyContents, false, true);
            }
        } catch (RequestException $e) {
            $GLOBALS['log']->fatal('ActiveCampaign Exception: An error occurred while posting ActiveCampaign ' . basename(__FILE__) . ":" . __LINE__);
            $GLOBALS['log']->fatal('Code: ' . $e->getCode());
            $GLOBALS['log']->fatal('Error: ' . $e->getMessage());
            PluginLogger::log('ActiveCampaign Exception: An error occurred while posting ActiveCampaign ' . basename(__FILE__) . ":" . __LINE__);
            PluginLogger::log('Code: ' . $e->getCode());
            PluginLogger::log('Error: ' . $e->getMessage());
        }

        return $result;

    }

    private function get_request_url($url)
    {
        $end_with_slash = $this->endsWith($this->api_url, "/");
        if ($end_with_slash) {
            $url = $this->api_url . $url;
        } else {
            $url = $this->api_url . "/" . $url;
        }
        return $url;
    }
}
