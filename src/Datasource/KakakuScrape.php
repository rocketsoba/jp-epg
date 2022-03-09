<?php

namespace Rocketsoba\EPG\Datasource;

use DateTime;
use Exception;
use Rocketsoba\Curl\MyCurl;
use Rocketsoba\Curl\MyCurlBuilder;
use Rocketsoba\DomParserWrapper\DomParserAdapter;

class KakakuScrape
{
    private $url = "https://kakaku.com/tv/";
    private $channel_list = [
        4 => "日本テレビ",
        6 => "TBS",
        8 => "フジテレビ",
        10 => "テレビ朝日",
        12 => "テレビ東京",
    ];
    private $channel_code = [
        4 => "NTV",
        6 => "TBS",
        8 => "CX",
        10 => "EX",
        12 => "TX",
    ];
    private $date_limit = "2008-08-20";

    private $programs = [];
    private $date = "";

    public function __construct($date = "")
    {
        $this->date = $date;
    }

    public function scrape($date = "", $channels = [4, 6, 8, 10, 12])
    {
        if ($date !== "") {
            $this->date = $date;
        }

        $unixtime = $this->validateDate($this->date);
        $formated_date = date("Ymd", $unixtime);

        foreach ($channels as $val1) {
            if (!array_key_exists($val1, $this->channel_list)) {
                throw new Exception("invalid channel");
            }

            $constructed_url = $this->url . "channel=" . $val1 . "/date=" . $formated_date . "/";
            $curl_object = $this->request("GET", $constructed_url);
            if ($curl_object->getHttpCode() !== 200) {
                throw new Exception("Fetch sequence is failed");
            }

            $dom = new DomParserAdapter($curl_object->getResult());
            $dom->findOne("div#programlist")
                ->findOne("table")
                ->findMany("tr");

            $channel_programs = [];
            foreach ($dom as $val2) {
                $table_dom = clone $val2;
                $val2->findMany("a");

                $val2->enableDeepCopy();
                $a_elements = iterator_to_array($val2);
                $val2->disableDeepCopy();
                if (count($a_elements) == 2) {
                    $title = trim($a_elements[0]->plaintext);
                    $program_date_str = trim($a_elements[1]->plaintext);
                } elseif (count($a_elements) == 1) {
                    $title = "";
                    $program_date_str = trim($a_elements[0]->plaintext);
                } else {
                    throw new Exception("program title and date are not found");
                }

                if (!preg_match('/(\d{4})年(\d{1,2})月(\d{1,2})日[^\d]+(\d{2}):(\d{2})(.+)(\d{2}):(\d{2})$/', $program_date_str, $matches)) {
                    throw new Exception("date is not found");
                }

                $start_date = $matches[1] . "-" . $matches[2] . "-" . $matches[3] . " " . $matches[4] . ":" . $matches[5] . ":00";
                if (preg_match('/(\d{4})年(\d{1,2})月(\d{1,2})日/', $matches[6], $matches2)) {
                    $end_date = $matches2[1] . "-" . $matches2[2] . "-" . $matches2[3] . " " . $matches[7] . ":" . $matches[8] . ":00";
                } else {
                    $end_date = $matches[1] . "-" . $matches[2] . "-" . $matches[3] . " " . $matches[7] . ":" . $matches[8] . ":00";
                }

                $channel_programs[] = [
                    "title" => $title,
                    "start_date" => $start_date,
                    "end_date" => $end_date,
                ];
            }

            $this->programs[$this->channel_code[$val1]] = $channel_programs;
        }

        return $this;
    }

    public function validateDate($date = "")
    {
        if (date_default_timezone_get() === "UTC") {
            throw new Exception("please set timezone");
        }
        if (
            !($unixtime = strtotime($date)) ||
            $unixtime > time() ||
            $unixtime < strtotime($this->date_limit)
        ) {
            throw new Exception("invalid date");
        }

        return $unixtime;
    }

    public function request($method, $uri, $options = [])
    {
        $curl_object = new MyCurlBuilder($uri);

        if (isset($options["headers"])) {
            $curl_object = $curl_object->setAddtionalHeaders($options["headers"]);
        }
        if (isset($options["rest_post_data"])) {
            $curl_object = $curl_object->setPlainPostData($options["rest_post_data"]);
        }
        if (isset($options["array_post_data"])) {
            $curl_object = $curl_object->setPostData($options["array_post_data"]);
        }

        $curl_object = $curl_object->build();
        return $curl_object;
    }

    public function getPrograms()
    {
        return $this->programs;
    }
}
