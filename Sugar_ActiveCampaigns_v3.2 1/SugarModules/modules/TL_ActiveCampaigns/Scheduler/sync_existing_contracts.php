<?php
require_once('modules/TL_ActiveCampaigns/ActiveCampaign_class.php');
require_once('modules/TL_ActiveCampaigns/license/SugarActiveOutfittersLicense.php');
require_once('modules/TL_ActiveCampaigns/PluginLogger.php');

global $current_user, $db;
$currentModule = "TL_ActiveCampaigns";
$validate_license = SugarActiveOutfittersLicense::isValid($currentModule, $current_user->id, false);

if ($validate_license === true) {
    PluginLogger::log('Proceeding with existing contracts sync.');
	$Administration = new Administration();
	$focus = $Administration->retrieveSettings();
	if (isset($focus->settings['TL_ActiveCampaigns_contracts_existing_sync'])) {
	    $contracts_existing_sync = $focus->settings['TL_ActiveCampaigns_contracts_existing_sync'];
	} else {
	    $contracts_existing_sync = '';
	}
	if ($contracts_existing_sync == 'on') {
        PluginLogger::log('Existing contracts sync is enabled in settings. Starting contracts sync.');
	    $active_campaign_obj = new ActiveCampaign_class();
	    $active_campaign_obj->create_all_campaign_contracts();
	}else{
        PluginLogger::log('Existing contracts sync is disabled in settings. Skipping contracts sync.');
    }
}else{
    PluginLogger::log('License validation failed for TL_ActiveCampaigns module. Skipping contracts sync. And deactivating the scheduler.');
	$query = "UPDATE schedulers SET status = 'Inactive' WHERE job = 'function::CreateCampaignContract'";
	$db->query($query);
}
