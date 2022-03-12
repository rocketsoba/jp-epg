<?php

namespace Rocketsoba\EPG\Tests;

use Rocketsoba\EPG\Datasource\KakakuScrape;
use Rocketsoba\DomParserWrapper\Exception\DomNotFoundException;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use ReflectionProperty;
use Mockery;
use Exception;
use PHPUnit\Framework\TestCase;

class KakakuScrapeTest extends TestCase
{
    public function testInvalidChannels()
    {
        $class = new KakakuScrape();

        $test_channels = [
            "hoge",
            "hogehoge",
        ];
        $result = $class->convertChannels($test_channels);
        $this->assertTrue(is_array($result) && empty($result));
    }

    public function testValidChannels()
    {
        $class = new KakakuScrape();

        $test_channels = [
            "NTV",
            "テレビ東京",
            6,
        ];
        $result = $class->convertChannels($test_channels);
        $this->assertTrue(is_array($result));
        $this->assertSame(count($result), 3);
    }

    /**
     * @runInSeparateProcess
     */
    public function testTimeZone()
    {
        if (version_compare(PHP_VERSION, '7.0.0', '<')) {
            ini_set('error_reporting', E_ALL & ~E_WARNING);
        }
        ini_set("date.timezone", "");
        $this->expectException(Exception::class);

        $class = new KakakuScrape();
        $class->validateDate(date("Ymd", time()));
    }

    public function dateProvider()
    {
        return [
            [strtotime("+1 day")],
            [strtotime("2008-08-19")],
        ];
    }

    /**
     * @dataProvider dateProvider
     */
    public function testInvalidDate($date)
    {
        $this->expectException(Exception::class);

        $class = new KakakuScrape();
        $class->validateDate(date("Y-m-d", $date));
    }

    public function testValidDate()
    {
        $class = new KakakuScrape();
        $result = $class->validateDate(date("Y-m-d", strtotime("2008-08-20")));

        $this->assertTrue($result);
    }

    public function testScrape()
    {
        $channel_code = [
            4 => "NTV",
            6 => "TBS",
            8 => "CX",
            10 => "EX",
            12 => "TX",
        ];
        $date = "20080820";

        $curlbuilder_mock = Mockery::mock();
        $curlbuilder_mock->shouldReceive([
            "getReqhead" => "",
            "getReshead" => "",
            "getHttpCode" => 200,
        ]);
        $curlbuilder_mock->shouldReceive("getResult")
                         ->andReturn(
                             file_get_contents(__DIR__ . "/Fixtures/20080820_4.html"),
                             file_get_contents(__DIR__ . "/Fixtures/20080820_6.html"),
                             file_get_contents(__DIR__ . "/Fixtures/20080820_8.html"),
                             file_get_contents(__DIR__ . "/Fixtures/20080820_10.html"),
                             file_get_contents(__DIR__ . "/Fixtures/20080820_12.html")
                         );

        $mock = Mockery::mock(KakakuScrape::CLASS . "[!scrape,!__construct,!getPrograms]");
        $mock->shouldReceive([
            "request" => $curlbuilder_mock,
            "validateDate" => true,
            "convertChannels" => array_keys($channel_code),
        ]);

        $mock->scrape($date, $channel_code);
        $result = $mock->getPrograms();

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

    public function testAutoDateSet()
    {
        $channel_code = [
            4 => "NTV",
            6 => "TBS",
            8 => "CX",
            10 => "EX",
            12 => "TX",
        ];
        $date = "20080820";

        $curlbuilder_mock = Mockery::mock();
        $curlbuilder_mock->shouldReceive([
            "getReqhead" => "",
            "getReshead" => "",
            "getHttpCode" => 200,
        ]);
        $curlbuilder_mock->shouldReceive("getResult")
                         ->andReturn(file_get_contents(__DIR__ . "/Fixtures/20080820_4.html"));

        $mock = Mockery::mock(KakakuScrape::CLASS . "[!scrape,!__construct,!getDate]");
        $mock->shouldReceive([
            "request" => $curlbuilder_mock,
            "validateDate" => true,
            "convertChannels" => [4],
        ]);

        $mock->scrape();
        $this->assertSame($mock->getDate(), date("Y-m-d", time()));
    }

    public function testFetchNotFound()
    {
        $channel_code = [
            4 => "NTV",
            6 => "TBS",
            8 => "CX",
            10 => "EX",
            12 => "TX",
        ];

        $curlbuilder_mock = Mockery::mock();
        $curlbuilder_mock->shouldReceive([
            "getReqhead" => "",
            "getReshead" => "",
            "getHttpCode" => 403,
        ]);

        $mock = Mockery::mock(KakakuScrape::CLASS . "[!scrape,!__construct]");
        $mock->shouldReceive([
            "request" => $curlbuilder_mock,
            "validateDate" => true,
            "convertChannels" => array_keys($channel_code),
        ]);

        $this->expectException(Exception::class);
        $mock->scrape();
    }

    public function testInvalidChannelTitle()
    {
        $channel_code = [
            4 => "NTV",
            6 => "TBS",
            8 => "CX",
            10 => "EX",
            12 => "TX",
        ];
        $date = "20080820";

        $curlbuilder_mock = Mockery::mock();
        $curlbuilder_mock->shouldReceive([
            "getReqhead" => "",
            "getReshead" => "",
            "getHttpCode" => 200,
            "getResult" => file_get_contents(__DIR__ . "/Fixtures/20080820_invalid_title.html"),
        ]);

        $mock = Mockery::mock(KakakuScrape::CLASS . "[!scrape,!__construct]");
        $mock->shouldReceive([
            "request" => $curlbuilder_mock,
            "validateDate" => true,
            "convertChannels" => [4],
        ]);

        $this->expectException(Exception::class);
        $mock->scrape($date, [4]);
    }

    public function testDivNotFound()
    {
        $channel_code = [
            4 => "NTV",
            6 => "TBS",
            8 => "CX",
            10 => "EX",
            12 => "TX",
        ];
        $date = "20080820";

        $curlbuilder_mock = Mockery::mock();
        $curlbuilder_mock->shouldReceive([
            "getReqhead" => "",
            "getReshead" => "",
            "getHttpCode" => 200,
            "getResult" => file_get_contents(__DIR__ . "/Fixtures/20080820_no_div.html"),
        ]);

        $mock = Mockery::mock(KakakuScrape::CLASS . "[!scrape,!__construct]");
        $mock->shouldReceive([
            "request" => $curlbuilder_mock,
            "validateDate" => true,
            "convertChannels" => [4],
        ]);

        $this->expectException(DomNotFoundException::class);
        $mock->scrape($date, [4]);
    }

    public function testAElementNotFound()
    {
        $channel_code = [
            4 => "NTV",
            6 => "TBS",
            8 => "CX",
            10 => "EX",
            12 => "TX",
        ];
        $date = "20080820";

        $curlbuilder_mock = Mockery::mock();
        $curlbuilder_mock->shouldReceive([
            "getReqhead" => "",
            "getReshead" => "",
            "getHttpCode" => 200,
            "getResult" => file_get_contents(__DIR__ . "/Fixtures/20080820_no_a_elements.html"),
        ]);

        $mock = Mockery::mock(KakakuScrape::CLASS . "[!scrape,!__construct]");
        $mock->shouldReceive([
            "request" => $curlbuilder_mock,
            "validateDate" => true,
            "convertChannels" => [4],
        ]);

        $this->expectException(Exception::class);
        $mock->scrape($date, [4]);
    }

    public function testMalformedDate()
    {
        $channel_code = [
            4 => "NTV",
            6 => "TBS",
            8 => "CX",
            10 => "EX",
            12 => "TX",
        ];
        $date = "20080820";

        $curlbuilder_mock = Mockery::mock();
        $curlbuilder_mock->shouldReceive([
            "getReqhead" => "",
            "getReshead" => "",
            "getHttpCode" => 200,
            "getResult" => file_get_contents(__DIR__ . "/Fixtures/20080820_malformed_date.html"),
        ]);

        $mock = Mockery::mock(KakakuScrape::CLASS . "[!scrape,!__construct]");
        $mock->shouldReceive([
            "request" => $curlbuilder_mock,
            "validateDate" => true,
            "convertChannels" => [4],
        ]);

        $this->expectException(Exception::class);
        $mock->scrape($date, [4]);
    }
}
