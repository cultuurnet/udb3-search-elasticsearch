<?php

namespace CultuurNet\UDB3\Search\ElasticSearch\Organizer;

use CultuurNet\UDB3\ReadModel\JsonDocument;
use CultuurNet\UDB3\Search\ElasticSearch\JsonDocument\CopyJsonName;
use CultuurNet\UDB3\Search\ElasticSearch\SimpleArrayLogger;
use CultuurNet\UDB3\Search\JsonDocument\Testing\AssertJsonDocumentTrait;

class OrganizerJsonDocumentTransformerTest extends \PHPUnit_Framework_TestCase
{
    use AssertJsonDocumentTrait;

    /**
     * @var SimpleArrayLogger
     */
    private $logger;

    /**
     * @var OrganizerJsonDocumentTransformer
     */
    private $transformer;

    public function setUp()
    {
        $this->logger = new SimpleArrayLogger();

        $this->transformer = new OrganizerJsonDocumentTransformer(
            new CopyJsonName($this->logger)
        );
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

    /**
     * @test
     */
    public function it_handles_all_known_languages()
    {
        $original = file_get_contents(__DIR__ . '/data/all_languages_original.json');
        $originalDocument = new JsonDocument('5e0b3f9c-5947-46a0-b8f2-a1a5a37f3b83', $original);

        $expected = file_get_contents(__DIR__ . '/data/all_languages_indexed.json');
        $expectedDocument = new JsonDocument('5e0b3f9c-5947-46a0-b8f2-a1a5a37f3b83', $expected);

        $actualDocument = $this->transformer->transform($originalDocument);

        $this->assertJsonDocumentEquals($this, $expectedDocument, $actualDocument);
    }

    /**
     * @test
     */
    public function it_logs_missing_required_name_for_main_language()
    {
        $original = file_get_contents(__DIR__ . '/data/missing_main_language_original.json');
        $originalDocument = new JsonDocument('5e0b3f9c-5947-46a0-b8f2-a1a5a37f3b83', $original);

        $expected = file_get_contents(__DIR__ . '/data/missing_main_language_indexed.json');
        $expectedDocument = new JsonDocument('5e0b3f9c-5947-46a0-b8f2-a1a5a37f3b83', $expected);

        $expectedLogs = [
            ['warning', "Missing expected field 'name.nl'.", []],
        ];

        $actualDocument = $this->transformer->transform($originalDocument);
        $actualLogs = $this->logger->getLogs();

        $this->assertJsonDocumentEquals($this, $expectedDocument, $actualDocument);
        $this->assertEquals($expectedLogs, $actualLogs);
    }
}
