<?php

namespace Model;

use Sunra\PhpSimple\HtmlDomParser;
use Lib\Curl\MyCurl;
use Model\LoadModel;

class TvtimetableModel
{
    private $table_exists = false;
    private $target_station_list = null;
    private $tv_station_list = array();
    private $target_station_list_path =  __DIR__ . "/../../config/station_list.json";

    public function __construct()
    {
        LoadModel::initialize();
        $this->checkTableExists();
        $this->loadStationList();
    }

    private function loadStationList()
    {
        $this->target_station_list = json_decode(file_get_contents($this->target_station_list_path), true);
    }
    
    private function checkTableExists()
    {
        $table_status = \ORM::for_table("")->raw_query("SHOW TABLES LIKE 'program_elements'")->find_array();
        if (!empty($table_status)) {
            $this->table_exists = true;
        }
    }

    public function insertWithDate($program_elements = null, $program_date = null)
    {
        if (is_null($program_elements) || is_null($program_date) || !$this->table_exists) {
            return -1;
        }
        
        foreach ($program_elements as $ind1 => $ele1) {
            $station_insert_array = ["name" => $ind1];
            if (isset($this->target_station_list[$ind1])) {
                $station_insert_array["active_flag"] = 1;
            } else {
                $station_insert_array["active_flag"] = 0;
            }
            $this->insertStationList($station_insert_array);
            $station_num = $this->selectStationByName(["name" => $ind1]);
            
            foreach ($ele1 as $ind2 => $ele2) {
                $insert_row = $ele2;
                $insert_row["station_id"] = $station_num;
                $insert_row["program_date"] = $program_date;
                $insert_row["program_time"] = date("H:i:s", strtotime($insert_row["program_time"]));
                $this->insertProgramElements($insert_row);
            }
        }
    }
    private function selectStationByName($station_name = null)
    {
        $table_object = \ORM::for_table("station_list");
        $result = $table_object
            ->where($station_name)
            ->find_array();
        if (!empty($result)) {
            return $result[0]["id"];
        } else {
            return false;
        }
    }
    
    public function insertProgramElements($insert_array = null)
    {
        $table_object = \ORM::for_table("program_elements");
        
        $row_exist_check = $table_object
            ->where($insert_array)
            ->find_one();
        
        if (!$row_exist_check) {
            $insert_array["create_time"] = null;
            $table_status = $table_object
                ->create()
                ->set($insert_array)
                ->save();
            return $table_status;
        }
    }
    
    public function insertStationList($insert_array = null)
    {
        $table_object = \ORM::for_table("station_list");
        
        $row_exist_check = $table_object
            ->where($insert_array)
            ->find_one();
        
        if (!$row_exist_check) {
            $insert_array["create_time"] = null;
            $table_status = $table_object
                ->create()
                ->set($insert_array)
                ->save();
            return $table_status;
        }
    }
}
