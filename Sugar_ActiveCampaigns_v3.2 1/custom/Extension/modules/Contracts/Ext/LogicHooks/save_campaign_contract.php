<?php

$hook_array['before_save'][] = array(
    11,
    'save contact in active campaign',
    'custom/modules/Contracts/logic_hook/CampaignContract.php',
    'CampaignContract',
    'save_contract'
);

$hook_array['after_save'][] = array(
    11,
    'save contact in active campaign',
    'custom/modules/Contracts/logic_hook/CampaignContract.php',
    'CampaignContract',
    'link_to_active_campaign'
);

$hook_array['after_delete'][] = array(
    11,
    'delete contact from active campaign',
    'custom/modules/Contracts/logic_hook/CampaignContract.php',
    'CampaignContract',
    'delete_contract_from_ac'
);
