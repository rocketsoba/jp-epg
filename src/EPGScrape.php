<?php

namespace Lib;

use KubAT\PhpSimple\HtmlDomParser;
use Lib\MyCurl;

class EPGScrape
{
    private $program_elements = array();
    private $program_date = null;
    private $program_datestring = null;
    private $target_url = null;

    private $target_url_prefix = 'http://timetable.yanbe.net/html/13/';
    private $target_url_sufix = '_1.html?13';
    private $tmp_path = __DIR__ . "/../tmp/Tvtimetable/";

    public function __construct($target_string = null)
    {
        if ($this->checkTargetArg($target_string)) {
            $this->scrape();
        }
    }
    
    private function loadHTML()
    {
        $cache_path = $this->tmp_path . $this->program_datestring . ".html";
        if (file_exists($cache_path)) {
            $html_result = file_get_contents($cache_path);
        } else {
            $curl1 = new MyCurl($this->target_url);
            $html_result = $curl1->getResult();
            if (!file_exists($this->tmp_path)) {
                mkdir($this->tmp_path, 0775, true);
            }
            file_put_contents($cache_path, $html_result);
        }
        return $html_result;
    }

    private function checkTargetArg($target_string = null)
    {
        if (!is_null($target_string)) {
            $this->target_string = $target_string;
        }
        
        if (is_string($this->target_string)) {
            if (preg_match('/^http:\/\/timetable\.yanbe\.net\/html\//', $target_string)) {
                $this->target_url = $target_string;
                $this->setProgramDate();
                return true;
            }
            if (preg_match('/^([0-9]{4})-([0-9]{2})-([0-9]{2})/', $target_string, $matches)) {
                $this->program_time = $target_string;
                $this->setTargetUrl($matches);
                return true;
            }
        }
        return false;
    }

    private function setProgramDate()
    {
        if (is_null($this->program_date) && !is_null($this->target_url)) {
            $date_from_url = str_replace($this->target_url_prefix, '', $this->target_url);

            if (preg_match('/^([0-9]{4})\/([0-9]{2})\/([0-9]{2})/', $date_from_url, $matches)) {
                $this->program_date = $matches[1] . "-" . $matches[2] . "-" . $matches[3];
                $this->program_datestring = $matches[1] . $matches[2] . $matches[3];
            }
        }
    }
    
    private function setTargetUrl($ymd = array())
    {
        if (is_null($this->target_url)) {
            $this->target_url =
                $this->target_url_prefix . $ymd[1] . "/" . $ymd[2] . "/" . $ymd[3] . $this->target_url_sufix;
            $this->program_datestring = $ymd[1] . $ymd[2] . $ymd[3];
        }
    }
    
    public function scrape($target_url = null)
    {
        $rowspan_count = array();
        $program_name_list = array();

        $html_result = $this->loadHTML();
        $dom = HtmlDomParser::str_get_html($html_result);

        $program_table = $dom->find("table.d1_table", 0);
        $program_tr = $program_table->find("tr");
        
        $first_row = array_shift($program_tr);
        foreach ($first_row->find("td") as $ind1 => $ele1) {
            $rowspan_count[$ind1] = 0;
            $program_name = trim($ele1->plaintext);
            if ($program_name !== "") {
                $program_name_list[$ind1] = $program_name;
            }
        }
        foreach ($program_tr as $ind1 => $ele1) {
            $program_td = $ele1->find("td");
            $target_column = array();
            foreach ($rowspan_count as $ind2 => $ele2) {
                if ($ele2 == 0) {
                    $target_column[] = $ind2;
                }
            }
            if (count($program_td) > 0) {
                foreach ($program_td as $ind2 => $ele2) {
                    $program_time = $ele2->find("span.program_time", 0);
                    $program_title = $ele2->find("span.program_title", 0);
                    /**
                     * $program_info = $ele2->find("span.program_contents", 0);
                     */
                    if (isset($program_time) && isset($program_title)) {
                        $this->program_elements[$program_name_list[$target_column[$ind2]]][] = [
                            "program_time" => trim($program_time->plaintext),
                            "title" => trim($program_title->plaintext),
                            /**
                             * "infomation" => html_entity_decode(
                             *     trim($program_info->plaintext),
                             *     ENT_HTML5 | ENT_QUOTES,
                             *     'UTF-8'
                             * ),
                             */
                        ];
                    }
                    $rowspan = (int) $ele2->getAttribute("rowspan");
                    
                    /* emptyは関数の返り値を直接渡せる、issetはできない */
                    if (!empty($rowspan)) {
                        $rowspan_count[$target_column[$ind2]] += $rowspan;
                    }
                }
            }
            foreach ($rowspan_count as $ind2 => $ele2) {
                if ($ele2 > 0) {
                    $rowspan_count[$ind2]--;
                }
            }
        }
    }
    public function getProgramElements()
    {
        if (!is_null($this->program_elements)) {
            return $this->program_elements;
        } else {
            return false;
        }
    }
    public function getProgramDate()
    {
        if (!is_null($this->program_date)) {
            return $this->program_date;
        } else {
            return false;
        }
    }
}
