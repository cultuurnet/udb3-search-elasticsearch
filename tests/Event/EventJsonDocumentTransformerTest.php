<?php

namespace CultuurNet\UDB3\Search\ElasticSearch\Event;

use Cake\Chronos\Chronos;
use CultuurNet\UDB3\ReadModel\JsonDocument;
use CultuurNet\UDB3\Search\ElasticSearch\Offer\OfferRegionServiceInterface;
use CultuurNet\UDB3\Search\ElasticSearch\PathEndIdUrlParser;
use CultuurNet\UDB3\Search\ElasticSearch\SimpleArrayLogger;
use CultuurNet\UDB3\Search\JsonDocument\Testing\AssertJsonDocumentTrait;
use CultuurNet\UDB3\Search\Region\RegionId;

class EventJsonDocumentTransformerTest extends \PHPUnit_Framework_TestCase
{
    use AssertJsonDocumentTrait;

    /**
     * @var OfferRegionServiceInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $offerRegionService;

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
        $this->offerRegionService = $this->createMock(OfferRegionServiceInterface::class);

        $this->logger = new SimpleArrayLogger();

        $this->transformer = new EventJsonDocumentTransformer(
            new PathEndIdUrlParser(),
            $this->offerRegionService,
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
        $expectedDocument = new JsonDocument(
            $id,
            '{"@type":"Event","name":{},"audienceType":"everyone","mediaObjectsCount":0}'
        );

        $expectedLogs = [
            ['debug', "Transforming event $id for indexation.", []],
            ['warning', "Missing expected field '@id'.", []],
            ['warning', "Missing expected field 'calendarType'.", []],
            ['warning', "Missing expected field 'workflowStatus'.", []],
            ['warning', "Missing expected field 'availableTo'.", []],
            ['warning', "Missing expected field 'name.nl'.", []],
            ['warning', "Missing expected field 'location'.", []],
            ['warning', "Missing expected field 'created'.", []],
            ['warning', "Missing expected field 'creator'.", []],
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
    public function it_transforms_events_with_multiple_dates()
    {
        $original = file_get_contents(__DIR__ . '/data/original-with-multiple-dates.json');
        $originalDocument = new JsonDocument('23017cb7-e515-47b4-87c4-780735acc942', $original);

        $expected = file_get_contents(__DIR__ . '/data/indexed-with-multiple-dates.json');
        $expectedDocument = new JsonDocument('23017cb7-e515-47b4-87c4-780735acc942', $expected);

        $actualDocument = $this->transformer->transform($originalDocument);

        $this->assertJsonDocumentEquals($this, $expectedDocument, $actualDocument);
    }

    /**
     * @test
     */
    public function it_transforms_permanent_events_to_an_infinite_date_range()
    {
        $original = file_get_contents(__DIR__ . '/data/original-permanent.json');
        $originalDocument = new JsonDocument('23017cb7-e515-47b4-87c4-780735acc942', $original);

        $expected = file_get_contents(__DIR__ . '/data/indexed-permanent.json');
        $expectedDocument = new JsonDocument('23017cb7-e515-47b4-87c4-780735acc942', $expected);

        $actualDocument = $this->transformer->transform($originalDocument);

        $this->assertJsonDocumentEquals($this, $expectedDocument, $actualDocument);
    }

    /**
     * @test
     */
    public function it_transforms_periodic_events()
    {
        $original = file_get_contents(__DIR__ . '/data/original-periodic.json');
        $originalDocument = new JsonDocument('23017cb7-e515-47b4-87c4-780735acc942', $original);

        $expected = file_get_contents(__DIR__ . '/data/indexed-periodic.json');
        $expectedDocument = new JsonDocument('23017cb7-e515-47b4-87c4-780735acc942', $expected);

        $actualDocument = $this->transformer->transform($originalDocument);

        $this->assertJsonDocumentEquals($this, $expectedDocument, $actualDocument);
    }

    /**
     * @test
     */
    public function it_transforms_periodic_opening_hours_to_date_ranges()
    {
        $original = file_get_contents(__DIR__ . '/data/original-periodic-with-opening-hours.json');
        $originalDocument = new JsonDocument('23017cb7-e515-47b4-87c4-780735acc942', $original);

        $expected = file_get_contents(__DIR__ . '/data/indexed-periodic-with-opening-hours.json');
        $expectedDocument = new JsonDocument('23017cb7-e515-47b4-87c4-780735acc942', $expected);

        $expectedLogs = [
            ['debug', "Transforming event 23017cb7-e515-47b4-87c4-780735acc942 for indexation.", []],
            ['debug', "Transformation of event 23017cb7-e515-47b4-87c4-780735acc942 finished.", []],
        ];

        $actualDocument = $this->transformer->transform($originalDocument);
        $actualLogs = $this->logger->getLogs();

        $this->assertJsonDocumentEquals($this, $expectedDocument, $actualDocument);
        $this->assertEquals($expectedLogs, $actualLogs);
    }

    /**
     * @test
     */
    public function it_transforms_permanent_opening_hours_to_date_ranges()
    {
        Chronos::setTestNow(
            Chronos::createFromFormat(
                \DateTime::ATOM,
                '2017-05-09T15:11:32+02:00'
            )
        );

        $original = file_get_contents(__DIR__ . '/data/original-permanent-with-opening-hours.json');
        $originalDocument = new JsonDocument('23017cb7-e515-47b4-87c4-780735acc942', $original);

        $expected = file_get_contents(__DIR__ . '/data/indexed-permanent-with-opening-hours.json');
        $expectedDocument = new JsonDocument('23017cb7-e515-47b4-87c4-780735acc942', $expected);

        $expectedLogs = [
            ['debug', "Transforming event 23017cb7-e515-47b4-87c4-780735acc942 for indexation.", []],
            ['debug', "Transformation of event 23017cb7-e515-47b4-87c4-780735acc942 finished.", []],
        ];

        $actualDocument = $this->transformer->transform($originalDocument);
        $actualLogs = $this->logger->getLogs();

        $this->assertJsonDocumentEquals($this, $expectedDocument, $actualDocument);
        $this->assertEquals($expectedLogs, $actualLogs);
    }

    /**
     * @test
     */
    public function it_logs_incorrect_opening_hours_and_does_not_transform_them()
    {
        $original = file_get_contents(__DIR__ . '/data/original-periodic-with-wrong-opening-hours.json');
        $originalDocument = new JsonDocument('23017cb7-e515-47b4-87c4-780735acc942', $original);

        $expected = file_get_contents(__DIR__ . '/data/indexed-periodic-without-date-range.json');
        $expectedDocument = new JsonDocument('23017cb7-e515-47b4-87c4-780735acc942', $expected);

        $expectedLogs = [
            ['debug', "Transforming event 23017cb7-e515-47b4-87c4-780735acc942 for indexation.", []],
            ['warning', "Missing expected field 'openingHours[0].dayOfWeek'.", []],
            ['warning', "Missing expected field 'openingHours[1].closes'.", []],
            ['warning', "Missing expected field 'openingHours[2].opens'.", []],
            ['warning', "Unknown day 'st. patrick's day' in opening hours.", []],
            ['warning', "Missing expected field 'subEvent'.", []],
            ['debug', "Transformation of event 23017cb7-e515-47b4-87c4-780735acc942 finished.", []],
        ];

        $actualDocument = $this->transformer->transform($originalDocument);
        $actualLogs = $this->logger->getLogs();

        $this->assertJsonDocumentEquals($this, $expectedDocument, $actualDocument);
        $this->assertEquals($expectedLogs, $actualLogs);
    }

    /**
     * @test
     */
    public function it_does_not_polyfill_sub_event_for_unknown_calendar_types()
    {
        $original = file_get_contents(__DIR__ . '/data/original-with-wrong-calendar-type.json');
        $originalDocument = new JsonDocument('23017cb7-e515-47b4-87c4-780735acc942', $original);

        $expected = file_get_contents(__DIR__ . '/data/indexed-with-wrong-calendar-type.json');
        $expectedDocument = new JsonDocument('23017cb7-e515-47b4-87c4-780735acc942', $expected);

        $expectedLogs = [
            ['debug', "Transforming event 23017cb7-e515-47b4-87c4-780735acc942 for indexation.", []],
            ['warning', "Could not polyfill subEvent for unknown calendarType 'foobar'.", []],
            ['warning', "Missing expected field 'subEvent'.", []],
            ['debug', "Transformation of event 23017cb7-e515-47b4-87c4-780735acc942 finished.", []],
        ];

        $actualDocument = $this->transformer->transform($originalDocument);
        $actualLogs = $this->logger->getLogs();

        $this->assertJsonDocumentEquals($this, $expectedDocument, $actualDocument);
        $this->assertEquals($expectedLogs, $actualLogs);
    }

    /**
     * @test
     */
    public function it_logs_missing_start_date_when_sub_event_is_also_missing()
    {
        $original = file_get_contents(__DIR__ . '/data/original-without-start-date.json');
        $originalDocument = new JsonDocument('23017cb7-e515-47b4-87c4-780735acc942', $original);

        $expected = file_get_contents(__DIR__ . '/data/indexed-without-date-range.json');
        $expectedDocument = new JsonDocument('23017cb7-e515-47b4-87c4-780735acc942', $expected);

        $expectedLogs = [
            ['debug', "Transforming event 23017cb7-e515-47b4-87c4-780735acc942 for indexation.", []],
            ['warning', "Missing expected field 'startDate'.", []],
            ['warning', "Missing expected field 'subEvent'.", []],
            ['debug', "Transformation of event 23017cb7-e515-47b4-87c4-780735acc942 finished.", []],
        ];

        $actualDocument = $this->transformer->transform($originalDocument);
        $actualLogs = $this->logger->getLogs();

        $this->assertJsonDocumentEquals($this, $expectedDocument, $actualDocument);
        $this->assertEquals($expectedLogs, $actualLogs);
    }

    /**
     * @test
     */
    public function it_logs_missing_end_date_when_sub_event_is_also_missing()
    {
        $original = file_get_contents(__DIR__ . '/data/original-without-end-date.json');
        $originalDocument = new JsonDocument('23017cb7-e515-47b4-87c4-780735acc942', $original);

        $expected = file_get_contents(__DIR__ . '/data/indexed-without-date-range.json');
        $expectedDocument = new JsonDocument('23017cb7-e515-47b4-87c4-780735acc942', $expected);

        $expectedLogs = [
            ['debug', "Transforming event 23017cb7-e515-47b4-87c4-780735acc942 for indexation.", []],
            ['warning', "Missing expected field 'endDate'.", []],
            ['warning', "Missing expected field 'subEvent'.", []],
            ['debug', "Transformation of event 23017cb7-e515-47b4-87c4-780735acc942 finished.", []],
        ];

        $actualDocument = $this->transformer->transform($originalDocument);
        $actualLogs = $this->logger->getLogs();

        $this->assertJsonDocumentEquals($this, $expectedDocument, $actualDocument);
        $this->assertEquals($expectedLogs, $actualLogs);
    }

    /**
     * @test
     */
    public function it_logs_missing_start_and_end_date_in_sub_events()
    {
        $original = file_get_contents(__DIR__ . '/data/original-with-multiple-dates-and-wrong-sub-events.json');
        $originalDocument = new JsonDocument('23017cb7-e515-47b4-87c4-780735acc942', $original);

        $expected = file_get_contents(__DIR__ . '/data/indexed-with-multiple-dates-without-date-range.json');
        $expectedDocument = new JsonDocument('23017cb7-e515-47b4-87c4-780735acc942', $expected);

        $expectedLogs = [
            ['debug', "Transforming event 23017cb7-e515-47b4-87c4-780735acc942 for indexation.", []],
            ['warning', "Missing expected field 'subEvent[0].startDate'.", []],
            ['warning', "Missing expected field 'subEvent[1].endDate'.", []],
            ['debug', "Transformation of event 23017cb7-e515-47b4-87c4-780735acc942 finished.", []],
        ];

        $actualDocument = $this->transformer->transform($originalDocument);
        $actualLogs = $this->logger->getLogs();

        $this->assertJsonDocumentEquals($this, $expectedDocument, $actualDocument);
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

    /**
     * @test
     */
    public function it_skips_wrong_available_from()
    {
        $original = file_get_contents(__DIR__ . '/data/original-with-wrong-available-from.json');
        $originalDocument = new JsonDocument('23017cb7-e515-47b4-87c4-780735acc942', $original);

        $expected = file_get_contents(__DIR__ . '/data/indexed.json');
        $expectedDocument = new JsonDocument('23017cb7-e515-47b4-87c4-780735acc942', $expected);

        $expectedLogs = [
            ['debug', "Transforming event 23017cb7-e515-47b4-87c4-780735acc942 for indexation.", []],
            ['warning', "Found availableFrom but workflowStatus is DRAFT.", []],
            ['error', "Could not parse availableFrom as an ISO-8601 datetime.", []],
            ['debug', "Transformation of event 23017cb7-e515-47b4-87c4-780735acc942 finished.", []],
        ];

        $actualDocument = $this->transformer->transform($originalDocument);
        $actualLogs = $this->logger->getLogs();

        $this->assertJsonDocumentEquals($this, $expectedDocument, $actualDocument);
        $this->assertEquals($expectedLogs, $actualLogs);
    }

    /**
     * @test
     */
    public function it_skips_wrong_available_to()
    {
        $original = file_get_contents(__DIR__ . '/data/original-with-wrong-available-to.json');
        $originalDocument = new JsonDocument('23017cb7-e515-47b4-87c4-780735acc942', $original);

        $expected = file_get_contents(__DIR__ . '/data/indexed-without-available-to.json');
        $expectedDocument = new JsonDocument('23017cb7-e515-47b4-87c4-780735acc942', $expected);

        $expectedLogs = [
            ['debug', "Transforming event 23017cb7-e515-47b4-87c4-780735acc942 for indexation.", []],
            ['warning', "Found availableFrom but workflowStatus is DRAFT.", []],
            ['error', "Could not parse availableTo as an ISO-8601 datetime.", []],
            ['debug', "Transformation of event 23017cb7-e515-47b4-87c4-780735acc942 finished.", []],
        ];

        $actualDocument = $this->transformer->transform($originalDocument);
        $actualLogs = $this->logger->getLogs();

        $this->assertJsonDocumentEquals($this, $expectedDocument, $actualDocument);
        $this->assertEquals($expectedLogs, $actualLogs);
    }

    /**
     * @test
     */
    public function it_adds_regions_if_there_are_any_matching()
    {
        $original = file_get_contents(__DIR__ . '/data/original-with-optional-fields.json');
        $originalDocument = new JsonDocument('23017cb7-e515-47b4-87c4-780735acc942', $original);

        $expected = file_get_contents(__DIR__ . '/data/indexed-with-regions.json');
        $expectedDocument = new JsonDocument('23017cb7-e515-47b4-87c4-780735acc942', $expected);

        $this->offerRegionService->expects($this->once())
            ->method('getRegionIds')
            ->willReturn(
                [
                    new RegionId('prv-vlaams-brabant'),
                    new RegionId('gem-leuven'),
                ]
            );

        $actualDocument = $this->transformer->transform($originalDocument);

        $this->assertJsonDocumentEquals($this, $expectedDocument, $actualDocument);
    }

    /**
     * @test
     */
    public function it_transforms_modified_metadata_date()
    {
        $original = file_get_contents(__DIR__ . '/data/original-modified.json');
        $originalDocument = new JsonDocument('179c89c5-dba4-417b-ae96-62e7a12c2405', $original);

        $expected = file_get_contents(__DIR__ . '/data/indexed-modified.json');
        $expectedDocument = new JsonDocument('179c89c5-dba4-417b-ae96-62e7a12c2405', $expected);

        $actualDocument = $this->transformer->transform($originalDocument);

        $this->assertJsonDocumentEquals($this, $expectedDocument, $actualDocument);
    }
}
