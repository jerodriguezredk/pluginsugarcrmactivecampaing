<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

require_once('include/MVC/View/SugarView.php');

require_once('modules/TL_ActiveCampaigns/ActiveCampaign_class.php');

require_once('modules/TL_ActiveCampaigns/PluginLogger.php');

class ViewSetting extends SugarView
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
            translate('ActiveCampaign Configuration', 'Administration'),
        );
    }


    public function display()
    {
        try {
            global $mod_strings, $app_strings, $currentModule;
            $prefix = 'TL_ActiveCampaigns';

            $list_arr = array();
            $display_error = null;

            if (isset($_REQUEST['msg']) && $_REQUEST['msg'] != '') {
                $this->ss->assign("msg", '<div class="moduleTitle" id="mymsg"><p>' . $_REQUEST['msg'] . '</p></div>');
            }
            $authenticated = false;
            $Administration = new Administration();
            $focus = $Administration->retrieveSettings();
            if (isset($focus->settings[$prefix . '_ac_authenticated']) && $focus->settings[$prefix . '_ac_authenticated'] == true) {
                $authenticated = true;
            }
            $api_url = $this->getVariableValue($focus, 'api_url');
            $api_key = $this->getVariableValue($focus, 'api_key');
            $account_sync = $this->getVariableValue($focus, 'account_sync');
            $account_existing_sync = $this->getVariableValue($focus, 'account_existing_sync');
            $account_new_sync = $this->getVariableValue($focus, 'account_new_sync');
            $contacts_sync = $this->getVariableValue($focus, 'contacts_sync');
            $contacts_existing_sync = $this->getVariableValue($focus, 'contacts_existing_sync');
            $contacts_new_sync = $this->getVariableValue($focus, 'contacts_new_sync');
            $leads_sync = $this->getVariableValue($focus, 'leads_sync');
            $leads_existing_sync = $this->getVariableValue($focus, 'leads_existing_sync');
            $leads_new_sync = $this->getVariableValue($focus, 'leads_new_sync');
            $contracts_sync = $this->getVariableValue($focus, 'contracts_sync');
            $contracts_existing_sync = $this->getVariableValue($focus, 'contracts_existing_sync');
            $contracts_new_sync = $this->getVariableValue($focus, 'contracts_new_sync');
            $target_list_mapped = $this->getVariableValue($focus, 'target_list_mapped', true);
            $ac_lists_mapped = $this->getVariableValue($focus, 'ac_lists_mapped', true);
            $existing_sync = $this->getVariableValue($focus, 'existing_sync', true);
            $new_sync = $this->getVariableValue($focus, 'new_sync', true);
            $lead_score_sync = $this->getVariableValue($focus, 'lead_score_sync');

            if ($api_url && $api_key) {
                $limit = "100";
                $offset = "0";
                $ac = new ActiveCampaign_class();
                $listResponse = $ac->get_all_lists($limit, $offset);
                if (isset($listResponse['status']) && $listResponse['status'] == true && isset($listResponse['data'])) {
                    $list_arr = $listResponse['data'];
                } else if (isset($listResponse['data'])) {
                    $display_error = $listResponse['data'];
                }
            }
            /**Get CRM Target lists */
            $query = new SugarQuery();
            $query->select(array('id', 'name'));
            $query->from(BeanFactory::getBean('ProspectLists'));
            $query->where()->equals('deleted', '0');
            $query->orderBy('date_entered');
            $tg_lists = $query->execute();

            if ($tg_lists && $list_arr) {
                if ($target_list_mapped) {
                    $html = $this->get_list_mapping_html($tg_lists, $list_arr, $target_list_mapped, $ac_lists_mapped, $existing_sync, $new_sync);
                } else {
                    $html = $this->get_initial_mapping_html($tg_lists, $list_arr);
                }
            } else {
                if (!$tg_lists) {
                    $lbl = $mod_strings["LBL_NO_TARGET_LISTS_IN_CRM"] ?? "No target list found in CRM.";
                    $html = '<tr><td colspan="5" class="tcenter" style="padding: 12px !important">' . $lbl . '</td></tr>';
                }
                if (!$list_arr) {
                    $lbl = $mod_strings["LBL_NO_TARGET_LISTS_IN_ACTIVECAMPAIGN"] ?? "No List found in Active Campaign.";
                    $html = '<tr><td colspan="5" class="tcenter" style="padding: 12px !important">' . $lbl . '</td></tr>';
                }
            }
            $this->ss->assign("MOD", $mod_strings);
            $this->ss->assign("APP", $app_strings);
            $this->ss->assign("RETURN_MODULE", $currentModule);
            $this->ss->assign("RETURN_ACTION", "config");
            $this->ss->assign("MODULE", $currentModule);
            $this->ss->assign("mapping_htmls", $html);
            $this->ss->assign("api_url", $api_url);
            $this->ss->assign("api_key", $api_key);
            $this->ss->assign("list_arr", $list_arr);
            $this->ss->assign("account_sync", $account_sync);
            $this->ss->assign("account_existing_sync", $account_existing_sync);
            $this->ss->assign("account_new_sync", $account_new_sync);
            $this->ss->assign("contacts_sync", $contacts_sync);
            $this->ss->assign("contacts_existing_sync", $contacts_existing_sync);
            $this->ss->assign("contacts_new_sync", $contacts_new_sync);
            $this->ss->assign("leads_sync", $leads_sync);
            $this->ss->assign("leads_existing_sync", $leads_existing_sync);
            $this->ss->assign("leads_new_sync", $leads_new_sync);
            $this->ss->assign("contracts_sync", $contracts_sync);
            $this->ss->assign("contracts_existing_sync", $contracts_existing_sync);
            $this->ss->assign("contracts_new_sync", $contracts_new_sync);
            $this->ss->assign("lead_score_sync", $lead_score_sync);
            $this->ss->assign("display_error", $display_error);
            $this->ss->assign("authenticated", $authenticated);
            $this->ss->assign("PRINT_URL", "index.php?" . $GLOBALS['request_string']);
            $this->ss->assign("JAVASCRIPT2", '<script src="modules/' . $currentModule . '/js/config.js"></script>');
            $this->ss->display("modules/$currentModule/tpls/setting.tpl");
        } catch (Exception $e) {
            $GLOBALS['log']->fatal('ActiveCampaign Exception: While loading ActiveCampaign config view. ' . basename(__FILE__) . ":" . __LINE__);
            $GLOBALS['log']->fatal($e->getMessage());
        }
    }

    private function getVariableValue($focus, $key, $is_array = false)
    {
        $prefix = 'TL_ActiveCampaigns';
        $result = '';
        if (isset($focus->settings[$prefix . '_' . $key]) && !empty($focus->settings[$prefix . '_' . $key])) {
            $result = $focus->settings[$prefix . '_' . $key];
        } else if ($is_array) {
            $result = [];
        }
        return $result;
    }

    private function get_list_mapping_html($tg_lists, $list_arr, $target_list_mapped, $ac_lists_mapped, $existing_sync, $new_sync)
    {
        $html = "";
        $minus = "";
        for ($i = 0; $i < count($target_list_mapped); $i++) {
            if ($i != 0) {
                $minus = "&nbsp&nbsp&nbsp
                <a href='#' class='remove_mapping' style='text-decoration:none; color:#F00;'>
                    <i class='plugin-icon plugin-times'></i> 
                </a>";
            }
            $html .= "<tr>
                        <td class='tcenter' style='padding: 12px !important'>
                            <select class='target_list_mapped' id='target_list_mapped' name='target_list_mapped[$i]' style='width: 100%; text-align: center;'>
                            " . $this->get_options($tg_lists, array($target_list_mapped[$i])) . "
                            </select>
                        </td>
                        <td class='tcenter' style='padding: 12px !important'>
                            <select id='ac_lists_mapped' name='ac_lists_mapped[$i][]' style='width: 100%; text-align: center;' multiple=''>
                            " . $this->get_ac_lists_options($list_arr, $ac_lists_mapped, $i) . "
                            </select>
                        </td>
                        <td class='tcenter' style='padding: 12px !important'>
                            " . $this->get_existing_new_checked($existing_sync, $i, "existing_sync") . "
                        </td>
                        <td class='tcenter' style='padding: 12px !important'>
                        " . $this->get_existing_new_checked($new_sync, $i, "new_sync") . "
                        </td>
                        <td class='tcenter' style='padding: 12px !important'>
                            <a href='#' class='add_more_mapping' style='margin-left: -0.5%;'>
                                <i class='plugin-icon plugin-plus'></i>
                            </a>
                            $minus        
                        </td>
                    </tr>";
        }
        return $html;
    }

    private function get_initial_mapping_html($tg_lists, $list_arr)
    {
        $tg_options = $this->get_options($tg_lists);
        $ac_lists_options = $this->get_options($list_arr, array(), true);
        $html = "<tr>
                    <td class='tcenter' style='padding: 12px !important'>
                        <select class='target_list_mapped' id='target_list_mapped' name='target_list_mapped[0]' style='width: 100%; text-align: center;'>
                        $tg_options
                        </select>
                    </td>
                    <td class='tcenter' style='padding: 12px !important'>
                        <select id='ac_lists_mapped' name='ac_lists_mapped[0][]' style='width: 100%; text-align: center;' multiple=''>
                        $ac_lists_options
                        </select>
                    </td>
                    <td class='tcenter' style='padding: 12px !important'>
                        <input type='checkbox' id='existing_sync' name='existing_sync[0]'>
                    </td>
                    <td class='tcenter' style='padding: 12px !important'>
                        <input type='checkbox' id='new_sync' name='new_sync[0]'>
                    </td>
                    <td class='tcenter' style='padding: 12px !important'>
                        <a href='#' class='add_more_mapping' row_count='0' style='margin-left: -0.5%;'>
                            <i class='plugin-icon plugin-plus'></i>
                        </a>
                    </td>
                </tr>";
        return $html;
    }

    private function get_options($lists, $matching_ids = array(), $without_empty_option = false)
    {
        if ($without_empty_option) {
            $options = "";
        } else {
            $options = "<option value=''>select</option>";
        }
        foreach ($lists as $list) {
            $id = $list['id'];
            $lable = $list['name'];

            if ($matching_ids && in_array($id, $matching_ids)) {
                $options .= "<option value='$id' selected> $lable</option>";
            } else {
                $options .= "<option value='$id'> $lable</option>";
            }
        }
        return $options;
    }

    private function get_ac_lists_options($list_arr, $ac_lists_mapped, $i)
    {
        $maped_arr = array();
        if (isset($ac_lists_mapped[$i])) {
            foreach ($ac_lists_mapped[$i] as $maped) {
                $maped_arr[] = $maped;
            }
        }
        if ($maped_arr) {
            return $this->get_options($list_arr, $maped_arr, true);
        } else {
            return $this->get_options($list_arr, array(), true);
        }
    }

    private function get_existing_new_checked($checked, $i, $name)
    {
        if (isset($checked[$i])) {
            return "<input type='checkbox' id='$name' name='$name" . "[$i]' checked>";
        } else {
            return "<input type='checkbox' id='$name' name='$name" . "[$i]'>";
        }
    }
}
