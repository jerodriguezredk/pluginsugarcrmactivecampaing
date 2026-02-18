<?php

/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

$dictionary['TL_ActiveCampaigns'] = array(
    'table' => 'tl_activecampaigns',
    'audited' => true,
    'activity_enabled' => false,
    'duplicate_merge' => true,
    'fields' => array (
  'activity_date' => 
  array (
    'required' => false,
    'name' => 'activity_date',
    'vname' => 'LBL_ACTIVITY_DATE',
    'type' => 'datetimecombo',
    'massupdate' => true,
    'no_default' => false,
    'comments' => '',
    'help' => '',
    'importable' => 'true',
    'duplicate_merge' => 'enabled',
    'duplicate_merge_dom_value' => '1',
    'audited' => false,
    'reportable' => true,
    'unified_search' => false,
    'merge_filter' => 'disabled',
    'pii' => false,
    'calculated' => false,
    'size' => '20',
    'enable_range_search' => false,
    'dbType' => 'datetime',
  ),
  'ac_campaign_id' => 
  array (
    'required' => false,
    'name' => 'ac_campaign_id',
    'vname' => 'LBL_AC_CAMPAIGN_ID',
    'type' => 'int',
    'massupdate' => false,
    'no_default' => false,
    'comments' => '',
    'help' => '',
    'importable' => 'true',
    'duplicate_merge' => 'enabled',
    'duplicate_merge_dom_value' => '1',
    'audited' => false,
    'reportable' => true,
    'unified_search' => false,
    'merge_filter' => 'disabled',
    'pii' => false,
    'default' => '',
    'full_text_search' => 
    array (
      'enabled' => '0',
      'boost' => '1',
      'searchable' => false,
    ),
    'calculated' => false,
    'len' => '255',
    'size' => '20',
    'enable_range_search' => false,
    'disable_num_format' => '',
    'min' => false,
    'max' => false,
  ),
  'status' => 
  array (
    'required' => false,
    'name' => 'status',
    'vname' => 'LBL_STATUS',
    'type' => 'enum',
    'massupdate' => true,
    'no_default' => false,
    'comments' => '',
    'help' => '',
    'importable' => 'true',
    'duplicate_merge' => 'enabled',
    'duplicate_merge_dom_value' => '1',
    'audited' => false,
    'reportable' => true,
    'unified_search' => false,
    'merge_filter' => 'disabled',
    'pii' => false,
    'calculated' => false,
    'len' => 100,
    'size' => '20',
    'options' => 'active_campaign_status_list',
    'default' => '',
    'dependency' => false,
  ),
),
    'relationships' => array (
),
    'optimistic_locking' => true,
    'unified_search' => false,
    'full_text_search' => false,
);

if (!class_exists('VardefManager')){
}
VardefManager::createVardef('TL_ActiveCampaigns','TL_ActiveCampaigns', array('basic','team_security','assignable','taggable'));