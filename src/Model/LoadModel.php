<?php
namespace Model;

date_default_timezone_set("Asia/Tokyo");
ini_set("arg_separator.output", "&");

require_once __DIR__ . "/../../vendor/autoload.php";

class LoadModel
{
    private static $mysql_config_path =      __DIR__ . "/../../config/mysql_config.json";
    private static $mysql_config_orig_path = __DIR__ . "/../../config/mysql_config_orig.json";
    
    static public function initialize()
    {
        if (!file_exists(self::$mysql_config_path)) {
            $config_array = json_decode(file_get_contents(self::$mysql_config_orig_path), true);
            $config_array["setting1"]["password"] = md5(openssl_random_pseudo_bytes(32));
            file_put_contents(self::$mysql_config_path,
                              json_encode($config_array, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        }
        else {
            /* jsonはシングルクォートを使ってはいけない */
            $config_array = json_decode(file_get_contents(self::$mysql_config_path), true);
        }
        \ORM::configure($config_array["setting1"]);
    }
}


