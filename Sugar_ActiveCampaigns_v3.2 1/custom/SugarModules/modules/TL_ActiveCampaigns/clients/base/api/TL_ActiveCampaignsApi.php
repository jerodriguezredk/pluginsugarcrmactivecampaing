<?php

if (!defined('sugarEntry') || !sugarEntry)
    die('Not A Valid Entry Point');
//http://localhost/sugar_activecampaign/rest/v11_4/TL_ActiveCampaigns/SyncAcContacts
//http://localhost/sugar_activecampaign/rest/v11_4/TL_ActiveCampaigns/UpdateAcContacts

require_once('modules/TL_ActiveCampaigns/ActiveCampaign_class.php');

class TL_ActiveCampaignsApi extends ModuleApi
{

    public function registerApiRest()
    {
        return array(
            'SyncTL_ActiveCampaignsContacts' => array(
                'reqType' => array('POST'),
                'noLoginRequired' => true,
                'path' => array('TL_ActiveCampaigns', 'SyncAcContacts'),
                'pathVars' => array('module', 'action'),
                'method' => 'SyncContactz',
                'shortHelp' => 'create newly created contact in crm also.',
                'longHelp' => '',
            ),
            'UpdateTL_ActiveCampaignsContacts' => array(
                'reqType' => array('POST'),
                'noLoginRequired' => true,
                'path' => array('TL_ActiveCampaigns', 'UpdateAcContacts'),
                'pathVars' => array('module', 'action'),
                'method' => 'UpdateContactz',
                'shortHelp' => 'Update contact in crm.',
                'longHelp' => '',
            ),
            'UpdateTL_ActiveCampaignsContacts' => array(
                'reqType' => array('POST'),
                'noLoginRequired' => true,
                'path' => array('TL_ActiveCampaigns', 'CampaignStats'),
                'pathVars' => array('module', 'action'),
                'method' => 'GetCampaignStats',
                'shortHelp' => 'Save Campaing Stats.',
                'longHelp' => '',
            ),
        );
    }

    public function SyncContactz($api, $args)
    {
        $args = (array) $_REQUEST;
        if (isset($args['type']) && $args['type'] == "subscribe") {
            $this->create_contact($args);
        }
    }

    public function UpdateContactz($api, $args)
    {
        $args = (array) $_REQUEST;
        if (isset($args['type']) && $args['type'] == "update") {
            $this->update_contact($args);
        }
    }

    public function GetCampaignStats($api, $args)
    {
        $args = (array) $_REQUEST;
        $ac_class_obj = new ActiveCampaign_class();
        if (isset($args['type']) && !empty($args['type'])) {
            $sent_date_time = $args['date_time'];
            $campaign_id = $args['campaign']['id'];
            $campaign_name = $args['campaign']['name'];
            if (strtolower($args['type']) == "sent") {
                $GLOBALS['log']->debug("<==================================Campaign is sent==================================>");
                if (isset($args['list'])) {
                    foreach ($args['list'] as $list) {
                        $contacts = $this->get_contacts_by_list($list['id'], $ac_class_obj);
                        foreach ($contacts as $kntct) {
                            $this->add_campaign_stat($kntct, $sent_date_time, $campaign_id, $campaign_name, $ac_class_obj, "sent");
                            $GLOBALS['log']->debug("<==================================sent==================================>");
                        }
                    }
                }
            } else if (strtolower($args['type']) == "open") {
                $GLOBALS['log']->debug("<==================================Campaign is Opend==================================>");
                $this->add_campaign_stat($args['contact'], $sent_date_time, $campaign_id, $campaign_name, $ac_class_obj, "opened");
            } else if (strtolower($args['type']) == "click") {
                $GLOBALS['log']->debug("<==================================Campaign is Clicked==================================>");
                $this->add_campaign_stat($args['contact'], $sent_date_time, $campaign_id, $campaign_name, $ac_class_obj, "clicked");
            }
        }
    }

    private function create_contact($args)
    {
        try {
            $GLOBALS['log']->debug("In Create Contact Function ");
            $ac_contact = $args['contact'];
            $exist = $this->check_duplication($ac_contact['email'], $ac_contact['id']);
            if (!$exist && $args['list'] == 0) {
                $obj = new Contact();
                $obj->first_name = $ac_contact['first_name'];
                $obj->last_name =  $ac_contact['last_name'];
                $obj->phone_mobile = $ac_contact['phone'];
                $obj->active_campaign_id_c = $ac_contact['id'];
                $new_contact_id = $obj->save();
                $obj->emailAddress->addAddress($ac_contact['email'], true, false, false, false, null);
                $obj->emailAddress->save($new_contact_id, "Contacts");
                $GLOBALS['log']->debug("New Contact Created...");
            } else {
                $GLOBALS['log']->debug("Duplicate Record Found...");
                $this->update_contact($args, $exist);
            }
        } catch (Exception $e) {
            $GLOBALS['log']->fatal("ActiveCampaign Exception: " . basename(__FILE__) . ":" . __LINE__);
            $GLOBALS['log']->fatal($e->getMessage());
        }
    }

    private function update_contact($args, $exist_contact_id = false)
    {
        try {
            $GLOBALS['log']->debug("In Update Function...");
            $ac_contact = $args['contact'];
            if (isset($ac_contact['id'])) {
                $obj = new Contact();
                if ($exist_contact_id) {
                    $GLOBALS['log']->debug("Existing contact id :- $exist_contact_id");
                    $obj->retrieve_by_string_fields(array("Contacts.id" => $exist_contact_id));
                } else {
                    $obj->retrieve_by_string_fields(array("active_campaign_id_c" => $ac_contact['id']));
                }
                if ($obj->id && !empty($obj->id)) {
                    $obj->first_name = $ac_contact['first_name'];
                    $obj->last_name =  $ac_contact['last_name'];
                    $obj->phone_mobile = $ac_contact['phone'];
                    $obj->active_campaign_id_c = $ac_contact['id'];
                    $contact_id = $obj->save();
                    $obj->emailAddress->addAddress($ac_contact['email'], true, false, false, false, null);
                    $obj->emailAddress->save($contact_id, "Contacts");
                    $GLOBALS['log']->debug("Contact Updated...");
                }
            }
        } catch (Exception $e) {
            $GLOBALS['log']->fatal("ActiveCampaign Exception: " . basename(__FILE__) . ":" . __LINE__);
            $GLOBALS['log']->fatal($e->getMessage());
        }
    }

    private function check_duplication($email, $ac_contact_id)
    {
        global $db;
        $GLOBALS['log']->debug("In Duplicate Function...");
        $found = "";
        $sql = "SELECT 
                    id_c
                FROM
                    contacts_cstm
                WHERE
                    active_campaign_id_c = '$ac_contact_id'";
        $result = $db->query($sql);
        while ($row = $db->fetchByAssoc($result)) {
            $found = $row['id_c'];
        }
        if (!$found) {
            $sql = "SELECT 
                        email_addr_bean_rel.bean_module,
                        email_addr_bean_rel.bean_id,
                        email_addresses.email_address
                    FROM
                        email_addr_bean_rel
                            INNER JOIN
                        email_addresses ON email_addresses.id = email_addr_bean_rel.email_address_id
                            AND email_addr_bean_rel.deleted = 0
                            INNER JOIN 
                            contacts as c on c.id = email_addr_bean_rel.bean_id
                    WHERE
                        email_addresses.deleted = 0 
                        AND c.deleted = 0 
                        AND email_addresses.email_address = '$email'";
            $result = $db->query($sql);
            while ($row = $db->fetchByAssoc($result)) {
                if ($row['bean_module'] == "Contacts") {
                    $found = $row['bean_id'];
                }
            }
        }
        /* return founded contact id */
        return $found;
    }

    private function add_campaign_stat($kntct, $activity_date_time, $campaign_id, $campaign_name, $ac_class_obj, $status)
    {
        if ($kntct) {
            $old_date_timestamp = strtotime($activity_date_time);
            $activity_date_time = date('Y-m-d H:i:s', $old_date_timestamp);
            $related_modules_ids = $this->get_contact_related_module_and_ids($kntct['id'], $ac_class_obj);

            $is_already_stats_added = $this->already_stats_added($campaign_id, $related_modules_ids);
            if ($is_already_stats_added) {
                $this->update_active_campaign_status($is_already_stats_added,$status);
            } else {
                $u = BeanFactory::getBean('Users','1');
                $privateTeamID = $u->getAllTeams();
                $team_id = "";

                foreach ($privateTeamID as $key => $value) {
                    if($value == "Global"){
                        $team_id = $key;
                        break;
                    }
                }

                $obj = new TL_ActiveCampaigns();
                $obj->name = $campaign_name;
                $obj->activity_date = $activity_date_time;
                $obj->ac_campaign_id = $campaign_id;
                $obj->status = $status;
                $obj->team_id = $team_id;
                $obj->team_set_id = $team_id;
                $record_id = $obj->save();

                /**
                 * Create Relationships...
                 */
                if ($related_modules_ids['CRM_SOURCE'] == "Contacts") {
                    $this->create_relation_with_contact($related_modules_ids['CRM_RECORD_ID'], $record_id);
                } else if ($related_modules_ids['CRM_SOURCE'] == "Accounts") {
                    $this->create_relation_with_account($related_modules_ids['CRM_RECORD_ID'], $record_id);
                } else if ($related_modules_ids['CRM_SOURCE'] == "Leads") {
                    $this->create_relation_with_lead($related_modules_ids['CRM_RECORD_ID'], $record_id);
                }
            }
        }
    }

    private function create_relation_with_contact($crm_record_id, $record_id)
    {
        if ($crm_record_id) {
            global $db;
            $sql = "select id from contacts where id='$crm_record_id'";
            $result = $db->query($sql);
            if ($result) {
                $row = $db->fetchByAssoc($result);
                if ($row['id']) {
                    $date_modified = date("Y-m-d H:i:s");
                    $id = create_guid();
                    $sql = "INSERT INTO tl_activecampaigns_contacts_c  
                            (id, tl_activecampaigns_contactscontacts_ida, tl_activecampaigns_contactstl_activecampaigns_idb, date_modified,deleted)
                            VALUES ('$id','$crm_record_id','$record_id','$date_modified','0')";
                    $result = $db->query($sql);
                }
            }
        }
    }

    private function create_relation_with_account($crm_record_id, $record_id)
    {
        if ($crm_record_id) {
            global $db;
            $sql = "select id from accounts where id='$crm_record_id'";
            $result = $db->query($sql);
            if ($result) {
                $row = $db->fetchByAssoc($result);
                if ($row['id']) {
                    $date_modified = date("Y-m-d H:i:s");
                    $id = create_guid();
                    $sql = "INSERT INTO tl_activecampaigns_accounts_c  
                        (id, tl_activecampaigns_accountsaccounts_ida, tl_activecampaigns_accountstl_activecampaigns_idb, date_modified,deleted)
                        VALUES ('$id','$crm_record_id','$record_id','$date_modified','0')";
                    $result = $db->query($sql);
                }
            }
        }
    }

    private function create_relation_with_lead($crm_record_id, $record_id)
    {
        if ($crm_record_id) {
            global $db;
            $sql = "select id from leads where id='$crm_record_id'";
            $result = $db->query($sql);
            if ($result) {
                $row = $db->fetchByAssoc($result);
                if ($row['id']) {
                    $date_modified = date("Y-m-d H:i:s");
                    $id = create_guid();
                    $sql = "INSERT INTO tl_activecampaigns_leads_c  
                        (id, tl_activecampaigns_leadsleads_ida, tl_activecampaigns_leadstl_activecampaigns_idb, date_modified,deleted)
                        VALUES ('$id','$crm_record_id','$record_id','$date_modified','0')";
                    $result = $db->query($sql);
                }
            }
        }
    }

    private function get_contacts_by_list($list_id, $ac_class_obj)
    {
        $return  = array();
        $result = $ac_class_obj->get_data("api/3/contacts?listid=$list_id");
        if (isset($result['contacts'])) {
            $return =  $result['contacts'];
        }
        return $return;
    }

    private function get_contact_related_module_and_ids($contact_id, $ac_class_obj)
    {
        try {
            $result = $ac_class_obj->get_data("api/3/contacts/$contact_id/fieldValues");
            if ($result) {
                if (isset($result['fieldValues'])) {
                    $return = array();
                    foreach ($result['fieldValues'] as $field) {
                        $return[$this->get_field_name($field['id'], $ac_class_obj)] = trim($field['value']);
                    }
                }
            }
        } catch (Exception $e) {
            $GLOBALS['log']->fatal("ActiveCampaign Exception: " . basename(__FILE__) . ":" . __LINE__);
            $GLOBALS['log']->fatal($e->getMessage());
            $return = array();
        }
        return $return;
    }

    private function get_field_name($field_id, $ac_class_obj)
    {
        $field_name = "";
        try {
            $result = $ac_class_obj->get_data("api/3/fieldValues/$field_id/field");
            if (isset($result['field'])) {
                $field_name = $result['field']['perstag'];
            }
        } catch (Exception $e) {
            $GLOBALS['log']->fatal("ActiveCampaign Exception: " . basename(__FILE__) . ":" . __LINE__);
            $GLOBALS['log']->fatal($e->getMessage());
            $field_name = "";
        }
        return $field_name;
    }

    private function already_stats_added($campaign_id, $related_modules_ids)
    {
        global $db;
        $relalted_record_id = $related_modules_ids['CRM_RECORD_ID'];
        $join = "";
        $where = "";
        if ($related_modules_ids['CRM_SOURCE'] == "Contacts") {
            $join = "tl_activecampaigns_contacts_c AS ac_c ON ac_c.tl_activecampaigns_contactstl_activecampaigns_idb = ac.id";
            $where = " ac_c.tl_activecampaigns_contactscontacts_ida = '$relalted_record_id' ";
        } else if ($related_modules_ids['CRM_SOURCE'] == "Accounts") {
            $join = "tl_activecampaigns_accounts_c AS ac_a ON ac_a.tl_activecampaigns_accountstl_activecampaigns_idb = ac.id";
            $where = " ac_a.tl_activecampaigns_accountsaccounts_ida = '$relalted_record_id' ";
        } else if ($related_modules_ids['CRM_SOURCE'] == "Leads") {
            $join = "tl_activecampaigns_leads_c AS ac_l ON ac_l.tl_activecampaigns_leadstl_activecampaigns_idb = ac.id";
            $where = " ac_l.tl_activecampaigns_leadsleads_ida = '$relalted_record_id' ";
        }
        $sql = "SELECT 
                    ac.id as crm_ac_id
                FROM
                    tl_activecampaigns AS ac
                        INNER JOIN
                    $join
                WHERE
                 ac.ac_campaign_id = '$campaign_id' AND ac.deleted = 0 AND $where";

        
        $result = $db->query($sql);
        if ($result->num_rows > 0) {
            $row = $db->fetchByAssoc($result);
            return $row['crm_ac_id'];
        } else {
            return false;
        }
    }

    private function update_active_campaign_status($ac_id,$status){
        global $db;
        $sql = "UPDATE tl_activecampaigns 
                SET 
                    status = '$status'
                WHERE
                    id = '$ac_id'";
        $result = $db->query($sql);
    }
}
