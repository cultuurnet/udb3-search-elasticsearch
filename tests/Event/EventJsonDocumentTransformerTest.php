<?php

namespace CultuurNet\UDB3\Search\ElasticSearch\Event;

use CultuurNet\UDB3\ReadModel\JsonDocument;
use CultuurNet\UDB3\Search\ElasticSearch\PathEndIdUrlParser;
use CultuurNet\UDB3\Search\ElasticSearch\SimpleArrayLogger;
use CultuurNet\UDB3\Search\JsonDocument\Testing\AssertJsonDocumentTrait;

class EventJsonDocumentTransformerTest extends \PHPUnit_Framework_TestCase
{
    use AssertJsonDocumentTrait;

    /**
     * @var SimpleArrayLogger
     */
    private $logger;

    /**
     * @var EventJsonDocumentTransformer
     */
    private $transformer;

    public function setUp()
    {
        $this->logger = new SimpleArrayLogger();

        $this->transformer = new EventJsonDocumentTransformer(
            new PathEndIdUrlParser(),
            $this->logger
        );
    }

    /**
     * @test
     */
    public function it_transforms_required_fields()
    {
        $original = file_get_contents(__DIR__ . '/data/original.json');
        $originalDocument = new JsonDocument('23017cb7-e515-47b4-87c4-780735acc942', $original);

        $expected = file_get_contents(__DIR__ . '/data/indexed.json');
        $expectedDocument = new JsonDocument('23017cb7-e515-47b4-87c4-780735acc942', $expected);

        $actualDocument = $this->transformer->transform($originalDocument);

        $this->assertJsonDocumentEquals($this, $expectedDocument, $actualDocument);
    }

    /**
     * @test
     */
    public function it_logs_missing_required_fields()
    {
        $id = 'a9c2c833-5311-44bd-8cb8-b959196cb4b9';
        $originalDocument = new JsonDocument($id, '{}');
        $expectedDocument = new JsonDocument($id, '{"@type":"Event","name":{},"languages":[]}');

        $expectedLogs = [
            ['debug', "Transforming event $id for indexation.", []],
            ['warning', "Missing expected field '@id'.", []],
            ['warning', "Missing expected field 'name.nl'.", []],
            ['warning', "Missing expected field 'location'.", []],
            ['debug', "Transformation of event $id finished.", []],
        ];

        $actualDocument = $this->transformer->transform($originalDocument);
        $actualLogs = $this->logger->getLogs();

        $this->assertEquals($expectedDocument, $actualDocument);
        $this->assertEquals($expectedLogs, $actualLogs);
    }

    /**
     * @test
     */
    public function it_transforms_optional_fields_if_present()
    {
        $original = file_get_contents(__DIR__ . '/data/original-with-optional-fields.json');
        $originalDocument = new JsonDocument('23017cb7-e515-47b4-87c4-780735acc942', $original);

        $expected = file_get_contents(__DIR__ . '/data/indexed-with-optional-fields.json');
        $expectedDocument = new JsonDocument('23017cb7-e515-47b4-87c4-780735acc942', $expected);

        $actualDocument = $this->transformer->transform($originalDocument);

        $this->assertJsonDocumentEquals($this, $expectedDocument, $actualDocument);
    }

    /**
     * @test
     */
    public function it_skips_wrong_typical_age_range()
    {
        $original = file_get_contents(__DIR__ . '/data/original-with-wrong-typical-age-range.json');
        $originalDocument = new JsonDocument('23017cb7-e515-47b4-87c4-780735acc942', $original);

        $expected = file_get_contents(__DIR__ . '/data/indexed.json');
        $expectedDocument = new JsonDocument('23017cb7-e515-47b4-87c4-780735acc942', $expected);

        $actualDocument = $this->transformer->transform($originalDocument);

        $this->assertJsonDocumentEquals($this, $expectedDocument, $actualDocument);
    }
}
