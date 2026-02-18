<?php
// created: 2019-12-10 11:11:54
$dictionary["tl_activecampaigns_contacts"] = array (
  'true_relationship_type' => 'one-to-many',
  'relationships' => 
  array (
    'tl_activecampaigns_contacts' => 
    array (
      'lhs_module' => 'Contacts',
      'lhs_table' => 'contacts',
      'lhs_key' => 'id',
      'rhs_module' => 'TL_ActiveCampaigns',
      'rhs_table' => 'tl_activecampaigns',
      'rhs_key' => 'id',
      'relationship_type' => 'many-to-many',
      'join_table' => 'tl_activecampaigns_contacts_c',
      'join_key_lhs' => 'tl_activecampaigns_contactscontacts_ida',
      'join_key_rhs' => 'tl_activecampaigns_contactstl_activecampaigns_idb',
    ),
  ),
  'table' => 'tl_activecampaigns_contacts_c',
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
    'tl_activecampaigns_contactscontacts_ida' => 
    array (
      'name' => 'tl_activecampaigns_contactscontacts_ida',
      'type' => 'id',
    ),
    'tl_activecampaigns_contactstl_activecampaigns_idb' => 
    array (
      'name' => 'tl_activecampaigns_contactstl_activecampaigns_idb',
      'type' => 'id',
    ),
  ),
  'indices' => 
  array (
    0 => 
    array (
      'name' => 'idx_tl_activecampaigns_contacts_pk',
      'type' => 'primary',
      'fields' => 
      array (
        0 => 'id',
      ),
    ),
    1 => 
    array (
      'name' => 'idx_tl_activecampaigns_contacts_ida1_deleted',
      'type' => 'index',
      'fields' => 
      array (
        0 => 'tl_activecampaigns_contactscontacts_ida',
        1 => 'deleted',
      ),
    ),
    2 => 
    array (
      'name' => 'idx_tl_activecampaigns_contacts_idb2_deleted',
      'type' => 'index',
      'fields' => 
      array (
        0 => 'tl_activecampaigns_contactstl_activecampaigns_idb',
        1 => 'deleted',
      ),
    ),
    3 => 
    array (
      'name' => 'tl_activecampaigns_contacts_alt',
      'type' => 'alternate_key',
      'fields' => 
      array (
        0 => 'tl_activecampaigns_contactstl_activecampaigns_idb',
      ),
    ),
  ),
);