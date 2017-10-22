<?php

date_default_timezone_set("Asia/Tokyo");
ini_set("arg_separator.output", "&");

require_once __DIR__ . "/../../vendor/autoload.php";

use Model\TvtimetableModel;
use Lib\Tvtimetable\TvtimetableLibrary;

$lib1 = new TvtimetableLibrary();
$model1 = new TvtimetableModel();
$lib1->scrape();
$model1->insertWithDate($lib1->getProgramElements(), $lib1->getProgramDate());
