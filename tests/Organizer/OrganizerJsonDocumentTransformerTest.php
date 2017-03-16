<?php

namespace CultuurNet\UDB3\Search\ElasticSearch\Organizer;

use CultuurNet\UDB3\ReadModel\JsonDocument;
use CultuurNet\UDB3\Search\JsonDocument\Testing\AssertJsonDocumentTrait;

class OrganizerJsonDocumentTransformerTest extends \PHPUnit_Framework_TestCase
{
    use AssertJsonDocumentTrait;

    /**
     * @var OrganizerJsonDocumentTransformer
     */
    private $transformer;

    public function setUp()
    {
        $this->transformer = new OrganizerJsonDocumentTransformer();
    }

    /**
     * @test
     */
    public function it_keeps_the_properties_that_are_required_to_maintain_backwards_compatibility_with_the_api()
    {
        $original = file_get_contents(__DIR__ . '/data/original.json');
        $originalDocument = new JsonDocument('5e0b3f9c-5947-46a0-b8f2-a1a5a37f3b83', $original);

        $expected = file_get_contents(__DIR__ . '/data/indexed.json');
        $expectedDocument = new JsonDocument('5e0b3f9c-5947-46a0-b8f2-a1a5a37f3b83', $expected);

        $actualDocument = $this->transformer->transform($originalDocument);

        $this->assertJsonDocumentEquals($this, $expectedDocument, $actualDocument);
    }
}
