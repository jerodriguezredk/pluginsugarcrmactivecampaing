<?php

array_push($job_strings, 'GetExistingLeadScores');

function GetExistingLeadScores()
{
    require_once('modules/TL_ActiveCampaigns/Scheduler/update_existing_lead_scores.php');
    return 'success';
}
