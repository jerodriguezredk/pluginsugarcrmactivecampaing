<?php
$module_name = 'TL_ActiveCampaigns';
$viewdefs[$module_name] = 
array (
  'base' => 
  array (
    'view' => 
    array (
      'list' => 
      array (
        'panels' => 
        array (
          0 => 
          array (
            'label' => 'LBL_PANEL_1',
            'fields' => 
            array (
              0 => 
              array (
                'name' => 'name',
                'label' => 'LBL_NAME',
                'default' => true,
                'enabled' => true,
                'link' => true,
              ),
              1 => 
              array (
                'name' => 'tl_activecampaigns_contacts_name',
                'label' => 'LBL_TL_ACTIVECAMPAIGNS_CONTACTS_FROM_CONTACTS_TITLE',
                'enabled' => true,
                'id' => 'TL_ACTIVECAMPAIGNS_CONTACTSCONTACTS_IDA',
                'link' => true,
                'sortable' => false,
                'default' => true,
              ),
              2 => 
              array (
                'name' => 'tl_activecampaigns_leads_name',
                'label' => 'LBL_TL_ACTIVECAMPAIGNS_LEADS_FROM_LEADS_TITLE',
                'enabled' => true,
                'id' => 'TL_ACTIVECAMPAIGNS_LEADSLEADS_IDA',
                'link' => true,
                'sortable' => false,
                'default' => true,
              ),
              3 => 
              array (
                'name' => 'tl_activecampaigns_accounts_name',
                'label' => 'LBL_TL_ACTIVECAMPAIGNS_ACCOUNTS_FROM_ACCOUNTS_TITLE',
                'enabled' => true,
                'id' => 'TL_ACTIVECAMPAIGNS_ACCOUNTSACCOUNTS_IDA',
                'link' => true,
                'sortable' => false,
                'default' => true,
              ),
              4 => 
              array (
                'name' => 'status',
                'label' => 'LBL_STATUS',
                'enabled' => true,
                'default' => true,
              ),
              5 => 
              array (
                'name' => 'activity_date',
                'label' => 'LBL_ACTIVITY_DATE',
                'enabled' => true,
                'default' => true,
              ),
              6 => 
              array (
                'name' => 'team_name',
                'label' => 'LBL_TEAM',
                'default' => false,
                'enabled' => true,
              ),
            ),
          ),
        ),
        'orderBy' => 
        array (
          'field' => 'date_modified',
          'direction' => 'desc',
        ),
      ),
    ),
  ),
);
