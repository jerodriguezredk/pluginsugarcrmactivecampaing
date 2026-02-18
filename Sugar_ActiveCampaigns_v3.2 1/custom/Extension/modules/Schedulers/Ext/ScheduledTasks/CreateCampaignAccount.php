<?php

array_push($job_strings, 'CreateCampaignAccount');

function CreateCampaignAccount()
{
    require_once('modules/TL_ActiveCampaigns/Scheduler/sync_existing_accounts.php');
    return 'success';
}
