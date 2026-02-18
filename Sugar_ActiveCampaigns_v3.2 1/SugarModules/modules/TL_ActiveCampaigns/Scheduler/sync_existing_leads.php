<?php
require_once('modules/TL_ActiveCampaigns/ActiveCampaign_class.php');
require_once('modules/TL_ActiveCampaigns/license/SugarActiveOutfittersLicense.php');
require_once('modules/TL_ActiveCampaigns/PluginLogger.php');

global $current_user, $db;
$currentModule = "TL_ActiveCampaigns";
$validate_license = SugarActiveOutfittersLicense::isValid($currentModule, $current_user->id, false);

if ($validate_license === true) {
    PluginLogger::log('Proceeding with existing leads sync.');
	$Administration = new Administration();
	$focus = $Administration->retrieveSettings();
	if (isset($focus->settings['TL_ActiveCampaigns_leads_existing_sync'])) {
	    $leads_existing_sync = $focus->settings['TL_ActiveCampaigns_leads_existing_sync'];
	} else {
	    $leads_existing_sync = '';
	}
	if ($leads_existing_sync == 'on') {
        PluginLogger::log('Existing leads sync is enabled in settings. Starting leads sync.');
	    $active_campaign_obj = new ActiveCampaign_class();
	    $res = $active_campaign_obj->create_all_campaign_leads();
	}else{
        PluginLogger::log('Existing leads sync is disabled in settings. Skipping leads sync.');
    }
}else{
    PluginLogger::log('License validation failed for TL_ActiveCampaigns module. Skipping leads sync. And deactivating the scheduler.');
	$query = "UPDATE schedulers SET status = 'Inactive' WHERE job = 'function::CreateCampaignLead'";
	$db->query($query);
}
