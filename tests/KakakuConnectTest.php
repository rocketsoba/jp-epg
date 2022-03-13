<?php

namespace Rocketsoba\EPG\Tests;

use Rocketsoba\Curl\MyCurl;
use Rocketsoba\Curl\MyCurlBuilder;
use Rocketsoba\EPG\Datasource\KakakuScrape;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use PHPUnit\Framework\TestCase;

class KakakuConnectTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        $builder = new MyCurlBuilder("");
        $builder->deleteCookie();
        $curl_object = $builder->build();
        $curl_object->initialize();
    }

    public function testConnectScrape()
    {
        $channel_code = [
            4 => "NTV",
            6 => "TBS",
            8 => "CX",
            10 => "EX",
            12 => "TX",
        ];

        $class = new KakakuScrape();
        $class->scrape();

        $result = $class->getPrograms();

        foreach ($channel_code as $idx1 => $val1) {
            $this->assertTrue(array_key_exists($val1, $result));
            if (!isset($result[$val1])) {
                continue;
            }
            $is_invalid = false;
            foreach ($result[$val1] as $idx2 => $val2) {
                if (
                    !isset($val2["title"]) ||
                    !isset($val2["start_date"]) ||
                    !isset($val2["end_date"]) ||
                    strtotime($val2["end_date"]) < strtotime($val2["start_date"])
                ) {
                    $is_invalid = true;
                }
            }
            $this->assertFalse($is_invalid);
        }
    }
}
