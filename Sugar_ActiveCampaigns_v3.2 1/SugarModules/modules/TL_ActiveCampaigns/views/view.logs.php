<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

require_once('include/MVC/View/SugarView.php');

require_once('modules/TL_ActiveCampaigns/ActiveCampaign_class.php');
require_once('include/utils/sugar_file_utils.php');

class ViewLogs extends SugarView
{
    function preDisplay()
    {
        global $current_user;

        if (!is_admin($current_user)) {
            !is_admin_for_module($GLOBALS['current_user'], 'TL_ActiveCampaigns');
            sugar_die("Unauthorized access to administration.");
        }
    }

    protected function _getModuleTitleParams($browserTitle = false)
    {
        global $mod_strings;

        return array(
            "<a href='index.php?module=Administration&action=index'>" . translate('LBL_MODULE_NAME', 'Administration') . "</a>",
            translate('ActiveCampaign Logs', 'Administration'),
        );
    }


    public function display()
    {
        try {
            global $mod_strings, $app_strings, $currentModule;
            require_once('modules/TL_ActiveCampaigns/PluginLogger.php');
            $logs = PluginLogger::getLogs();
            $this->ss->assign('MOD', $mod_strings);
            $this->ss->assign('logs', $logs);
            $this->ss->display("modules/$currentModule/tpls/logs.tpl");
        } catch (Exception $e) {
            $GLOBALS['log']->fatal('Plugin Logs View Exception: ' . basename(__FILE__) . ":" . __LINE__);
            $GLOBALS['log']->fatal($e->getMessage());
        }
    }
}
