<?php

array_push($job_strings, 'CreateCampaignLead');

function CreateCampaignLead()
{
    require_once('modules/TL_ActiveCampaigns/Scheduler/sync_existing_leads.php');
    return 'success';
}
