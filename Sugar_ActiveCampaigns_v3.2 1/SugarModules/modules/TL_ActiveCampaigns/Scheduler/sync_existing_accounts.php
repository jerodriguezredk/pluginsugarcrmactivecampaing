<?php
require_once('modules/TL_ActiveCampaigns/ActiveCampaign_class.php');
require_once('modules/TL_ActiveCampaigns/license/SugarActiveOutfittersLicense.php');
require_once('modules/TL_ActiveCampaigns/PluginLogger.php');

global $current_user, $db;
$currentModule = "TL_ActiveCampaigns";
$validate_license = SugarActiveOutfittersLicense::isValid($currentModule, $current_user->id, false);

if ($validate_license === true) {
    PluginLogger::log('Proceeding with existing accounts sync.');
	$Administration = new Administration();
	$focus = $Administration->retrieveSettings();
	if (isset($focus->settings['TL_ActiveCampaigns_account_existing_sync'])) {
	    $contacts_existing_sync = $focus->settings['TL_ActiveCampaigns_account_existing_sync'];
	} else {
	    $contacts_existing_sync = '';
	}
	if ($contacts_existing_sync == 'on') {
        PluginLogger::log('Existing accounts sync is enabled in settings. Starting accounts sync.');
	    $active_campaign_obj = new ActiveCampaign_class();
	    $res = $active_campaign_obj->create_all_campaign_accounts();
	}else{
        PluginLogger::log('Existing accounts sync is disabled in settings. Skipping accounts sync.');
    }
}else{
    PluginLogger::log('License validation failed for TL_ActiveCampaigns module. Skipping accounts sync. And deactivating the scheduler.');
	$query = "UPDATE schedulers SET status = 'Inactive' WHERE job = 'function::CreateCampaignAccount'";
	$db->query($query);
}
