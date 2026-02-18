<?php
 // created: 2019-11-28 07:50:35
$layout_defs["Contacts"]["subpanel_setup"]['tl_activecampaigns_contacts'] = array (
  'order' => 100,
  'module' => 'TL_ActiveCampaigns',
  'subpanel_name' => 'default',
  'sort_order' => 'asc',
  'sort_by' => 'id',
  'title_key' => 'LBL_TL_ACTIVECAMPAIGNS_CONTACTS_FROM_TL_ACTIVECAMPAIGNS_TITLE',
  'get_subpanel_data' => 'tl_activecampaigns_contacts',
  'top_buttons' => 
  array (
    0 => 
    array (
      'widget_class' => 'SubPanelTopButtonQuickCreate',
    ),
    1 => 
    array (
      'widget_class' => 'SubPanelTopSelectButton',
      'mode' => 'MultiSelect',
    ),
  ),
);
