<?php
// created: 2019-11-28 08:03:24
$subpanel_layout['list_fields'] = array (
  'name' => 
  array (
    'vname' => 'LBL_NAME',
    'widget_class' => 'SubPanelDetailViewLink',
    'width' => 10,
    'default' => true,
  ),
  'tl_activecampaigns_leads_name' => 
  array (
    'type' => 'relate',
    'link' => true,
    'vname' => 'LBL_TL_ACTIVECAMPAIGNS_LEADS_FROM_LEADS_TITLE',
    'id' => 'TL_ACTIVECAMPAIGNS_LEADSLEADS_IDA',
    'width' => 10,
    'default' => true,
    'widget_class' => 'SubPanelDetailViewLink',
    'target_module' => 'Leads',
    'target_record_key' => 'tl_activecampaigns_leadsleads_ida',
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