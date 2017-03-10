<?php

namespace CultuurNet\UDB3\Search\ElasticSearch\Place;

use CultuurNet\UDB3\Language;
use CultuurNet\UDB3\ReadModel\JsonDocument;
use CultuurNet\UDB3\Search\ElasticSearch\PathEndIdUrlParser;
use CultuurNet\UDB3\Search\JsonDocument\Testing\AssertJsonDocumentTrait;

class PlaceJsonDocumentTransformerTest extends \PHPUnit_Framework_TestCase
{
    use AssertJsonDocumentTrait;

    /**
     * @var PlaceJsonDocumentTransformer
     */
    private $transformer;

    public function setUp()
    {
        $this->transformer = new PlaceJsonDocumentTransformer(
            new PathEndIdUrlParser()
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
