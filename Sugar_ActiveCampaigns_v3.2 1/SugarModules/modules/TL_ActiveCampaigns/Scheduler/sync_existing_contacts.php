<?php
require_once('modules/TL_ActiveCampaigns/ActiveCampaign_class.php');
require_once('modules/TL_ActiveCampaigns/license/SugarActiveOutfittersLicense.php');
require_once('modules/TL_ActiveCampaigns/PluginLogger.php');

global $current_user, $db;
$currentModule = "TL_ActiveCampaigns";
$validate_license = SugarActiveOutfittersLicense::isValid($currentModule, $current_user->id, false);

if ($validate_license === true) {
    PluginLogger::log('Proceeding with existing contacts sync.');
	$Administration = new Administration();
	$focus = $Administration->retrieveSettings();
	if (isset($focus->settings['TL_ActiveCampaigns_contacts_existing_sync'])) {
	    $contacts_existing_sync = $focus->settings['TL_ActiveCampaigns_contacts_existing_sync'];
	} else {
	    $contacts_existing_sync = '';
	}
	if ($contacts_existing_sync == 'on') {
        PluginLogger::log('Existing contacts sync is enabled in settings. Starting contacts sync.');
	    $active_campaign_obj = new ActiveCampaign_class();
	    $res = $active_campaign_obj->create_all_campaign_contacts();
	}else{
        PluginLogger::log('Existing contacts sync is disabled in settings. Skipping contacts sync.');
    }
}else{
    PluginLogger::log('License validation failed for TL_ActiveCampaigns module. Skipping contacts sync. And deactivating the scheduler.');
	$query = "UPDATE schedulers SET status = 'Inactive' WHERE job = 'function::CreateCampaignContact'";
	$db->query($query);
}