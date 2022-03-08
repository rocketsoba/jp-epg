<?php

date_default_timezone_set("Asia/Tokyo");
ini_set("arg_separator.output", "&");

require_once __DIR__ . "/../vendor/autoload.php";

use Lib\EPGScrape;

$lib1 = new EPGScrape("http://timetable.yanbe.net/html/13/2017/02/26_1.html?13");
var_dump($lib1->getProgramDate());
var_dump($lib1->getProgramElements());
