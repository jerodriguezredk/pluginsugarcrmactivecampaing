<?php

$hook_array['before_save'][] = array(
    //Processing index. For sorting the array.
    11,

    //Label. A string value to identify the hook.
    'save account in active campaign',

    //The PHP file where your class is located.
    'custom/modules/Accounts/logic_hook/CampaignAccount.php',

    //The class the method is in.
    'CampaignAccount',

    //The method to call.
    'save_account'
);

$hook_array['after_save'][] = array(
    //Processing index. For sorting the array.
    11,

    //Label. A string value to identify the hook.
    'save account in active campaign',

    //The PHP file where your class is located.
    'custom/modules/Accounts/logic_hook/CampaignAccount.php',

    //The class the method is in.
    'CampaignAccount',

    //The method to call.
    'link_to_active_campaign'
);

$hook_array['after_delete'][] = array(
    //Processing index. For sorting the array.
    11,

    //Label. A string value to identify the hook.
    'delete account from active campaign',

    //The PHP file where your class is located.
    'custom/modules/Accounts/logic_hook/CampaignAccount.php',

    //The class the method is in.
    'CampaignAccount',

    //The method to call.
    'delete_contact_from_ac'
);
