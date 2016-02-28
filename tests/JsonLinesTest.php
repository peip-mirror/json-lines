<?php

namespace Rs\JsonLines\Tests;

use Rs\JsonLines\JsonLines;
use Rs\JsonLines\Exception\InvalidJson;
use Rs\JsonLines\Exception\NonTraversable;
use PHPUnit_Framework_TestCase as PHPUnit;

class JsonLinesTest extends PHPUnit
{
    protected $jsonLines;

    public function setUp()
    {
        $this->jsonLines = new JsonLines();
    }
    /**
     * @test
     */
    public function enline()
    {
        $expectedLines = [
            '{"one":1,"two":2}',
            '{"three":3,"four":4,"five":5}',
            '{"six":6,"seven":7,"key":"value"}',
            '{"nested":["a","b","c"]}',
            '{"newlined-content":"\ntest-content"}',
        ];
        $expectedLines = join(
            JsonLines::LINE_SEPARATOR,
            $expectedLines
        ) . JsonLines::LINE_SEPARATOR;

        $lines = $this->jsonLines->enline([
            ["one" => 1, "two" => 2],
            ["three" => 3, "four" => 4, "five" => 5],
            ["six" => 6, "seven" => 7, "key" => "value"],
            ["nested" => ["a", "b", "c"]],
            ["newlined-content" => "\ntest-content"]
        ]);

        $this->assertSame($expectedLines, $lines);
    }

    /**
     * @test
     */
    public function enlineWithFixture()
    {
        $fedJson = file_get_contents(
            __DIR__ . '/fixtures/winning_hands.json'
        );
        $enlinedJson = file_get_contents(
            __DIR__ . '/fixtures/winning_hands.jsonl'
        );

        $this->assertSame(
            $enlinedJson,
            $this->jsonLines->enline(json_decode($fedJson, true))
        );
    }

    /**
     * @test
     * @dataProvider nonTraversablesProvider
     */
    public function raisesExceptionOnNonTraversableData($data)
    {
        $this->setExpectedException(NonTraversable::class);

        $this->jsonLines->enline($data);
    }

    /**
     * @test
     */
    public function raisesExceptionOnInvalidJson()
    {
        $this->setExpectedException(InvalidJson::class);

        $invalidJson = '{"invalid"_,"14": 15}';
        $this->jsonLines->enline(["foo" => $invalidJson]);
    }

    /**
     * @test
     */
    public function deline()
    {
        $expectedJson = json_encode([
            ["one" => 1, "two" => 2],
            ["three" => 3, "four" => 4, "five" => 5],
            ["six" => 6, "seven" => 7, "key" => "value"],
            ["nested" => ["a", "b", "c"]],
        ]);

        $fedJsonLines = <<<JSON_LINES
{"one":1,"two":2}
{"three":3,"four":4,"five":5}
{"six":6,"seven":7,"key":"value"}
{"nested":["a","b","c"]}

JSON_LINES;

        $this->assertEquals(
            $expectedJson,
            $this->jsonLines->deline($fedJsonLines)
        );
        $this->assertEquals(
            "[]",
            $this->jsonLines->deline("")
        );
    }

    /**
     * @test
     */
    public function delineWithFixture()
    {
        $delinedJson = json_encode(json_decode(file_get_contents(
            __DIR__ . '/fixtures/winning_hands.json'
        )));

        $fedJsonLines = file_get_contents(
            __DIR__ . '/fixtures/winning_hands.jsonl'
        );

        $this->assertEquals(
            $delinedJson,
            $this->jsonLines->deline($fedJsonLines)
        );
    }

    /**
     * @return array
     */
    public function nonTraversablesProvider()
    {
        return [
            "null_data" => [null],
            "string_data" => ["non_traversable_string"],
            "numeric_data" => [125],
            "empty_data" => [[]],
        ];
    }
}
