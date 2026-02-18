<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

require_once('include/MVC/View/SugarView.php');

require_once('modules/TL_ActiveCampaigns/ActiveCampaign_class.php');

class ViewMapping extends SugarView
{
    function preDisplay()
    {
        global $current_user;

        if (!is_admin($current_user)) {
            !is_admin_for_module($GLOBALS['current_user'], 'TL_ActiveCampaigns');
            sugar_die("Unauthorized access to administration.");
        }
    }

    protected function _getModuleTitleParams($browserTitle = false)
    {
        global $mod_strings;

        return array(
            "<a href='index.php?module=Administration&action=index'>" . translate('LBL_MODULE_NAME', 'Administration') . "</a>",
            translate('ActiveCampaign Configuration', 'Administration'),
        );
    }

    public function display()
    {
        global $mod_strings, $app_strings, $currentModule, $app_list_strings;
        $meta = BeanFactory::newBean('Contacts');
        $sugarContactsAttributes = array();
        $ignoreTypes = array("relate", "link");
        foreach ($meta->getFieldDefinitions() as $key => $value) {
            if(isset($value['type']) && in_array($value['type'], $ignoreTypes)){
                continue;
            }
            $label = $value["vname"] ?? $key;
            if($label){
                $label = translate($label, 'Contacts');
            }
            $sugarContactsAttributes[$key] = str_replace(":", "", $label);
        }
        $contact_mapping_array = array();
        $Administration = new Administration();
        $focus = $Administration->retrieveSettings();
        if (isset($focus->settings['TL_ActiveCampaigns_contact_mapping_array'])) {
            $val = (string)$focus->settings['TL_ActiveCampaigns_contact_mapping_array'];
            $frmDec = urldecode($val);
            $contact_mapping_array = unserialize($frmDec);
        } else {
            $contact_mapping_array = array();
        }
        if ((array_key_exists('email', $contact_mapping_array)) !== false) {
            unset($contact_mapping_array['email']);
        }
        if ((array_key_exists('email', $sugarContactsAttributes)) !== false) {
            unset($sugarContactsAttributes['email']);
        }
        $leadmeta = BeanFactory::newBean('Leads');
        $contractmeta = BeanFactory::newBean('Contracts');
        $sugarLeadsAttributes = array();
        $sugarContractsAttributes = array();
        foreach ($leadmeta->getFieldDefinitions() as $key => $value) {
            if(isset($value['type']) && in_array($value['type'], $ignoreTypes)){
                continue;
            }
            $label = $value["vname"] ?? $key;
            if($label){
                $label = translate($label, 'Leads');
            }
            $sugarLeadsAttributes[$key] = str_replace(":", "", $label);
        }
        foreach ($contractmeta->getFieldDefinitions() as $key => $value) {
            if(isset($value['type']) && in_array($value['type'], $ignoreTypes)){
                continue;
            }
            $label = $value["vname"] ?? $key;
            if($label){
                $label = translate($label, 'Contracts');
            }
            $sugarContractsAttributes[$key] = str_replace(":", "", $label);
        }
        $lead_mapping_array = array();
        $contract_mapping_array = array();
        $Administration = new Administration();
        $focus = $Administration->retrieveSettings();
        if (isset($focus->settings['TL_ActiveCampaigns_lead_mapping_array'])) {
            $val = (string)$focus->settings['TL_ActiveCampaigns_lead_mapping_array'];
            $frmDec = urldecode($val);
            $lead_mapping_array = unserialize($frmDec);
        } else {
            $lead_mapping_array = array();
        }
        if (isset($focus->settings['TL_ActiveCampaigns_contract_mapping_array'])) {
            $val = (string)$focus->settings['TL_ActiveCampaigns_contract_mapping_array'];
            $frmDec = urldecode($val);
            $contract_mapping_array = unserialize($frmDec);
        } else {
            $contract_mapping_array = array();
        }
        if ((array_key_exists('email', $lead_mapping_array)) !== false) {
            unset($lead_mapping_array['email']);
        }
        if ((array_key_exists('email', $sugarLeadsAttributes)) !== false) {
            unset($sugarLeadsAttributes['email']);
        }
        if ((array_key_exists('email', $contract_mapping_array)) !== false) {
            unset($contract_mapping_array['email']);
        }
        if ((array_key_exists('email', $sugarContractsAttributes)) !== false) {
            unset($sugarContractsAttributes['email']);
        }
        $accountmeta = BeanFactory::newBean('Accounts');
        $sugarAccountsAttributes = array();
        foreach ($accountmeta->getFieldDefinitions() as $key => $value) {
            if(isset($value['type']) && in_array($value['type'], $ignoreTypes)){
                continue;
            }
            $label = $value["vname"] ?? $key;
            if($label){
                $label = translate($label, 'Accounts');
            }
            $sugarAccountsAttributes[$key] = str_replace(":", "", $label);
        }
        $account_mapping_array = array();
        $Administration = new Administration();
        $focus = $Administration->retrieveSettings();
        if (isset($focus->settings['TL_ActiveCampaigns_account_mapping_array'])) {
            $val = (string)$focus->settings['TL_ActiveCampaigns_account_mapping_array'];
            $frmDec = urldecode($val);
            $account_mapping_array = unserialize($frmDec);
        } else {
            $account_mapping_array = array();
        }
        if ((array_key_exists('email', $account_mapping_array)) !== false) {
            unset($account_mapping_array['email']);
        }
        if ((array_key_exists('email', $sugarAccountsAttributes)) !== false) {
            unset($sugarAccountsAttributes['email']);
        }
        $active_campaign_obj = new ActiveCampaign_class();
        $campaign_custom_fields = $active_campaign_obj->get_custom_fields();
        $campaign_custom_fields["first_name"] = "First Name";
        $campaign_custom_fields["last_name"] = "Last Name";
        $campaign_custom_fields["phone"] = "Phone";

        if(isset($campaign_custom_fields[AcConstants::CRM_SOURCE])){
            unset($campaign_custom_fields[AcConstants::CRM_SOURCE]);
        }
        if(isset($campaign_custom_fields[AcConstants::CRM_RECORD_ID])){
            unset($campaign_custom_fields[AcConstants::CRM_RECORD_ID]);
        }
        $this->ss->assign("MOD", $mod_strings);
        $this->ss->assign("APP", $app_strings);
        $this->ss->assign("APP_LIST_STRINGS", $app_list_strings);
        $this->ss->assign("RETURN_MODULE", $currentModule);
        $this->ss->assign("RETURN_ACTION", "mapping");
        $this->ss->assign("MODULE", $currentModule);
        $this->ss->assign("campaign_custom_fields", $campaign_custom_fields);
        $this->ss->assign("sugar_contacts_attributes", $sugarContactsAttributes);
        $this->ss->assign("contactMappedInfo", $contact_mapping_array);
        $this->ss->assign("sugar_leads_attributes", $sugarLeadsAttributes);
        $this->ss->assign("leadMappedInfo", $lead_mapping_array);
        $this->ss->assign("sugar_contracts_attributes", $sugarContractsAttributes);
        $this->ss->assign("contractMappedInfo", $contract_mapping_array);
        $this->ss->assign("sugar_accounts_attributes", $sugarAccountsAttributes);
        $this->ss->assign("accountMappedInfo", $account_mapping_array);
        $this->ss->assign("PRINT_URL", "index.php?" . $GLOBALS['request_string']);
        $this->ss->assign("JAVASCRIPT", '<script src="modules/' . $currentModule . '/js/fields_mappings.js"></script>');
        $this->ss->display("modules/$currentModule/tpls/mapping.tpl");
    }
}
