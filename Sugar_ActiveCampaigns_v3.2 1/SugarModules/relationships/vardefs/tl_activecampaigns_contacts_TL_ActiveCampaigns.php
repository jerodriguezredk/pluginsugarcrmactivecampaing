<?php
// created: 2019-12-10 11:11:54
$dictionary["TL_ActiveCampaigns"]["fields"]["tl_activecampaigns_contacts"] = array (
  'name' => 'tl_activecampaigns_contacts',
  'type' => 'link',
  'relationship' => 'tl_activecampaigns_contacts',
  'source' => 'non-db',
  'module' => 'Contacts',
  'bean_name' => 'Contact',
  'side' => 'right',
  'vname' => 'LBL_TL_ACTIVECAMPAIGNS_CONTACTS_FROM_TL_ACTIVECAMPAIGNS_TITLE',
  'id_name' => 'tl_activecampaigns_contactscontacts_ida',
  'link-type' => 'one',
);
$dictionary["TL_ActiveCampaigns"]["fields"]["tl_activecampaigns_contacts_name"] = array (
  'name' => 'tl_activecampaigns_contacts_name',
  'type' => 'relate',
  'source' => 'non-db',
  'vname' => 'LBL_TL_ACTIVECAMPAIGNS_CONTACTS_FROM_CONTACTS_TITLE',
  'save' => true,
  'id_name' => 'tl_activecampaigns_contactscontacts_ida',
  'link' => 'tl_activecampaigns_contacts',
  'table' => 'contacts',
  'module' => 'Contacts',
  'rname' => 'full_name',
  'db_concat_fields' => 
  array (
    0 => 'first_name',
    1 => 'last_name',
  ),
);
$dictionary["TL_ActiveCampaigns"]["fields"]["tl_activecampaigns_contactscontacts_ida"] = array (
  'name' => 'tl_activecampaigns_contactscontacts_ida',
  'type' => 'id',
  'source' => 'non-db',
  'vname' => 'LBL_TL_ACTIVECAMPAIGNS_CONTACTS_FROM_TL_ACTIVECAMPAIGNS_TITLE_ID',
  'id_name' => 'tl_activecampaigns_contactscontacts_ida',
  'link' => 'tl_activecampaigns_contacts',
  'table' => 'contacts',
  'module' => 'Contacts',
  'rname' => 'id',
  'reportable' => false,
  'side' => 'right',
  'massupdate' => false,
  'duplicate_merge' => 'disabled',
  'hideacl' => true,
);
