<?php
// created: 2019-11-28 08:02:11
$subpanel_layout['list_fields'] = array (
  'name' => 
  array (
    'vname' => 'LBL_NAME',
    'widget_class' => 'SubPanelDetailViewLink',
    'width' => 10,
    'default' => true,
  ),
  'tl_activecampaigns_contacts_name' => 
  array (
    'type' => 'relate',
    'link' => true,
    'vname' => 'LBL_TL_ACTIVECAMPAIGNS_CONTACTS_FROM_CONTACTS_TITLE',
    'id' => 'TL_ACTIVECAMPAIGNS_CONTACTSCONTACTS_IDA',
    'width' => 10,
    'default' => true,
    'widget_class' => 'SubPanelDetailViewLink',
    'target_module' => 'Contacts',
    'target_record_key' => 'tl_activecampaigns_contactscontacts_ida',
  ),
  'status' => 
  array (
    'type' => 'enum',
    'default' => true,
    'vname' => 'LBL_STATUS',
    'width' => 10,
  ),
  'activity_date' => 
  array (
    'type' => 'datetimecombo',
    'vname' => 'LBL_ACTIVITY_DATE',
    'width' => 10,
    'default' => true,
  ),
);