<?php
// created: 2019-11-28 08:03:24
$viewdefs['TL_ActiveCampaigns']['base']['view']['subpanel-for-leads-tl_activecampaigns_leads'] = array (
  'panels' => 
  array (
    0 => 
    array (
      'name' => 'panel_header',
      'label' => 'LBL_PANEL_1',
      'fields' => 
      array (
        0 => 
        array (
          'label' => 'LBL_NAME',
          'enabled' => true,
          'default' => true,
          'name' => 'name',
          'link' => true,
        ),
        1 => 
        array (
          'name' => 'tl_activecampaigns_leads_name',
          'label' => 'LBL_TL_ACTIVECAMPAIGNS_LEADS_FROM_LEADS_TITLE',
          'enabled' => true,
          'id' => 'TL_ACTIVECAMPAIGNS_LEADSLEADS_IDA',
          'link' => true,
          'sortable' => false,
          'default' => true,
        ),
        2 => 
        array (
          'name' => 'status',
          'label' => 'LBL_STATUS',
          'enabled' => true,
          'default' => true,
        ),
        3 => 
        array (
          'name' => 'activity_date',
          'label' => 'LBL_ACTIVITY_DATE',
          'enabled' => true,
          'default' => true,
        ),
      ),
    ),
  ),
  'orderBy' => 
  array (
    'field' => 'date_modified',
    'direction' => 'desc',
  ),
  'type' => 'subpanel-list',
);