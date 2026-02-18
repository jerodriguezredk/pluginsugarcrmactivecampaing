<?php
// created: 2019-11-28 07:50:35
$dictionary["TL_ActiveCampaigns"]["fields"]["tl_activecampaigns_accounts"] = array (
  'name' => 'tl_activecampaigns_accounts',
  'type' => 'link',
  'relationship' => 'tl_activecampaigns_accounts',
  'source' => 'non-db',
  'module' => 'Accounts',
  'bean_name' => 'Account',
  'side' => 'right',
  'vname' => 'LBL_TL_ACTIVECAMPAIGNS_ACCOUNTS_FROM_TL_ACTIVECAMPAIGNS_TITLE',
  'id_name' => 'tl_activecampaigns_accountsaccounts_ida',
  'link-type' => 'one',
);
$dictionary["TL_ActiveCampaigns"]["fields"]["tl_activecampaigns_accounts_name"] = array (
  'name' => 'tl_activecampaigns_accounts_name',
  'type' => 'relate',
  'source' => 'non-db',
  'vname' => 'LBL_TL_ACTIVECAMPAIGNS_ACCOUNTS_FROM_ACCOUNTS_TITLE',
  'save' => true,
  'id_name' => 'tl_activecampaigns_accountsaccounts_ida',
  'link' => 'tl_activecampaigns_accounts',
  'table' => 'accounts',
  'module' => 'Accounts',
  'rname' => 'name',
);
$dictionary["TL_ActiveCampaigns"]["fields"]["tl_activecampaigns_accountsaccounts_ida"] = array (
  'name' => 'tl_activecampaigns_accountsaccounts_ida',
  'type' => 'id',
  'source' => 'non-db',
  'vname' => 'LBL_TL_ACTIVECAMPAIGNS_ACCOUNTS_FROM_TL_ACTIVECAMPAIGNS_TITLE_ID',
  'id_name' => 'tl_activecampaigns_accountsaccounts_ida',
  'link' => 'tl_activecampaigns_accounts',
  'table' => 'accounts',
  'module' => 'Accounts',
  'rname' => 'id',
  'reportable' => false,
  'side' => 'right',
  'massupdate' => false,
  'duplicate_merge' => 'disabled',
  'hideacl' => true,
);
