<?php

namespace CultuurNet\UDB3\Search\ElasticSearch\Offer;

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
        if (!isset($from->availableFrom) || !isset($from->availableTo)) {
            return;
        }

        if (isset($from->workflowStatus) && $from->workflowStatus == 'DRAFT') {
            $this->logger->warning('Found availableFrom but workflowStatus is DRAFT.');
        }

        // Convert to DateTimeImmutable to verify the format is correct.
        $availableFrom = \DateTimeImmutable::createFromFormat(\DateTime::ATOM, $from->availableFrom);
        $availableTo = \DateTimeImmutable::createFromFormat(\DateTime::ATOM, $from->availableTo);

        if (!$availableFrom) {
            $this->logger->error('Could not parse availableFrom as an ISO-8601 datetime.');
            return;
        }

        if (!$availableTo) {
            $this->logger->error('Could not parse availableTo as an ISO-8601 datetime.');
            return;
        }

        $to->availableRange = new \stdClass();
        $to->availableRange->gte = $availableFrom->format(\DateTime::ATOM);
        $to->availableRange->lte = $availableTo->format(\DateTime::ATOM);
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

        if (isset($from->openingHours)) {
            // @todo Implement with a different method in III-2063.
            return;
        }

        $from = $this->polyFillJsonLdSubEvents($from);

        $dateRange = [];

        switch ($from->calendarType) {
            case 'single':
            case 'periodic':
            case 'multiple':
                // Index each subEvent as a separate date range.
                if (!isset($from->subEvent)) {
                    $this->logMissingExpectedField('subEvent');
                    return;
                }

                foreach ($from->subEvent as $index => $subEvent) {
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
                    $dateRange[] = $range;
                }
                break;

            case 'permanent':
                // Index a single range without any bounds.
                $dateRange[] = new \stdClass();
                break;
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
        if (isset($from->subEvent)) {
            return $from;
        }

        $from = clone $from;

        if ($from->calendarType == 'single' || $from->calendarType == 'periodic') {
            if (!isset($from->startDate)) {
                $this->logMissingExpectedField('startDate');
                return $from;
            }

            if (!isset($from->endDate)) {
                $this->logMissingExpectedField('endDate');
                return $from;
            }

            $from->subEvent = [
                (object) [
                    '@type' => 'Event',
                    'startDate' => $from->startDate,
                    'endDate' => $from->endDate,
                ],
            ];
        }

        return $from;
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
    protected function copyName(\stdClass $from, \stdClass $to)
    {
        $to->name = new \stdClass();

        if (isset($from->name->nl)) {
            $to->name->nl = $from->name->nl;
        } else {
            $this->logMissingExpectedField('name.nl');
        }

        // Only copy over the languages that we know how to analyze.
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

        foreach ($translatableFields as $translatableField) {
            if (!isset($from->{$translatableField})) {
                continue;
            }

            $languages = array_merge(
                $languages,
                array_keys(
                    get_object_vars($from->{$translatableField})
                )
            );
        }

        // Make sure to use array_values(), because array_unique() keeps the
        // original keys so this can result in gaps. This is bad because those
        // gaps result in the array being converted to an object when encoding
        // as JSON.
        $languages = array_values(array_unique($languages));

        $to->languages = $languages;
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
        if (isset($from->audience->audienceType) &&
            is_string($from->audience->audienceType)) {
            $to->audienceType = $from->audience->audienceType;
        }
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

        $to->organizer->name = new \stdClass();
        $to->organizer->name->nl = $from->organizer->name;

        $this->copyLabels($from->organizer, $to->organizer);
    }

    /**
     * @param $fieldName
     */
    protected function logMissingExpectedField($fieldName)
    {
        $this->logger->warning("Missing expected field '{$fieldName}'.");
    }
}
