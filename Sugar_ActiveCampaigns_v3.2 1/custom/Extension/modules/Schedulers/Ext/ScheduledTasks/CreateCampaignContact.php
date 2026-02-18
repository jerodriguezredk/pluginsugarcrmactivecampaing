<?php

array_push($job_strings, 'CreateCampaignContact');

function CreateCampaignContact()
{
    require_once('modules/TL_ActiveCampaigns/Scheduler/sync_existing_contacts.php');
    return 'success';
}
