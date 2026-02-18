<?php

array_push($job_strings, 'CreateCampaignContract');

function CreateCampaignContract()
{
    require_once('modules/TL_ActiveCampaigns/Scheduler/sync_existing_contracts.php');
    return 'success';
}
