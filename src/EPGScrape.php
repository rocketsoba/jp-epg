<?php

namespace Rocketsoba\EPG;

use Rocketsoba\Curl\MyCurl;
use Rocketsoba\Curl\MyCurlBuilder;
use Rocketsoba\DomParserWrapper\DomParserAdapter;
use Rocketsoba\EPG\Datasource\KakakuScrape;
use Exception;

class EPGScrape
{
    private $programs = [];
    private $date = "";
    private $channels = [];

    public function __construct($date = "")
    {
        $this->date = $date;
    }

    public function scrape($date = "")
    {
        if ($date === "") {
            if ($this->date === "") {
                $this->date = date("Y-m-d", time());
            }
        } else {
            $this->date = $date;
        }

        if (($unixtime = strtotime($this->date)) && $unixtime <= time()) {
            $scraper = new KakakuScrape($this->date);
        } else {
            throw new Exception("Error");
        }

        if (!empty($this->channels)) {
            $scraper->setChannels($this->channels);
        }

        $scraper->scrape();
        $this->programs = $scraper->getPrograms();

        return $this;
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

    public function setChannels(array $channels)
    {
        $this->channels = $channels;
    }

    public function getPrograms()
    {
        return $this->programs;
    }
}
