<?php

namespace CultuurNet\UDB3\Search\ElasticSearch;

class UuidRegexIdUrlParserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var UuidRegexIdUrlParser
     */
    private $parser;

    public function setUp()
    {
        $this->parser = new UuidRegexIdUrlParser();
    }

    /**
     * @test
     */
    public function it_can_find_the_id_at_the_end_of_the_url()
    {
        $url = 'http://foo.bar/event/ab314bf2-703d-4411-ba0d-d2a0c056a7b4';
        $expectedId = 'ab314bf2-703d-4411-ba0d-d2a0c056a7b4';
        $actualId = $this->parser->getIdFromUrl($url);
        $this->assertEquals($expectedId, $actualId);
    }

    /**
     * @test
     */
    public function it_can_find_the_id_if_it_is_not_at_the_end_of_the_url()
    {
        // Note the trailing slash.
        $url = 'http://foo.bar/event/ab314bf2-703d-4411-ba0d-d2a0c056a7b4/';
        $expectedId = 'ab314bf2-703d-4411-ba0d-d2a0c056a7b4';
        $actualId = $this->parser->getIdFromUrl($url);
        $this->assertEquals($expectedId, $actualId);
    }

    /**
     * @test
     */
    public function it_throws_an_exception_if_no_uuid_can_be_found()
    {
        $url = 'http://foo.bar';
        $this->expectException(\RuntimeException::class);
        $this->parser->getIdFromUrl($url);
    }
}
