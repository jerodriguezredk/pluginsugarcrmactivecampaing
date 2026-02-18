<?php
// WARNING: The contents of this file are auto-generated.
?>
<?php
// Merged from custom/Extension/modules/ProspectLists/Ext/LogicHooks/import_to_active_campaign.php


$hook_array['after_relationship_add'][] = array(
    15,
    'Import Account, Contacts, and lead to Active Campaign',
    'custom/modules/ProspectLists/logic_hook/ManageRelations.php',
    'ManageRelations',
    'start_process'
);

$hook_array['after_relationship_delete'][] = array(
    15,
    'Import Account, Contacts, and lead to Active Campaign',
    'custom/modules/ProspectLists/logic_hook/ManageUnlinks.php',
    'ManageUnlinks',
    'start_process'
);

?>
