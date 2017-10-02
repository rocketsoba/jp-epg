<?php
date_default_timezone_set("Asia/Tokyo");
ini_set("arg_separator.output", "&");

require_once __DIR__ . "/../../../vendor/autoload.php";
use Sunra\PhpSimple\HtmlDomParser;
use Lib\Curl\MyCurl;

class TvtimetableScrape
{
    private $program_elements = array();
    private $program_date = null;
    
    public function scape($target_url = "http://timetable.yanbe.net/html/13/2017/02/01_1.html?13")
    {
        
        $program_rowspan_count_list = array();
        $program_name_list = array();

        if (is_null($this->program_date)) {
            $date_from_url = str_replace('http://timetable.yanbe.net/html/', '', $target_url);

            if (preg_match('/^[0-9]+\/([0-9]{4})\/([0-9]{2})\/([0-9]{2})/', $date_from_url, $matches)) {
                $this->program_date = $matches[1] . "-" . $matches[2] . "-" . $matches[3];
            }
        }
        
	/* $curl1 = new MyCurl($target_url);
         * $result1 = $curl1->getResult();*/
	/* file_put_contents(__DIR__ . "/tvtimetable.html", $result1);*/
	/* echo $result1[0];*/
	$result1 = file_get_contents(__DIR__ . "/../../../tvtimetable.html");
	$dom = HtmlDomParser::str_get_html($result1);
	/* echo $dom->plaintext;*/
	
	$program_table = $dom->find("table[cellspacing=0]", 0);
        $program_tr = $program_table->find("tr");
        $first_row = array_shift($program_tr);
        foreach ($first_row->find("td") as $ind1 => $ele1) {
            $program_rowspan_count_list[$ind1] = 0;
            $program_name = trim($ele1->plaintext);
            if ($program_name !== "") {
                $program_name_list[$ind1] = $program_name;
            }
        }
        foreach ($program_tr as $ind1 => $ele1) {
            $program_td = $ele1->find("td");
            $target_column = array();
	    foreach ($program_rowspan_count_list as $ind2 => $ele2) {
		if($ele2 == 0)
		    $target_column[] = $ind2;
	    }
            if (count($program_td) > 0) {
                foreach ($program_td as $ind2 => $ele2) {
                    $program_time = $ele2->find("span.program_time", 0);
                    $program_title = $ele2->find("a.lightwindow", 0);
                    $program_info = $ele2->find("span.program_contents", 0);
                    if (isset($program_time) && isset($program_title) && isset($program_info)) {
                        $this->program_elements[$program_name_list[$target_column[$ind2]]][] = [
                            "time" => trim($program_time->plaintext),
                            "title" => trim($program_title->plaintext),
                            "info" => html_entity_decode(trim($program_info->plaintext), ENT_HTML5 | ENT_QUOTES, 'UTF-8'),
                        ];
                    }
                    $rowspan = (int) $ele2->getAttribute("rowspan");
                    
                    /* emptyは関数の返り値を直接渡せる、issetはできない */
                    if (!empty($rowspan)) {
                        $program_rowspan_count_list[$target_column[$ind2]] += $rowspan;
                    }
                }
            }
            foreach ($program_rowspan_count_list as $ind2 => $ele2) {
                if ($ele2 > 0) {
                    $program_rowspan_count_list[$ind2]--;
                }
            }
        }
        /* var_dump($this->program_elements);*/
    }
    public function getProgramElements()
    {
        if (!is_null($this->program_elements)) {
            return $this->program_elements;
        }
        else {
            return false;
        }
    }
    public function getProgramDate()
    {
        if (!is_null($this->program_date)) {
            return $this->program_date;
        }
        else {
            return false;
        }
    }
}
$test1 = new TvtimetableScrape();
$test1->scape();
var_dump($test1->getProgramElements());
