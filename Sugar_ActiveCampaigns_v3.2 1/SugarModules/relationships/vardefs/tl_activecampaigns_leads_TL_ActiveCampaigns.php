<?php
// created: 2019-12-10 11:11:54
$dictionary["TL_ActiveCampaigns"]["fields"]["tl_activecampaigns_leads"] = array (
  'name' => 'tl_activecampaigns_leads',
  'type' => 'link',
  'relationship' => 'tl_activecampaigns_leads',
  'source' => 'non-db',
  'module' => 'Leads',
  'bean_name' => 'Lead',
  'side' => 'right',
  'vname' => 'LBL_TL_ACTIVECAMPAIGNS_LEADS_FROM_TL_ACTIVECAMPAIGNS_TITLE',
  'id_name' => 'tl_activecampaigns_leadsleads_ida',
  'link-type' => 'one',
);
$dictionary["TL_ActiveCampaigns"]["fields"]["tl_activecampaigns_leads_name"] = array (
  'name' => 'tl_activecampaigns_leads_name',
  'type' => 'relate',
  'source' => 'non-db',
  'vname' => 'LBL_TL_ACTIVECAMPAIGNS_LEADS_FROM_LEADS_TITLE',
  'save' => true,
  'id_name' => 'tl_activecampaigns_leadsleads_ida',
  'link' => 'tl_activecampaigns_leads',
  'table' => 'leads',
  'module' => 'Leads',
  'rname' => 'full_name',
  'db_concat_fields' => 
  array (
    0 => 'first_name',
    1 => 'last_name',
  ),
);
$dictionary["TL_ActiveCampaigns"]["fields"]["tl_activecampaigns_leadsleads_ida"] = array (
  'name' => 'tl_activecampaigns_leadsleads_ida',
  'type' => 'id',
  'source' => 'non-db',
  'vname' => 'LBL_TL_ACTIVECAMPAIGNS_LEADS_FROM_TL_ACTIVECAMPAIGNS_TITLE_ID',
  'id_name' => 'tl_activecampaigns_leadsleads_ida',
  'link' => 'tl_activecampaigns_leads',
  'table' => 'leads',
  'module' => 'Leads',
  'rname' => 'id',
  'reportable' => false,
  'side' => 'right',
  'massupdate' => false,
  'duplicate_merge' => 'disabled',
  'hideacl' => true,
);
