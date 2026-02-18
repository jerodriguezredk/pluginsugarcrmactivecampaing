<?php

use Sugarcrm\Sugarcrm\Security\HttpClient\ExternalResourceClient;
use Sugarcrm\Sugarcrm\Security\HttpClient\RequestException;
use Sugarcrm\Sugarcrm\Util\Uuid;

if (!class_exists('SugarActiveOutfittersLicense')) {
    class SugarActiveOutfittersLicense
    {
        public static function isValid($thisModule = null, $user_id = null, $force_validation = false)
        {
            $outfitters_config = [];
            if (empty($thisModule)) {
                global $currentModule;
                $thisModule = $currentModule;
                if (empty($thisModule)) {
                    return true;
                }
            }
            require('modules/' . $thisModule . '/license/config.php');
            if (!empty($user_id) && isset($outfitters_config) && is_array($outfitters_config) && $outfitters_config['validate_users'] == true) {
                global $db;
                $result = $db->query("SELECT id FROM so_users WHERE shortname = '" . $db->quote($outfitters_config['shortname']) . "' and user_id = '" . $db->quote($user_id) . "'", false);
                $row = $db->fetchByAssoc($result);
                if (empty($row)) {
                    return 'The user does not have access to this add-on.';
                }
            }
            require_once('modules/Administration/Administration.php');
            $administration = new Administration();
            $administration->retrieveSettings();
            $last_validation = $administration->settings['SugarOutfitters_' . $outfitters_config['shortname']];
            $trimmed_last = trim($last_validation);
            if ($force_validation == false && !empty($trimmed_last)) {
                $last_validation = base64_decode($last_validation);
                $last_validation = unserialize($last_validation);
                $frequency = $outfitters_config['validation_frequency'];
                $elapsed = (7 * 24 * 60 * 60); //default to weekly
                if ($frequency == 'hourly') {
                    $elapsed = (60 * 60);
                } else if ($frequency == 'daily') {
                    $elapsed = (24 * 60 * 60);
                }

                if (($last_validation['last_ran'] + $elapsed) >= time()) {
                    if ($last_validation['last_result']['success'] === false) {
                        return $last_validation['last_result']['result'];
                    } else {
                        $validation_result = !empty($last_validation['last_result']['result']['validated']);

                        if ($outfitters_config['validate_users'] == true) {
                            if (!empty($last_validation['last_result']['result']) && (empty($last_validation['last_result']['result']['validated_users']) || $last_validation['last_result']['result']['validated_users'] !== true)) {
                                $validation_result = false;
                            }
                        }

                        if ($validation_result === false) {
                            return 'Insuffient number of user licenses. Please add additional user licenses and try again.';
                        } else {
                            return true;
                        }
                    }
                }
            }
            $validated = SugarActiveOutfittersLicense::doValidate($thisModule);
            $store = array(
                'last_ran' => time(),
                'last_result' => $validated,
            );
            $serialized = base64_encode(serialize($store));
            $administration->saveSetting('SugarOutfitters', $outfitters_config['shortname'], $serialized);
            if ($validated['success'] === false) {
                return $validated['result'];
            } else {
                $validation_result = !empty($validated['result']['validated']);

                if ($outfitters_config['validate_users'] == true) {
                    if (!empty($validated['result']) && (empty($validated['result']['validated_users']) || $validated['result']['validated_users'] !== true)) {
                        $validation_result = false;
                    }
                }

                if ($validation_result === false) {
                    return 'Insuffient number of user licenses. Please add additional user licenses and try again.';
                } else {
                    return true;
                }
            }
        }

        public static function validate()
        {
            $outfitters_config = [];
            if (empty($_REQUEST['key'])) {
                header('HTTP/1.1 400 Bad Request');
                $response = "Key is required.";
                echo JSON::encode($response);
                exit;
            }
            global $currentModule;
            require('modules/' . $currentModule . '/license/config.php');
            $validated = SugarActiveOutfittersLicense::doValidate($currentModule, trim($_REQUEST['key']));
            $store = array(
                'last_ran' => time(),
                'last_result' => $validated,
            );
            require_once('modules/Administration/Administration.php');
            $administration = new Administration();
            $serialized = base64_encode(serialize($store));
            $administration->saveSetting('SugarOutfitters', $outfitters_config['shortname'], $serialized);
            if ($validated['success'] === false) {
                header('HTTP/1.1 400 Bad Request');
            } else {
                global $currentModule;
                require('modules/' . $currentModule . '/license/config.php');
                require('modules/Configurator/Configurator.php');
                $cfg = new Configurator();
                $cfg->config['outfitters_licenses'][$outfitters_config['shortname']] = trim($_REQUEST['key']);
                $cfg->handleOverride();
            }
            echo JSON::encode($validated['result']);
            exit;
        }

        public static function doValidate($thisModule, $key = null)
        {
            require('modules/' . $thisModule . '/license/config.php');
            $data = array();
            if (!empty($key)) {
                $data['key'] = $key;
            }
            $response = SugarOutfitters_ActiveCampaignAPI::call($thisModule, 'key/validate', $data, 'get');
            if (empty($response['success']) || $response['success'] !== true) {
                $GLOBALS['log']->fatal('SugarActiveOutfittersLicense::doValidate() failed (' . basename(__FILE__) . ":" . __LINE__ . '): ' . print_r($response, true));
            }
            return $response;
        }

        public static function change()
        {
            if (!isset($key) || empty($key)) {
                $key = null;
            }
            $outfitters_config = [];
            if (empty(trim($_REQUEST['key']))) {
                header('HTTP/1.1 400 Bad Request');
                $response = "Key is required.";
                echo JSON::encode($response);
                exit;
            }
            if (empty($_REQUEST['user_count'])) {
                header('HTTP/1.1 400 Bad Request');
                $response = "User count is required.";
                echo JSON::encode($response);
                exit;
            }
            global $currentModule;

            //load license validation config
            $outfitters_config = [];
            require('modules/' . $currentModule . '/license/config.php');
            $data = array(
                'key' => trim($_REQUEST['key'] ?? $key),
                'user_count' => $_REQUEST['user_count'],
            );
            $response = SugarOutfitters_ActiveCampaignAPI::call($currentModule, 'key/change', $data);
            if (empty($response['success']) || $response['success'] !== true) {
                header('HTTP/1.1 400 Bad Request');
                $GLOBALS['log']->fatal('SugarActiveOutfittersLicense::change() failed (' . basename(__FILE__) . ":" . __LINE__ . '): ' . print_r($response, true));
            } else {
                require_once('modules/Administration/Administration.php');
                global $sugar_config, $sugar_version;
                $sugar_config['outfitters_licenses'][$outfitters_config['shortname']] = trim($_REQUEST['key'] ?? $key);
                rebuildConfigFile($sugar_config, $sugar_version);
            }
            echo JSON::encode($response['result']);
            exit;
        }

        public static function add()
        {
            if (empty($_REQUEST['licensed_users']) || count($_REQUEST['licensed_users']) == 0) {
                header('HTTP/1.1 400 Bad Request');
                $response = "No additional licenses were set to be added.";
                echo JSON::encode($response);
                exit;
            }
            global $currentModule;
            $outfitters_config = [];
            require('modules/' . $currentModule . '/license/config.php');
            $response = SugarActiveOutfittersLicense::doValidate($currentModule);
            if (empty($response['success']) || $response['success'] !== true || empty($response['result']['validated'])) {
                header('HTTP/1.1 400 Bad Request');
                $response = "The license key could not validate. Please check the key and re-validate.";
                echo JSON::encode($response);
                exit;
            }
            if ($outfitters_config['validate_users'] == true) {
                if (!empty($response['result']) && (empty($response['result']['validated_users']) || $response['result']['validated_users'] !== true)) {
                    header('HTTP/1.1 400 Bad Request');
                    $response = "Insuffient number of user licenses. Please add additional user licenses and try again.";
                    echo JSON::encode($response);
                    exit;
                }
            }
            $fieldDefs = array(
                'id' => array(
                    'name' => 'id',
                    'vname' => 'LBL_ID',
                    'type' => 'id',
                    'required' => true,
                    'reportable' => true,
                ),
                'deleted' => array(
                    'name' => 'deleted',
                    'vname' => 'LBL_DELETED',
                    'type' => 'bool',
                    'default' => '0',
                    'reportable' => false,
                    'comment' => 'Record deletion indicator',
                ),
                'shortname' => array(
                    'name' => 'shortname',
                    'vname' => 'LBL_SHORTNAME',
                    'type' => 'varchar',
                    'len' => 255,
                ),
                'user_id' => array(
                    'name' => 'user_id',
                    'rname' => 'user_name',
                    'module' => 'Users',
                    'id_name' => 'user_id',
                    'vname' => 'LBL_USER_ID',
                    'type' => 'relate',
                    'isnull' => 'false',
                    'dbType' => 'id',
                    'reportable' => true,
                    'massupdate' => false,
                ),
            );
            global $db;
            $sql = "DELETE FROM so_users WHERE shortname = '" . $db->quote($outfitters_config['shortname']) . "'";
            $db->query($sql, true, 'Unable to reset licensed users for ' . $outfitters_config['shortname']);
            foreach ($_REQUEST['licensed_users'] as $licensed_user) {
                $data = array(
                    'id' => Uuid::uuid1(),
                    'shortname' => $outfitters_config['shortname'],
                    'user_id' => $licensed_user,
                    'deleted' => 0,
                );
                $db->insertParams('so_users', $fieldDefs, $data);
            }
            $response = array(
                'success' => true,
            );
            echo JSON::encode($response);
            exit;
        }
    }
}

if (!class_exists('SugarOutfitters_ActiveCampaignAPI')) {
    class SugarOutfitters_ActiveCampaignAPI
    {
        public static function get_default_payload($module, $custom_data = array())
        {
            global $sugar_config, $sugar_flavor, $db;
            $not_set_value = 'not set';
            $data = array();
            $outfitters_config = [];
            require('modules/' . $module . '/license/config.php');
            if (empty($custom_data['key'])) {
                $data['key'] = empty($sugar_config['outfitters_licenses'][$outfitters_config['shortname']]) ? false : $sugar_config['outfitters_licenses'][$outfitters_config['shortname']];
            } else {
                $data['key'] = $custom_data['key'];
            }
            if (empty($custom_data['public_key'])) {
                $data['public_key'] = empty($outfitters_config['public_key']) ? $not_set_value : $outfitters_config['public_key'];
            } else {
                $data['public_key'] = $custom_data['public_key'];
            }
            if (!empty($custom_data['user_count'])) {
                $data['user_count'] = $custom_data['user_count'];
            } else if (isset($outfitters_config['manage_licensed_users']) && $outfitters_config['manage_licensed_users'] == true) {
                $licensed_users = 0;
                $sql = "SELECT count(*) as the_count FROM so_users WHERE shortname = '" . $db->quote($outfitters_config['shortname']) . "'";
                $result = $db->query($sql, false, 'Unable to reset licensed users for ' . $outfitters_config['shortname']);
                $row = $db->fetchByAssoc($result);
                if (!empty($row)) {
                    $licensed_users = $row['the_count'];
                }
                $data['user_count'] = $licensed_users;
            } else {
                $active_users = get_user_array(false, 'Active', '', false, '', ' AND portal_only=0 AND is_group=0');
                $data['user_count'] = empty($active_users) ? 0 : count($active_users);
            }
            $active_group_users = get_user_array(false, 'Active', '', false, '', ' AND portal_only=0 AND is_group=1');
            $data['group_user_count'] = empty($active_group_users) ? 0 : count($active_group_users);
            $active_admin_users = get_user_array(false, 'Active', '', false, '', ' AND portal_only=0 AND is_group=0 AND is_admin=1');
            $data['admin_user_count'] = empty($active_admin_users) ? 0 : count($active_admin_users);
            $data['sugar_edition'] = empty($sugar_flavor) ? $not_set_value : $sugar_flavor;
            $data['db_type'] = empty($sugar_config['dbconfig']['db_type']) ? $not_set_value : $sugar_config['dbconfig']['db_type'];
            $data['developerMode'] = (!empty($sugar_config['developerMode']) && $sugar_config['developerMode'] === true ? 'true' : 'false');
            $data['host_name'] = empty($sugar_config['host_name']) ? $not_set_value : $sugar_config['host_name'];
            $data['package_scan'] = (!empty($sugar_config['moduleInstaller']) && !empty($sugar_config['moduleInstaller']['packageScan']) && $sugar_config['moduleInstaller']['packageScan'] === true ? 'true' : 'false');
            $data['sugar_version'] = empty($sugar_config['sugar_version']) ? $not_set_value : $sugar_config['sugar_version'];
            $data['site_url'] = empty($sugar_config['site_url']) ? $not_set_value : $sugar_config['site_url'];
            $data['addon_version'] = $not_set_value;
            if (!empty($outfitters_config['name'])) {
                $result = $db->query("select version from upgrade_history where id_name = '" . $db->quote($outfitters_config['name']) . "' order by date_entered DESC");
                $row = $db->fetchByAssoc($result);
                if (!empty($row)) {
                    $data['addon_version'] = empty($row['version']) ? $not_set_value : $row['version'];
                } else {
                    $data['addon_version'] = "No Versions Found";
                }
            } else {
                $data['addon_version'] = "Error: 'name' not set in outfitters_config.";
            }
            return $data;
        }

        public static function tag($action, $payload)
        {
            return true;
        }

        public static function call($module, $path, $custom_data = array(), $method = 'post')
        {
            $outfitters_config = [];
            if (empty($module)) {
                return array(
                    'success' => false,
                    'result' => 'Module is not defined.'
                );
            }
            if (empty($path)) {
                return array(
                    'success' => false,
                    'result' => 'API Call Path is not defined.'
                );
            }
            if (!is_array($custom_data)) {
                return array(
                    'success' => false,
                    'result' => 'Data for API Call must be an array.'
                );
            }
            require('modules/' . $module . '/license/config.php');
            $url = $outfitters_config['api_url'] . '/' . $path;
            $data = static::get_default_payload($module, $custom_data);
            if (empty($data['key'])) {
                return array(
                    'success' => false,
                    'result' => 'Key could not be found locally. Please go to the license configuration tool and enter your key.'
                );
            }
            $result = array();
            $httpCode = 0;
            try {
                if ($method === 'post') {
                    $postObject = $data;
                    if (is_array($data)) {
                        $postObject = JSON::encode($data);
                    }
                    $response = (new ExternalResourceClient(3600, 10))->post($url, $postObject);
                } else {
                    $url .= '?' . http_build_query($data);
                    $response = (new ExternalResourceClient(3600, 10))->get($url);
                }
                $httpCode = $response->getStatusCode();
                if ($httpCode >= 400) {
                    $GLOBALS['log']->fatal('SugarOutfitters_ActiveCampaignAPI::call()  (' . basename(__FILE__) . ":" . __LINE__  . '): Unable to validate license. Please configure the firewall to allow requests to ' . $outfitters_config['api_url'] . '/key/validate and make sure that SSL certs are up to date on the server.');
                    $GLOBALS['log']->fatal("Request failed with status: " . $httpCode);
                    $result = $response->getBody()->getContents();
                } else {
                    $bodyContents = $response->getBody()->getContents();
                    $result = JSON::decode($bodyContents, false, true);
                }
            } catch (RequestException $e) {
                $GLOBALS['log']->fatal('ActiveCampaign Exception: SugarOutfitters_ActiveCampaignAPI::call() (' . basename(__FILE__) . ":" . __LINE__ . '): Unable to validate license. Please configure the firewall to allow requests to ' . $outfitters_config['api_url'] . '/key/validate and make sure that SSL certs are up to date on the server.');
                $GLOBALS['log']->fatal('Code: ' . $e->getCode());
                $GLOBALS['log']->fatal('Error: ' . $e->getMessage());
            }
            if ($httpCode == 0) {
                $GLOBALS['log']->fatal('SugarOutfitters_ActiveCampaignAPI::call() (' . basename(__FILE__) . ":" . __LINE__ . '): Unable to validate license. Please configure the firewall to allow requests to ' . $outfitters_config['api_url'] . '/key/validate and make sure that SSL certs are up to date on the server.');
                $message = 'SugarOutfitters_ActiveCampaignAPI::call(): Unable to validate the license key. Please configure the firewall to allow requests to ' . $outfitters_config['api_url'] . '/key/validate and make sure that SSL certs are up to date on the server.';
                if(is_string($result)){
                    $message = $result;
                }
                return array(
                    'success' => false,
                    'result' => $message
                );
            } else if ($httpCode != 200) {
                $GLOBALS['log']->fatal('SugarOutfitters_ActiveCampaignAPI::call() (' . basename(__FILE__) . ":" . __LINE__ .'): HTTP Request failed with status ' . $httpCode);
                $message = 'SugarOutfitters_ActiveCampaignAPI::call(): Unable to validate the license key. Please configure the firewall to allow requests to ' . $outfitters_config['api_url'] . '/key/validate and make sure that SSL certs are up to date on the server.';
                if(is_string($result)){
                    $message = $result;
                }
                return array(
                    'success' => false,
                    'result' => $message
                );
            } else {
                return array(
                    'success' => true,
                    'result' => $result
                );
            }
        }
    }
}