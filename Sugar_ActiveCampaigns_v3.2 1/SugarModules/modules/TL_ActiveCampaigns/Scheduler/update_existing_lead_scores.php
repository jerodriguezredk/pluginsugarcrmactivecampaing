<?php
require_once('modules/TL_ActiveCampaigns/ActiveCampaign_class.php');
require_once('modules/TL_ActiveCampaigns/license/SugarActiveOutfittersLicense.php');
require_once('modules/TL_ActiveCampaigns/PluginLogger.php');

global $current_user, $db;
$currentModule = "TL_ActiveCampaigns";
$validate_license = SugarActiveOutfittersLicense::isValid($currentModule, $current_user->id, false);

if ($validate_license === true) {
    PluginLogger::log('Proceeding with existing lead scores sync.');
	$Administration = new Administration();
	$focus = $Administration->retrieveSettings();
	if (isset($focus->settings['TL_ActiveCampaigns_lead_score_sync'])) {
	    $lead_score_sync = $focus->settings['TL_ActiveCampaigns_lead_score_sync'];
	} else {
	    $lead_score_sync = '';
	}
	if ($lead_score_sync == 'on') {
        PluginLogger::log('Existing lead scores sync is enabled in settings. Starting lead scores sync.');
	    $active_campaign_obj = new ActiveCampaign_class();
	    $limit = 100;
	    $offset = 0;
	    $res = $active_campaign_obj->update_existing_lead_score($limit, $offset);
	}else{
        PluginLogger::log('Existing lead scores sync is disabled in settings. Skipping lead scores sync.');
    }
}else{
    PluginLogger::log('License validation failed for TL_ActiveCampaigns module. Skipping lead scores sync. And deactivating the scheduler.');
	$query = "UPDATE schedulers SET status = 'Inactive' WHERE job = 'function::GetExistingLeadScores'";
	$db->query($query);
}
