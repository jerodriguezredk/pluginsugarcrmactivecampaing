<?php

array_push($job_strings, 'GetLeadScores');

function GetLeadScores()
{
    require_once('modules/TL_ActiveCampaigns/Scheduler/update_lead_scores.php');
    return 'success';
}
