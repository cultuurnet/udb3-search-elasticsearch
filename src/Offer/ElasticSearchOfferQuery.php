<?php

namespace CultuurNet\UDB3\Search\ElasticSearch\Offer;

use CultuurNet\UDB3\Label\ValueObjects\LabelName;
use CultuurNet\UDB3\Search\Offer\FacetName;
use CultuurNet\UDB3\Search\Offer\OfferSearchParameters;
use CultuurNet\UDB3\Search\Offer\SortBy;
use CultuurNet\UDB3\Search\Offer\Sorting;
use ONGR\ElasticsearchDSL\Aggregation\Bucketing\TermsAggregation;
use ONGR\ElasticsearchDSL\Query\Compound\BoolQuery;
use ONGR\ElasticsearchDSL\Query\FullText\MatchQuery;
use ONGR\ElasticsearchDSL\Query\FullText\QueryStringQuery;
use ONGR\ElasticsearchDSL\Query\Geo\GeoDistanceQuery;
use ONGR\ElasticsearchDSL\Query\Geo\GeoShapeQuery;
use ONGR\ElasticsearchDSL\Query\MatchAllQuery;
use ONGR\ElasticsearchDSL\Query\TermLevel\RangeQuery;
use ONGR\ElasticsearchDSL\Query\TermLevel\TermQuery;
use ONGR\ElasticsearchDSL\Search;
use ONGR\ElasticsearchDSL\Sort\FieldSort;

class ElasticSearchOfferQuery
{
    /**
     * @var array
     */
    private $query;

    /**
     * @param array $query
     */
    private function __construct(array $query)
    {
        $this->query = $query;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return $this->query;
    }

    /**
     * @param OfferSearchParameters $searchParameters
     * @return ElasticSearchOfferQuery
     */
    public static function fromSearchParameters(
        OfferSearchParameters $searchParameters
    ) {
        $search = new Search();
        $search->setFrom($searchParameters->getStart()->toNative());
        $search->setSize($searchParameters->getLimit()->toNative());

        $boolQuery = new BoolQuery();

        $matchAllQuery = new MatchAllQuery();
        $boolQuery->add($matchAllQuery, BoolQuery::MUST);

        if ($searchParameters->hasQueryString()) {
            $freeTextFields = [
                'id',
                'labels_free_text',
                'terms_free_text.id',
                'terms_free_text.label',
                'performer_free_text.name',
                'addressLocality',
                'postalCode',
                'streetAddress',
                'location.id',
                'organizer.id',
            ];

            foreach ($searchParameters->getTextLanguages() as $textLanguage) {
                $langCode = $textLanguage->getCode();
                $freeTextFields = array_merge(
                    $freeTextFields,
                    [
                        "name.{$langCode}",
                        "description.{$langCode}",
                        "location.name.{$langCode}",
                        "organizer.name.{$langCode}",
                    ]
                );
            }

            $queryStringQuery = new QueryStringQuery(
                $searchParameters->getQueryString()->toNative(),
                ['fields' => $freeTextFields]
            );

            $boolQuery->add($queryStringQuery);
        }

        if ($searchParameters->hasLanguages()) {
            // Use separate term queries instead of a single terms query, because
            // a combined terms query uses OR as operator instead of AND.
            foreach ($searchParameters->getLanguages() as $language) {
                $matchQuery = new MatchQuery('languages', $language->getCode());
                $boolQuery->add($matchQuery, BoolQuery::FILTER);
            }
        }

        if ($searchParameters->hasCdbid()) {
            $cdbidMatchQuery = new MatchQuery(
                'id',
                $searchParameters->getCdbid()->toNative()
            );
            $boolQuery->add($cdbidMatchQuery, BoolQuery::FILTER);
        }

        if ($searchParameters->hasLocationCdbid()) {
            $cdbidMatchQuery = new MatchQuery(
                'location.id',
                $searchParameters->getLocationCdbid()->toNative()
            );
            $boolQuery->add($cdbidMatchQuery, BoolQuery::FILTER);
        }

        if ($searchParameters->hasOrganizerCdbid()) {
            $cdbidMatchQuery = new MatchQuery(
                'organizer.id',
                $searchParameters->getOrganizerCdbid()->toNative()
            );
            $boolQuery->add($cdbidMatchQuery, BoolQuery::FILTER);
        }

        if ($searchParameters->hasCalendarType()) {
            $calendarTypeQuery = new MatchQuery(
                'calendarType',
                $searchParameters->getCalendarType()->toNative()
            );
            $boolQuery->add($calendarTypeQuery, BoolQuery::FILTER);
        }

        if ($searchParameters->hasDateFrom() || $searchParameters->hasDateTo()) {
            $parameters = [];

            if ($searchParameters->hasDateFrom()) {
                $parameters[RangeQuery::GTE] = $searchParameters->getDateFrom()->format(\DateTime::ATOM);
            }

            if ($searchParameters->hasDateTo()) {
                $parameters[RangeQuery::LTE] = $searchParameters->getDateTo()->format(\DateTime::ATOM);
            }

            $dateRangeQuery = new RangeQuery('dateRange', $parameters);
            $boolQuery->add($dateRangeQuery, BoolQuery::FILTER);
        }

        if ($searchParameters->hasWorkflowStatus()) {
            $matchQuery = new MatchQuery('workflowStatus', $searchParameters->getWorkflowStatus()->toNative());
            $boolQuery->add($matchQuery, BoolQuery::FILTER);
        }

        if ($searchParameters->hasAvailableFrom() || $searchParameters->hasAvailableTo()) {
            $parameters = [];

            if ($searchParameters->hasAvailableFrom()) {
                $parameters[RangeQuery::GTE] = $searchParameters->getAvailableFrom()->format(\DateTime::ATOM);
            }

            if ($searchParameters->hasAvailableTo()) {
                $parameters[RangeQuery::LTE] = $searchParameters->getAvailableTo()->format(\DateTime::ATOM);
            }

            $availableRangeQuery = new RangeQuery('availableRange', $parameters);
            $boolQuery->add($availableRangeQuery, BoolQuery::FILTER);
        }

        if (!is_null($searchParameters->getRegionId()) &&
            !is_null($searchParameters->getRegionIndexName()) &&
            !is_null($searchParameters->getRegionDocumentType())) {
            $geoShapeQuery = new GeoShapeQuery();

            $field = 'geo';
            $id = $searchParameters->getRegionId()->toNative();
            $type = $searchParameters->getRegionDocumentType()->toNative();
            $index = $searchParameters->getRegionIndexName()->toNative();
            $path = 'location';

            $geoShapeQuery->addPreIndexedShape(
                $field,
                $id,
                $type,
                $index,
                $path
            );

            $boolQuery->add($geoShapeQuery, BoolQuery::FILTER);
        }

        if ($searchParameters->hasGeoDistanceParameters()) {
            $geoDistanceParameters = $searchParameters->getGeoDistanceParameters();

            $geoDistanceQuery = new GeoDistanceQuery(
                'geo_point',
                $geoDistanceParameters->getMaximumDistance()->toNative(),
                (object) [
                    'lat' => $geoDistanceParameters->getCoordinates()->getLatitude()->toDouble(),
                    'lon' => $geoDistanceParameters->getCoordinates()->getLongitude()->toDouble(),
                ]
            );

            $boolQuery->add($geoDistanceQuery, BoolQuery::FILTER);
        }

        if ($searchParameters->hasPostalCode()) {
            $matchQuery = new MatchQuery('postalCode', $searchParameters->getPostalCode()->toNative());
            $boolQuery->add($matchQuery, BoolQuery::FILTER);
        }

        if ($searchParameters->hasAddressCountry()) {
            $addressCountryQuery = new MatchQuery(
                'addressCountry',
                $searchParameters->getAddressCountry()->getCode()->toNative()
            );

            $boolQuery->add($addressCountryQuery, BoolQuery::FILTER);
        }

        if ($searchParameters->hasAgeRange()) {
            $parameters = [];

            if ($searchParameters->hasMinimumAge()) {
                $parameters[RangeQuery::GTE] = $searchParameters->getMinimumAge()->toNative();
            }

            if ($searchParameters->hasMaximumAge()) {
                $parameters[RangeQuery::LTE] = $searchParameters->getMaximumAge()->toNative();
            }

            $rangeQuery = new RangeQuery('typicalAgeRange', $parameters);
            $boolQuery->add($rangeQuery, BoolQuery::FILTER);
        }

        // Prevent unrealistic queries for example:
        // /offers/?price=20minPrice=5&maxPrice=10
        // In this example only price parameter is used, because otherwise
        // no results would be returned.
        if ($searchParameters->hasPrice()) {
            $priceQuery = new MatchQuery('price', $searchParameters->getPrice()->toFloat());
            $boolQuery->add($priceQuery, BoolQuery::FILTER);
        } elseif ($searchParameters->hasPriceRange()) {
            $parameters = [];

            if ($searchParameters->hasMinimumPrice()) {
                $parameters[RangeQuery::GTE] = $searchParameters->getMinimumPrice()->toFloat();
            }

            if ($searchParameters->hasMaximumPrice()) {
                $parameters[RangeQuery::LTE] = $searchParameters->getMaximumPrice()->toFloat();
            }

            $rangeQuery = new RangeQuery('price', $parameters);
            $boolQuery->add($rangeQuery, BoolQuery::FILTER);
        }

        if ($searchParameters->hasAudienceType()) {
            $audienceTypeQuery = new MatchQuery(
                'audienceType',
                $searchParameters->getAudienceType()->toNative()
            );
            $boolQuery->add($audienceTypeQuery, BoolQuery::FILTER);
        }

        if ($searchParameters->hasMediaObjectsToggle()) {
            if ($searchParameters->getMediaObjectsToggle() === true) {
                // Return only documents with one or more media objects.
                $parameters = [RangeQuery::GTE => 1];
            } else {
                // Return only documents with zero media objects.
                $parameters = [RangeQuery::LTE => 0];
            }

            $mediaObjectsCountQuery = new RangeQuery('mediaObjectsCount', $parameters);
            $boolQuery->add($mediaObjectsCountQuery, BoolQuery::FILTER);
        }

        if ($searchParameters->hasUitpasToggle()) {
            $uitpasQuery = 'organizer.labels:(UiTPAS* OR Paspartoe)';

            if ($searchParameters->getUitpasToggle() == true) {
                $queryStringQuery = new QueryStringQuery($uitpasQuery);
                $boolQuery->add($queryStringQuery, BoolQuery::FILTER);
            } elseif ($searchParameters->getUitpasToggle() == false) {
                $queryStringQuery = new QueryStringQuery('!(' . $uitpasQuery . ')');
                $boolQuery->add($queryStringQuery, BoolQuery::FILTER);
            }
        }

        self::addTermIdsQuery($boolQuery, 'terms.id', $searchParameters->getTermIds());
        self::addTermIdsQuery($boolQuery, 'location.terms.id', $searchParameters->getLocationTermIds());

        self::addTermLabelsQuery($boolQuery, 'terms.label', $searchParameters->getTermLabels());
        self::addTermLabelsQuery($boolQuery, 'location.terms.label', $searchParameters->getLocationTermLabels());

        self::addLabelsQuery($boolQuery, 'labels', $searchParameters->getLabels());
        self::addLabelsQuery($boolQuery, 'location.labels', $searchParameters->getLocationLabels());
        self::addLabelsQuery($boolQuery, 'organizer.labels', $searchParameters->getOrganizerLabels());

        if ($searchParameters->hasCreator()) {
            $creatorQuery = new TermQuery(
                'creator',
                $searchParameters->getCreator()->toNative()
            );
            $boolQuery->add($creatorQuery, BoolQuery::FILTER);
        }

        $search->addQuery($boolQuery);

        self::addFacets($search, $searchParameters->getFacets());
        self::addSorting($search, $searchParameters->getSorting());

        return new ElasticSearchOfferQuery($search->toArray());
    }

    /**
     * @param BoolQuery $boolQuery
     * @param string $field
     * @param array $termIds
     */
    private static function addTermIdsQuery(BoolQuery $boolQuery, $field, array $termIds)
    {
        // Use separate term queries instead of a single terms query, because
        // a combined terms query uses OR as operator instead of AND.
        foreach ($termIds as $termId) {
            $matchQuery = new MatchQuery($field, $termId->toNative());
            $boolQuery->add($matchQuery, BoolQuery::FILTER);
        }
    }

    /**
     * @param BoolQuery $boolQuery
     * @param string $field
     * @param array $termLabels
     */
    private static function addTermLabelsQuery(BoolQuery $boolQuery, $field, array $termLabels)
    {
        // Use separate term queries instead of a single terms query, because
        // a combined terms query uses OR as operator instead of AND.
        foreach ($termLabels as $termLabel) {
            $matchQuery = new MatchQuery($field, $termLabel->toNative());
            $boolQuery->add($matchQuery, BoolQuery::FILTER);
        }
    }

    /**
     * @param BoolQuery $boolQuery
     * @param string $field
     * @param LabelName[] $labelNames
     */
    private static function addLabelsQuery(BoolQuery $boolQuery, $field, array $labelNames)
    {
        // Use separate term queries instead of a single terms query, because
        // a combined terms query uses OR as operator instead of AND.
        foreach ($labelNames as $labelName) {
            $label = $labelName->toNative();
            $matchQuery = new MatchQuery($field, $label);
            $boolQuery->add($matchQuery, BoolQuery::FILTER);
        }
    }

    /**
     * @param Search $search
     * @param FacetName[] $facetNames
     */
    private static function addFacets(Search $search, array $facetNames)
    {
        $facetNamesAsStrings = array_map(
            function (FacetName $facetName) {
                return $facetName->getValue();
            },
            $facetNames
        );

        $facetFields = [
            FacetName::REGIONS()->toNative() => 'regions.keyword',
            FacetName::TYPES()->toNative() => 'typeIds',
            FacetName::THEMES()->toNative() => 'themeIds',
            FacetName::FACILITIES()->toNative() => 'facilityIds',
        ];

        foreach ($facetFields as $facetName => $field) {
            if (in_array($facetName, $facetNamesAsStrings)) {
                $aggregation = new TermsAggregation($facetName, $field);
                $search->addAggregation($aggregation);
            }
        }
    }

    /**
     * @param Search $search
     * @param Sorting[] $sortingOptions
     */
    private static function addSorting(Search $search, array $sortingOptions)
    {
        $sortByFields = [
            SortBy::AVAILABLE_TO()->toNative() => 'availableTo',
            SortBy::SCORE()->toNative() => '_score',
        ];

        foreach ($sortingOptions as $sortingOption) {
            /* @var Sorting $sortingOptions */
            $sortByAsString = $sortingOption->getSortBy()->toNative();

            if (!isset($sortByFields[$sortByAsString])) {
                // Skip fields that are not mapped to ElasticSearch fields.
                continue;
            }

            $fieldSort = new FieldSort(
                $sortByFields[$sortByAsString],
                $sortingOption->getSortOrder()->toNative()
            );

            $search->addSort($fieldSort);
        }
    }
}
