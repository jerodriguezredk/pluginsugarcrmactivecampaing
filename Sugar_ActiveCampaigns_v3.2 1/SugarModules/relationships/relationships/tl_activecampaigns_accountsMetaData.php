<?php
// created: 2019-12-10 11:11:54
$dictionary["tl_activecampaigns_accounts"] = array (
  'true_relationship_type' => 'one-to-many',
  'relationships' => 
  array (
    'tl_activecampaigns_accounts' => 
    array (
      'lhs_module' => 'Accounts',
      'lhs_table' => 'accounts',
      'lhs_key' => 'id',
      'rhs_module' => 'TL_ActiveCampaigns',
      'rhs_table' => 'tl_activecampaigns',
      'rhs_key' => 'id',
      'relationship_type' => 'many-to-many',
      'join_table' => 'tl_activecampaigns_accounts_c',
      'join_key_lhs' => 'tl_activecampaigns_accountsaccounts_ida',
      'join_key_rhs' => 'tl_activecampaigns_accountstl_activecampaigns_idb',
    ),
  ),
  'table' => 'tl_activecampaigns_accounts_c',
  'fields' => 
  array (
    'id' => 
    array (
      'name' => 'id',
      'type' => 'id',
    ),
    'date_modified' => 
    array (
      'name' => 'date_modified',
      'type' => 'datetime',
    ),
    'deleted' => 
    array (
      'name' => 'deleted',
      'type' => 'bool',
      'default' => 0,
    ),
    'tl_activecampaigns_accountsaccounts_ida' => 
    array (
      'name' => 'tl_activecampaigns_accountsaccounts_ida',
      'type' => 'id',
    ),
    'tl_activecampaigns_accountstl_activecampaigns_idb' => 
    array (
      'name' => 'tl_activecampaigns_accountstl_activecampaigns_idb',
      'type' => 'id',
    ),
  ),
  'indices' => 
  array (
    0 => 
    array (
      'name' => 'idx_tl_activecampaigns_accounts_pk',
      'type' => 'primary',
      'fields' => 
      array (
        0 => 'id',
      ),
    ),
    1 => 
    array (
      'name' => 'idx_tl_activecampaigns_accounts_ida1_deleted',
      'type' => 'index',
      'fields' => 
      array (
        0 => 'tl_activecampaigns_accountsaccounts_ida',
        1 => 'deleted',
      ),
    ),
    2 => 
    array (
      'name' => 'idx_tl_activecampaigns_accounts_idb2_deleted',
      'type' => 'index',
      'fields' => 
      array (
        0 => 'tl_activecampaigns_accountstl_activecampaigns_idb',
        1 => 'deleted',
      ),
    ),
    3 => 
    array (
      'name' => 'tl_activecampaigns_accounts_alt',
      'type' => 'alternate_key',
      'fields' => 
      array (
        0 => 'tl_activecampaigns_accountstl_activecampaigns_idb',
      ),
    ),
  ),
);