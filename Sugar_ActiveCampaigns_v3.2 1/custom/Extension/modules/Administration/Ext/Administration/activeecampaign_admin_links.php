<?php

if(!isset($admin_options_defs) || !is_array($admin_options_defs)){
    $admin_options_defs = array();
}
$admin_options_defs['Administration']['ActiveCampaign License'] = array('TL_ActiveCampaigns',
    'License Validation',
    'Manage / Configure License for ActiveCampaign Plugin',
    'javascript:parent.SUGAR.App.router.navigate("#bwc/index.php?module=TL_ActiveCampaigns&action=license", {trigger: true});'
);
$admin_options_defs['Administration']['ActiveCampaign Plugin Configuration'] = array(
    'TL_ActiveCampaigns',
    'ActiveCampaign Api Configuration',
    'Set Api for Syncing data',
    'javascript:parent.SUGAR.App.router.navigate("#bwc/index.php?module=TL_ActiveCampaigns&action=config", {trigger: true});'
);
$admin_group_header[] = array(
    'ActiveCampaign Configuration ',
    '',
    false,
    $admin_options_defs,
    'Manage / configure license and access keys for ActiveCampaign plugin'
);
if(!isset($config_categories) || !is_array($config_categories)) {
    $config_categories = array();
}
$config_categories[] = 'TL_ActiveCampaigns';
?>