<?php

require_once('include/utils/sugar_file_utils.php');

class PluginLogger
{
    protected static $tableName = 'tl_activecampaignlogs';
    protected static $tableChecked = false;

    protected static function ensureTableExists()
    {
        if (self::$tableChecked) {
            self::$tableChecked = true;
            return;
        }
        $db = $GLOBALS['db'];
        $table = self::$tableName;
        $result = $db->query("SHOW TABLES LIKE '{$table}'");
        if ($db->getRowCount($result) == 0) {
            $sql = "
            CREATE TABLE {$table} (
                id CHAR(36) NOT NULL PRIMARY KEY,
                name VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
                description LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
                date_entered DATETIME,
                deleted TINYINT(1) DEFAULT 0
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";
            $db->query($sql);
        }

        self::$tableChecked = true;
        $_SESSION['ac_plugin_logger_table_exists'] = true;
    }

    public static function log($message)
    {
        try {
            self::ensureTableExists();
            if (is_array($message) || is_object($message)) {
                $message = print_r($message, true);
            }
            $message = mb_convert_encoding($message, 'UTF-8', 'UTF-8');
            $message = preg_replace('/[\x{10000}-\x{10FFFF}]/u', '', $message);
            $id = \Sugarcrm\Sugarcrm\Util\Uuid::uuid1();
            $now = \TimeDate::getInstance()->nowDb();
            $message = mb_convert_encoding($message, 'UTF-8', 'UTF-8'); // normalize
            $message = preg_replace('/[\x{10000}-\x{10FFFF}]/u', '', $message); // strip emojis/surrogates
            $name = mb_substr($message, 0, 100, 'UTF-8');
            $GLOBALS['db']->query(
                "INSERT INTO " . self::$tableName . " (id, name, description, date_entered, deleted)
             VALUES (
                " . self::quoted($id) . ",
                " . self::quoted($name) . ",
                " . self::quoted($message) . ",
                " . self::quoted($now) . ",
                0
             )"
            );
        } catch (Exception $e) {
            $GLOBALS['log']->fatal('PluginLogger DB Logging Error: ' . $e->getMessage());
        }
    }

    public static function getLogs($limit = 500)
    {
        self::ensureTableExists();
        $db = $GLOBALS['db'];
        $table = self::$tableName;
        $query = "SELECT date_entered, name, description FROM {$table} WHERE deleted = 0 ORDER BY date_entered ASC, id ASC LIMIT {$limit}";
        $result = $db->query($query);
        $logs = [];
        while ($row = $db->fetchByAssoc($result)) {
            if (!empty($row['description'])) {
                $entry = '[' . $row['date_entered'] . '] ' . $row['description'];
                $logs[] = $entry;
            }
        }
        return $logs;
    }

    public static function clearLogs()
    {
        self::ensureTableExists();

        $db = $GLOBALS['db'];
        $table = self::$tableName;

        $db->query("DELETE FROM {$table} WHERE deleted = 0");
    }

    protected static function quoted($value)
    {
        return "'" . $GLOBALS['db']->quote($value) . "'";
    }
}

