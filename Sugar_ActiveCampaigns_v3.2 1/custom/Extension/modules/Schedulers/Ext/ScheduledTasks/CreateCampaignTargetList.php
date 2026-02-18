<?php

array_push($job_strings, 'CreateCampaignTargetList');

function CreateCampaignTargetList()
{
    require_once('modules/TL_ActiveCampaigns/Scheduler/sync_existing_target_list.php');
    return 'success';
}
