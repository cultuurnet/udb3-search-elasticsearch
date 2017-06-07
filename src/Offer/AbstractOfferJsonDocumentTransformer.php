<?php

namespace CultuurNet\UDB3\Search\ElasticSearch\Offer;

use Cake\Chronos\Chronos;
use CultuurNet\UDB3\Offer\OfferType;
use CultuurNet\UDB3\ReadModel\JsonDocument;
use CultuurNet\UDB3\Search\ElasticSearch\IdUrlParserInterface;
use CultuurNet\UDB3\Search\JsonDocument\JsonDocumentTransformerInterface;
use CultuurNet\UDB3\Search\Region\RegionId;
use Psr\Log\LoggerInterface;

abstract class AbstractOfferJsonDocumentTransformer implements JsonDocumentTransformerInterface
{
    /**
     * @var IdUrlParserInterface
     */
    protected $idUrlParser;

    /**
     * @var OfferRegionServiceInterface
     */
    protected $offerRegionService;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param IdUrlParserInterface $idUrlParser
     * @param OfferRegionServiceInterface $offerRegionService
     * @param LoggerInterface $logger
     */
    public function __construct(
        IdUrlParserInterface $idUrlParser,
        OfferRegionServiceInterface $offerRegionService,
        LoggerInterface $logger
    ) {
        $this->idUrlParser = $idUrlParser;
        $this->offerRegionService = $offerRegionService;
        $this->logger = $logger;
    }

    /**
     * @param \stdClass $from
     * @param \stdClass $to
     */
    protected function copyAvailableRange(\stdClass $from, \stdClass $to)
    {
        if (isset($from->availableFrom) && isset($from->workflowStatus) && $from->workflowStatus == 'DRAFT') {
            $this->logger->warning('Found availableFrom but workflowStatus is DRAFT.');
        }

        $availableTo = $this->getAvailableDate($from, 'availableTo', true);
        $availableFrom = $this->getAvailableDate($from, 'availableFrom', false);

        if (!$availableTo) {
            return;
        }

        $to->availableTo = $availableTo->format(\DateTime::ATOM);

        if (!$availableFrom) {
            return;
        }

        $to->availableRange = new \stdClass();
        $to->availableRange->gte = $availableFrom->format(\DateTime::ATOM);
        $to->availableRange->lte = $availableTo->format(\DateTime::ATOM);
    }

    /**
     * @param \stdClass $from
     * @param string $propertyName
     * @param bool $logMissingField
     * @return \DateTimeImmutable|null
     */
    private function getAvailableDate(\stdClass $from, $propertyName, $logMissingField)
    {
        if (!isset($from->{$propertyName})) {
            if ($logMissingField) {
                $this->logMissingExpectedField($propertyName);
            }
            return null;
        }

        // Convert to DateTimeImmutable to verify the format is correct.
        $date = \DateTimeImmutable::createFromFormat(\DateTime::ATOM, $from->{$propertyName});

        if (!$date) {
            $this->logger->error("Could not parse {$propertyName} as an ISO-8601 datetime.");
            return null;
        }

        return $date;
    }

    /**
     * @param \stdClass $from
     * @param \stdClass $to
     */
    protected function copyCalendarType(\stdClass $from, \stdClass $to)
    {
        if (!isset($from->calendarType)) {
            $this->logMissingExpectedField('calendarType');
            return;
        }

        $to->calendarType = $from->calendarType;
    }

    /**
     * @param \stdClass $from
     * @param \stdClass $to
     */
    protected function copyDateRange(\stdClass $from, \stdClass $to)
    {
        if (!isset($from->calendarType)) {
            // Logged in AbstractOfferJsonDocumentTransformer::copyCalendarType().
            return;
        }

        $from = $this->polyFillJsonLdSubEvents($from);

        if (isset($from->subEvent)) {
            // Index each subEvent as a separate date range.
            $dateRange = $this->convertSubEventsToDateRanges($from->subEvent);
        } elseif (!isset($from->subEvent) && $from->calendarType == 'permanent') {
            // Index a single range without any bounds.
            $dateRange = [new \stdClass()];
        } else {
            $this->logMissingExpectedField('subEvent');
            $dateRange = [];
        }

        if (!empty($dateRange)) {
            $to->dateRange = $dateRange;
        }
    }

    /**
     * @param \stdClass $from
     * @return \stdClass
     */
    private function polyFillJsonLdSubEvents(\stdClass $from)
    {
        if ($from->calendarType == 'single' || $from->calendarType == 'periodic') {
            if (!isset($from->startDate)) {
                $this->logMissingExpectedField('startDate');
                return $from;
            }

            if (!isset($from->endDate)) {
                $this->logMissingExpectedField('endDate');
                return $from;
            }
        }

        switch ($from->calendarType) {
            case 'single':
                return $this->polyFillJsonLdSubEventsFromStartAndEndDate($from);
                break;

            case 'multiple':
                return $from;
                break;

            case 'periodic':
                if (isset($from->openingHours)) {
                    return $this->polyFillJsonLdSubEventsFromOpeningHours($from);
                } else {
                    return $this->polyFillJsonLdSubEventsFromStartAndEndDate($from);
                }
                break;

            case 'permanent':
                if (isset($from->openingHours)) {
                    return $this->polyFillJsonLdSubEventsFromOpeningHours($from);
                } else {
                    return $from;
                }
                break;

            default:
                $this->logger->warning("Could not polyfill subEvent for unknown calendarType '{$from->calendarType}'.");
                return $from;
                break;
        }
    }

    /**
     * @param \stdClass $from
     * @return \stdClass
     */
    private function polyFillJsonLdSubEventsFromStartAndEndDate(\stdClass $from)
    {
        $from = clone $from;

        $from->subEvent = [
            (object) [
                '@type' => 'Event',
                'startDate' => $from->startDate,
                'endDate' => $from->endDate,
            ],
        ];

        return $from;
    }

    /**
     * @param \stdClass $from
     * @return \stdClass
     */
    private function polyFillJsonLdSubEventsFromOpeningHours(\stdClass $from)
    {
        $from = clone $from;

        $openingHoursByDay = $this->convertOpeningHoursToListGroupedByDay($from->openingHours);

        if ($from->calendarType == 'permanent') {
            $now = new Chronos();
            $startDate = $now->modify('-6 months');
            $endDate = $now->modify('+12 months');
        } else {
            $startDate = Chronos::createFromFormat(\DateTime::ATOM, $from->startDate);
            $endDate = Chronos::createFromFormat(\DateTime::ATOM, $from->endDate);
        }

        $interval = new \DateInterval('P1D');
        $period = new \DatePeriod($startDate, $interval, $endDate);

        $subEvent = [];

        /* @var \DateTime $date */
        foreach ($period as $date) {
            $day = strtolower($date->format('l'));

            foreach ($openingHoursByDay[$day] as $openingHours) {
                $subEventStartDate = new \DateTimeImmutable(
                    $date->format('Y-m-d') . 'T' . $openingHours->opens . ':00',
                    new \DateTimeZone('Europe/Brussels')
                );

                $subEventEndDate = new \DateTimeImmutable(
                    $date->format('Y-m-d') . 'T' . $openingHours->closes . ':00',
                    new \DateTimeZone('Europe/Brussels')
                );

                $subEvent[] = (object) [
                    '@type' => 'Event',
                    'startDate' => $subEventStartDate->format(\DateTime::ATOM),
                    'endDate' => $subEventEndDate->format(\DateTime::ATOM),
                ];
            }
        }

        if (!empty($subEvent)) {
            $from->subEvent = $subEvent;
        }

        return $from;
    }

    /**
     * @param \stdClass[] $openingHours
     * @return \stdClass[]
     */
    private function convertOpeningHoursToListGroupedByDay(array $openingHours)
    {
        $openingHoursByDay = [
            'monday' => [],
            'tuesday' => [],
            'wednesday' => [],
            'thursday' => [],
            'friday' => [],
            'saturday' => [],
            'sunday' => [],
        ];

        foreach ($openingHours as $index => $openingHoursEntry) {
            if (!isset($openingHoursEntry->dayOfWeek)) {
                $this->logMissingExpectedField("openingHours[{$index}].dayOfWeek");
                continue;
            }

            if (!isset($openingHoursEntry->opens)) {
                $this->logMissingExpectedField("openingHours[{$index}].opens");
                continue;
            }

            if (!isset($openingHoursEntry->closes)) {
                $this->logMissingExpectedField("openingHours[{$index}].closes");
                continue;
            }

            foreach ($openingHoursEntry->dayOfWeek as $day) {
                if (!array_key_exists($day, $openingHoursByDay)) {
                    $this->logger->warning("Unknown day '{$day}' in opening hours.");
                    continue;
                }

                $openingHoursByDay[$day][] = (object) [
                    'opens' => $openingHoursEntry->opens,
                    'closes' => $openingHoursEntry->closes,
                ];
            }
        }

        foreach ($openingHoursByDay as $day => &$openingHours) {
            sort($openingHours);
        }

        return $openingHoursByDay;
    }

    /**
     * @param \stdClass[] $subEvents
     * @return \stdClass[]
     */
    private function convertSubEventsToDateRanges(array $subEvents)
    {
        $dateRanges = [];

        foreach ($subEvents as $index => $subEvent) {
            if (!isset($subEvent->startDate)) {
                $this->logMissingExpectedField("subEvent[{$index}].startDate");
                continue;
            }

            if (!isset($subEvent->endDate)) {
                $this->logMissingExpectedField("subEvent[{$index}].endDate");
                continue;
            }

            $range = new \stdClass();
            $range->gte = $subEvent->startDate;
            $range->lte = $subEvent->endDate;
            $dateRanges[] = $range;
        }

        return $dateRanges;
    }

    /**
     * @param \stdClass $from
     * @param \stdClass $to
     */
    protected function copyWorkflowStatus(\stdClass $from, \stdClass $to)
    {
        if (isset($from->workflowStatus)) {
            $to->workflowStatus = $from->workflowStatus;
        } else {
            $this->logMissingExpectedField('workflowStatus');
        }
    }

    /**
     * @param \stdClass $from
     * @param \stdClass $to
     * @param $fallbackType
     */
    protected function copyIdentifiers(\stdClass $from, \stdClass $to, $fallbackType)
    {
        if (isset($from->{"@id"})) {
            $to->{"@id"} = $from->{"@id"};
        } else {
            $this->logMissingExpectedField("@id");
        }

        $to->{"@type"} = isset($from->{"@type"}) ? $from->{"@type"} : $fallbackType;

        // Not included in the if statement above because it should be under
        // @type in the JSON. No else statement because we don't want to log a
        // missing @id twice.
        if (isset($from->{"@id"})) {
            $to->id = $this->idUrlParser->getIdFromUrl($from->{"@id"});
        }
    }

    /**
     * @param \stdClass $from
     * @param \stdClass $to
     */
    // TODO: Code needs to be shared between offer and organizer.
    protected function copyName(\stdClass $from, \stdClass $to)
    {
        $to->name = new \stdClass();

        // TODO: Use $jsonLd->mainLanguage to get the required name field.
        if (isset($from->name->nl)) {
            $to->name->nl = $from->name->nl;
        } else {
            $this->logMissingExpectedField('name.nl');
        }

        // TODO: The list of known languages gets bigger.
        // https://jira.uitdatabank.be/browse/III-2161 (es and it)
        if (isset($from->name->fr)) {
            $to->name->fr = $from->name->fr;
        }

        if (isset($from->name->en)) {
            $to->name->en = $from->name->en;
        }

        if (isset($from->name->de)) {
            $to->name->de = $from->name->de;
        }
    }

    /**
     * @param \stdClass $from
     * @param \stdClass $to
     */
    protected function copyDescription(\stdClass $from, \stdClass $to)
    {
        // Only copy over the languages that we know how to analyze.
        if (isset($from->description)) {
            $to->description = new \stdClass();
        }

        if (isset($from->description->nl)) {
            $to->description->nl = $from->description->nl;
        }

        if (isset($from->description->fr)) {
            $to->description->fr = $from->description->fr;
        }

        if (isset($from->description->en)) {
            $to->description->en = $from->description->en;
        }

        if (isset($from->description->de)) {
            $to->description->de = $from->description->de;
        }
    }

    /**
     * @param \stdClass $from
     * @param \stdClass $to
     */
    protected function copyLanguages(\stdClass $from, \stdClass $to)
    {
        $translatableFields = ['name', 'description'];
        $languages = [];
        $completedLanguages = [];

        foreach ($translatableFields as $translatableField) {
            if (!isset($from->{$translatableField})) {
                continue;
            }

            $languagesOnField = array_keys(
                get_object_vars($from->{$translatableField})
            );

            $languages = array_merge(
                $languages,
                $languagesOnField
            );

            if ($translatableField == $translatableFields[0]) {
                $completedLanguages = $languagesOnField;
            } else {
                $completedLanguages = array_intersect($completedLanguages, $languagesOnField);
            }
        }

        // Make sure to use array_values(), because array_unique() keeps the
        // original keys so this can result in gaps. This is bad because those
        // gaps result in the array being converted to an object when encoding
        // as JSON.
        $languages = array_values(array_unique($languages));
        $completedLanguages = array_values(array_unique($completedLanguages));

        if (!empty($languages)) {
            $to->languages = $languages;
        }

        if (!empty($completedLanguages)) {
            $to->completedLanguages = $completedLanguages;
        }
    }

    /**
     * @param \stdClass $from
     * @param \stdClass $to
     */
    protected function copyLabels(\stdClass $from, \stdClass $to)
    {
        $labels = $this->getLabels($from);

        if (!empty($labels)) {
            $to->labels = $labels;
        }
    }

    /**
     * @param \stdClass $from
     * @param \stdClass $to
     */
    protected function copyLabelsForFreeTextSearch(\stdClass $from, \stdClass $to)
    {
        $labels = $this->getLabels($from);

        if (!empty($labels)) {
            $to->labels_free_text = $labels;
        }
    }

    /**
     * @param \stdClass $object
     * @return array
     */
    protected function getLabels(\stdClass $object)
    {
        $labels = [];

        if (isset($object->labels)) {
            $labels = array_merge($labels, $object->labels);
        }

        if (isset($object->hiddenLabels)) {
            $labels = array_merge($labels, $object->hiddenLabels);
        }

        return $labels;
    }

    /**
     * @param \stdClass $from
     * @param \stdClass $to
     */
    protected function copyTerms(\stdClass $from, \stdClass $to)
    {
        $terms = $this->getTerms($from);
        if (!empty($terms)) {
            $to->terms = $terms;
        }
    }

    /**
     * @param \stdClass $from
     * @param \stdClass $to
     */
    protected function copyTermsForFreeTextSearch(\stdClass $from, \stdClass $to)
    {
        $terms = $this->getTerms($from);
        if (!empty($terms)) {
            $to->terms_free_text = $to->terms;
        }
    }

    /**
     * @param \stdClass $object
     * @return \stdClass[]
     */
    protected function getTerms(\stdClass $object)
    {
        if (!isset($object->terms)) {
            return [];
        }

        return array_map(
            function (\stdClass $term) {
                // Don't copy all properties, just those we're interested in.
                $copy = new \stdClass();
                $copy->id = $term->id;
                $copy->label = $term->label;
                return $copy;
            },
            $object->terms
        );
    }

    /**
     * @param \stdClass $from
     * @param \stdClass $to
     */
    protected function copyTermsForAggregations(\stdClass $from, \stdClass $to)
    {
        $typeIds = $this->getTermIdsByDomain($from, 'eventtype');
        $themeIds = $this->getTermIdsByDomain($from, 'theme');
        $facilityIds = $this->getTermIdsByDomain($from, 'facility');

        if (!empty($typeIds)) {
            $to->typeIds = $typeIds;
        }

        if (!empty($themeIds)) {
            $to->themeIds = $themeIds;
        }

        if (!empty($facilityIds)) {
            $to->facilityIds = $facilityIds;
        }
    }

    /**
     * @param \stdClass $object
     * @param string $domain
     * @return array
     */
    protected function getTermIdsByDomain(\stdClass $object, $domain)
    {
        // Don't use $this->getTerms() here as the resulting terms do not
        // contain the "domain" property.
        $terms = isset($object->terms) ? $object->terms : [];

        $filteredByDomain = array_filter(
            $terms,
            function ($term) use ($domain) {
                return isset($term->domain) && $term->domain == $domain && isset($term->id);
            }
        );

        $mappedToIds = array_map(
            function ($term) {
                return $term->id;
            },
            $filteredByDomain
        );

        $uniqueIds = array_unique($mappedToIds);

        $uniqueIdsWithConsecutiveKeys = array_values($uniqueIds);

        return $uniqueIdsWithConsecutiveKeys;
    }

    /**
     * @param \stdClass $from
     * @param \stdClass $to
     */
    protected function copyTypicalAgeRange(\stdClass $from, \stdClass $to)
    {
        if (isset($from->typicalAgeRange) && is_string($from->typicalAgeRange)) {
            $regexMatches = [];
            preg_match('/(\d*)-(\d*)/', $from->typicalAgeRange, $regexMatches);


            if (count($regexMatches) !== 3) {
                // The matches should always contain exactly 3 values:
                // 0: The delimiter (-)
                // 1: minAge as string (or empty string)
                // 2: maxAge as string (or empty string)
                return;
            }

            // Be sure to always do a strict comparison here!
            $minAge = ($regexMatches[1] !== '') ? (int) $regexMatches[1] : 0;
            $maxAge = ($regexMatches[2] !== '') ? (int) $regexMatches[2] : null;

            $to->typicalAgeRange = new \stdClass();
            $to->typicalAgeRange->gte = $minAge;

            if ($maxAge) {
                $to->typicalAgeRange->lte = $maxAge;
            }
        }
    }

    /**
     * @param \stdClass $from
     * @param \stdClass $to
     */
    protected function copyPriceInfo(\stdClass $from, \stdClass $to)
    {
        if (isset($from->priceInfo) && is_array($from->priceInfo)) {
            foreach ($from->priceInfo as $priceInfo) {
                if ($priceInfo->category === 'base') {
                    $to->price = $priceInfo->price;
                    break;
                }
            }
        }
    }

    /**
     * @param \stdClass $from
     * @param \stdClass $to
     */
    protected function copyAudienceType(\stdClass $from, \stdClass $to)
    {
        $audienceType = isset($from->audience->audienceType) ? (string) $from->audience->audienceType : 'everyone';
        $to->audienceType = $audienceType;
    }

    /**
     * @param \stdClass $from
     * @param \stdClass $to
     */
    protected function copyMediaObjectsCount(\stdClass $from, \stdClass $to)
    {
        $mediaObjectsCount = isset($from->mediaObject) ? count($from->mediaObject) : 0;
        $to->mediaObjectsCount = $mediaObjectsCount;
    }

    /**
     * @param \stdClass $from
     * @param \stdClass $to
     */
    protected function copyAddressAndGeoInformation(\stdClass $from, \stdClass $to)
    {
        if (isset($from->address->addressCountry)) {
            $to->addressCountry = $from->address->addressCountry;
        } else {
            $this->logMissingExpectedField('address.addressCountry');
        }

        if (isset($from->address->addressLocality)) {
            $to->addressLocality = $from->address->addressLocality;
        } else {
            $this->logMissingExpectedField('address.addressLocality');
        }

        if (isset($from->address->addressLocality)) {
            $to->postalCode = $from->address->postalCode;
        } else {
            $this->logMissingExpectedField('address.postalCode');
        }

        if (isset($from->address->streetAddress)) {
            $to->streetAddress = $from->address->streetAddress;
        } else {
            $this->logMissingExpectedField('address.streetAddress');
        }

        if (isset($from->geo)) {
            $to->geo = new \stdClass();
            $to->geo->type = 'Point';

            // Important! In GeoJSON, and therefore Elasticsearch, the correct coordinate order is longitude, latitude
            // (X, Y) within coordinate arrays. This differs from many Geospatial APIs (e.g., Google Maps) that
            // generally use the colloquial latitude, longitude (Y, X).
            // @see https://www.elastic.co/guide/en/elasticsearch/reference/current/geo-shape.html#input-structure
            $to->geo->coordinates = [
                $from->geo->longitude,
                $from->geo->latitude,
            ];

            // We need to duplicate the geo coordinates in an extra field to enable geo distance queries.
            // ElasticSearch has 2 formats for geo coordinates, one datatype indexed to facilitate geoshape queries,
            // and another datatype indexed to facilitate geo distance queries.
            $to->geo_point = [
                'lat' => $from->geo->latitude,
                'lon' => $from->geo->longitude,
            ];
        }
    }

    /**
     * @param OfferType $offerType
     * @param JsonDocument $jsonDocument
     * @return string[]
     */
    protected function getRegionIds(
        OfferType $offerType,
        JsonDocument $jsonDocument
    ) {
        $regionIds = $this->offerRegionService->getRegionIds(
            $offerType,
            $jsonDocument
        );

        if (empty($regionIds)) {
            return [];
        }

        return array_map(
            function (RegionId $regionId) {
                return $regionId->toNative();
            },
            $regionIds
        );
    }

    /**
     * @param \stdClass $from
     * @param \stdClass $to
     */
    protected function copyOrganizer(\stdClass $from, \stdClass $to)
    {
        if (!isset($from->organizer)) {
            return;
        }

        if (!isset($to->organizer)) {
            $to->organizer = new \stdClass();
        }

        $this->copyIdentifiers($from->organizer, $to->organizer, 'Organizer');

        $this->copyName($from->organizer, $to->organizer);

        $this->copyLabels($from->organizer, $to->organizer);
    }

    /**
     * @param \stdClass $from
     * @param \stdClass $to
     */
    protected function copyCreated(\stdClass $from, \stdClass $to)
    {
        if (!isset($from->created)) {
            $this->logMissingExpectedField('created');
            return;
        }

        $created = \DateTimeImmutable::createFromFormat(\DateTime::ATOM, $from->created);

        if (!$created) {
            $this->logger->error('Could not parse created as an ISO-8601 datetime.');
            return;
        }

        $to->created = $created->format(\DateTime::ATOM);
    }

    /**
     * @param \stdClass $from
     * @param \stdClass $to
     */
    protected function copyModified(\stdClass $from, \stdClass $to)
    {
        if (!isset($from->modified)) {
            return;
        }

        $modified = \DateTimeImmutable::createFromFormat(\DateTime::ATOM, $from->modified);

        if (!$modified) {
            $this->logger->error('Could not parse modified as an ISO-8601 datetime.');
            return;
        }

        $to->modified = $modified->format(\DateTime::ATOM);
    }

    /**
     * @param \stdClass $from
     * @param \stdClass $to
     */
    protected function copyCreator(\stdClass $from, \stdClass $to)
    {
        if (!isset($from->creator)) {
            $this->logMissingExpectedField('creator');
            return;
        }

        $to->creator = $from->creator;
    }

    /**
     * @param $fieldName
     */
    protected function logMissingExpectedField($fieldName)
    {
        $this->logger->warning("Missing expected field '{$fieldName}'.");
    }
}
