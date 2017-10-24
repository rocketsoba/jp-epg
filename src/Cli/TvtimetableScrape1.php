<?php

date_default_timezone_set("Asia/Tokyo");
ini_set("arg_separator.output", "&");

require_once __DIR__ . "/../../vendor/autoload.php";

use Model\TvtimetableModel;
use Lib\Tvtimetable\TvtimetableLibrary;

$lib1 = new TvtimetableLibrary("http://timetable.yanbe.net/html/13/2017/02/26_1.html?13");
$model1 = new TvtimetableModel();
var_dump($lib1->getProgramDate());
$model1->insertWithDate($lib1->getProgramElements(), $lib1->getProgramDate());
