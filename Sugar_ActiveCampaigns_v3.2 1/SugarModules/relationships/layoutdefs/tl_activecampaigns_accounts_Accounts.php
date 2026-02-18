<?php
 // created: 2019-12-10 11:11:54
$layout_defs["Accounts"]["subpanel_setup"]['tl_activecampaigns_accounts'] = array (
  'order' => 100,
  'module' => 'TL_ActiveCampaigns',
  'subpanel_name' => 'default',
  'sort_order' => 'asc',
  'sort_by' => 'id',
  'title_key' => 'LBL_TL_ACTIVECAMPAIGNS_ACCOUNTS_FROM_TL_ACTIVECAMPAIGNS_TITLE',
  'get_subpanel_data' => 'tl_activecampaigns_accounts',
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
