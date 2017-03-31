<?php

namespace CultuurNet\UDB3\Search\ElasticSearch\Place;

use CultuurNet\UDB3\ReadModel\JsonDocument;
use CultuurNet\UDB3\Search\ElasticSearch\PathEndIdUrlParser;
use CultuurNet\UDB3\Search\ElasticSearch\SimpleArrayLogger;
use CultuurNet\UDB3\Search\JsonDocument\Testing\AssertJsonDocumentTrait;

class PlaceJsonDocumentTransformerTest extends \PHPUnit_Framework_TestCase
{
    use AssertJsonDocumentTrait;

    /**
     * @var SimpleArrayLogger
     */
    private $logger;

    /**
     * @var PlaceJsonDocumentTransformer
     */
    private $transformer;

    public function setUp()
    {
        $this->logger = new SimpleArrayLogger();

        $this->transformer = new PlaceJsonDocumentTransformer(
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
        $originalDocument = new JsonDocument('179c89c5-dba4-417b-ae96-62e7a12c2405', $original);

        $expected = file_get_contents(__DIR__ . '/data/indexed.json');
        $expectedDocument = new JsonDocument('179c89c5-dba4-417b-ae96-62e7a12c2405', $expected);

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
        $expectedDocument = new JsonDocument($id, '{"@type":"Place","name":{},"languages":[]}');

        $expectedLogs = [
            ['debug', "Transforming place $id for indexation.", []],
            ['warning', "Missing expected field '@id'.", []],
            ['warning', "Missing expected field 'name.nl'.", []],
            ['warning', "Missing expected field 'address.addressLocality'.", []],
            ['warning', "Missing expected field 'address.postalCode'.", []],
            ['warning', "Missing expected field 'address.streetAddress'.", []],
            ['debug', "Transformation of place $id finished.", []],
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
        $originalDocument = new JsonDocument('179c89c5-dba4-417b-ae96-62e7a12c2405', $original);

        $expected = file_get_contents(__DIR__ . '/data/indexed-with-optional-fields.json');
        $expectedDocument = new JsonDocument('179c89c5-dba4-417b-ae96-62e7a12c2405', $expected);

        $actualDocument = $this->transformer->transform($originalDocument);

        $this->assertJsonDocumentEquals($this, $expectedDocument, $actualDocument);
    }
}
