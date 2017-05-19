<?php

namespace CultuurNet\UDB3\Search\ElasticSearch\Offer;

use CultuurNet\UDB3\Address\PostalCode;
use CultuurNet\UDB3\Label\ValueObjects\LabelName;
use CultuurNet\UDB3\Language;
use CultuurNet\UDB3\PriceInfo\Price;
use CultuurNet\UDB3\Search\Creator;
use CultuurNet\UDB3\Search\ElasticSearch\AbstractElasticSearchQueryBuilder;
use CultuurNet\UDB3\Search\GeoDistanceParameters;
use CultuurNet\UDB3\Search\Offer\AudienceType;
use CultuurNet\UDB3\Search\Offer\CalendarType;
use CultuurNet\UDB3\Search\Offer\Cdbid;
use CultuurNet\UDB3\Search\Offer\FacetName;
use CultuurNet\UDB3\Search\Offer\OfferQueryBuilderInterface;
use CultuurNet\UDB3\Search\Offer\SortBy;
use CultuurNet\UDB3\Search\Offer\TermId;
use CultuurNet\UDB3\Search\Offer\TermLabel;
use CultuurNet\UDB3\Search\Offer\WorkflowStatus;
use CultuurNet\UDB3\Search\Region\RegionId;
use CultuurNet\UDB3\Search\SortOrder;
use ONGR\ElasticsearchDSL\Aggregation\Bucketing\TermsAggregation;
use ONGR\ElasticsearchDSL\Query\Compound\BoolQuery;
use ONGR\ElasticsearchDSL\Query\Geo\GeoDistanceQuery;
use ONGR\ElasticsearchDSL\Query\Geo\GeoShapeQuery;
use ONGR\ElasticsearchDSL\Sort\FieldSort;
use ValueObjects\Geography\Country;
use ValueObjects\Number\Natural;
use ValueObjects\StringLiteral\StringLiteral;

class ElasticSearchOfferQueryBuilder extends AbstractElasticSearchQueryBuilder implements
    OfferQueryBuilderInterface
{
    /**
     * @inheritdoc
     */
    protected function getPredefinedQueryStringFields(Language ...$languages)
    {
        $fields = [
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

        foreach ($languages as $language) {
            $langCode = $language->getCode();
            $fields = array_merge(
                $fields,
                [
                    "name.{$langCode}",
                    "description.{$langCode}",
                    "location.name.{$langCode}",
                    "organizer.name.{$langCode}",
                ]
            );
        }

        return $fields;
    }

    /**
     * @inheritdoc
     */
    public function withCdbIdFilter(Cdbid $cdbid)
    {
        return $this->withMatchQuery('id', $cdbid->toNative());
    }

    /**
     * @inheritdoc
     */
    public function withLocationCdbIdFilter(Cdbid $locationCdbid)
    {
        return $this->withMatchQuery('location.id', $locationCdbid->toNative());
    }

    /**
     * @inheritdoc
     */
    public function withOrganizerCdbIdFilter(Cdbid $organizerCdbId)
    {
        return $this->withMatchQuery('organizer.id', $organizerCdbId);
    }

    /**
     * @inheritdoc
     */
    public function withLanguageFilter(Language $language)
    {
        return $this->withMatchQuery('languages', $language->getCode());
    }

    /**
     * @inheritdoc
     */
    public function withAvailableRangeFilter(
        \DateTimeImmutable $from = null,
        \DateTimeImmutable $to = null
    ) {
        $this->guardDateRange('available', $from, $to);
        return $this->withDateRangeQuery('availableRange', $from, $to);
    }

    /**
     * @inheritdoc
     */
    public function withWorkflowStatusFilter(WorkflowStatus $workflowStatus)
    {
        return $this->withMatchQuery('workflowStatus', $workflowStatus->toNative());
    }

    /**
     * @inheritdoc
     */
    public function withCreatedRangeFilter(
        \DateTimeImmutable $from = null,
        \DateTimeImmutable $to = null
    ) {
        $this->guardDateRange('created', $from, $to);
        return $this->withDateRangeQuery('created', $from, $to);
    }

    /**
     * @inheritdoc
     */
    public function withModifiedRangeFilter(
        \DateTimeImmutable $from = null,
        \DateTimeImmutable $to = null
    ) {
        $this->guardDateRange('modified', $from, $to);
        return $this->withDateRangeQuery('modified', $from, $to);
    }

    /**
     * @inheritdoc
     */
    public function withCreatorFilter(Creator $creator)
    {
        return $this->withMatchQuery('creator', $creator->toNative());
    }

    /**
     * @inheritdoc
     */
    public function withDateRangeFilter(
        \DateTimeImmutable $from = null,
        \DateTimeImmutable $to = null
    ) {
        $this->guardDateRange('date', $from, $to);
        return $this->withDateRangeQuery('dateRange', $from, $to);
    }

    /**
     * @inheritdoc
     */
    public function withCalendarTypeFilter(CalendarType $calendarType)
    {
        return $this->withMatchQuery('calendarType', $calendarType->toNative());
    }

    /**
     * @inheritdoc
     */
    public function withPostalCodeFilter(PostalCode $postalCode)
    {
        return $this->withMatchQuery('postalCode', $postalCode->toNative());
    }

    /**
     * @inheritdoc
     */
    public function withAddressCountryFilter(Country $country)
    {
        return $this->withMatchQuery('addressCountry', $country->getCode());
    }

    /**
     * @inheritdoc
     */
    public function withRegionFilter(
        StringLiteral $regionIndexName,
        StringLiteral $regionDocumentType,
        RegionId $regionId
    ) {
        $geoShapeQuery = new GeoShapeQuery();

        $geoShapeQuery->addPreIndexedShape(
            'geo',
            $regionId->toNative(),
            $regionDocumentType->toNative(),
            $regionIndexName->toNative(),
            'location'
        );

        $c = $this->getClone();
        $c->boolQuery->add($geoShapeQuery, BoolQuery::FILTER);
        return $c;
    }

    /**
     * @inheritdoc
     */
    public function withGeoDistanceFilter(GeoDistanceParameters $geoDistanceParameters)
    {
        $geoDistanceQuery = new GeoDistanceQuery(
            'geo_point',
            $geoDistanceParameters->getMaximumDistance()->toNative(),
            (object) [
                'lat' => $geoDistanceParameters->getCoordinates()->getLatitude()->toDouble(),
                'lon' => $geoDistanceParameters->getCoordinates()->getLongitude()->toDouble(),
            ]
        );

        $c = $this->getClone();
        $c->boolQuery->add($geoDistanceQuery, BoolQuery::FILTER);
        return $c;
    }

    /**
     * @inheritdoc
     */
    public function withAudienceTypeFilter(AudienceType $audienceType)
    {
        return $this->withMatchQuery('audienceType', $audienceType);
    }

    /**
     * @inheritdoc
     */
    public function withAgeRangeFilter(Natural $minimum = null, Natural $maximum = null)
    {
        $this->guardNaturalIntegerRange('age', $minimum, $maximum);

        $minimum = is_null($minimum) ? null : $minimum->toNative();
        $maximum = is_null($maximum) ? null : $maximum->toNative();

        return $this->withRangeQuery('typicalAgeRange', $minimum, $maximum);
    }

    /**
     * @inheritdoc
     */
    public function withPriceRangeFilter(Price $minimum = null, Price $maximum = null)
    {
        $this->guardNaturalIntegerRange('price', $minimum, $maximum);

        $minimum = is_null($minimum) ? null : $minimum->toFloat();
        $maximum = is_null($maximum) ? null : $maximum->toFloat();

        return $this->withRangeQuery('price', $minimum, $maximum);
    }

    /**
     * @inheritdoc
     */
    public function withMediaObjectsFilter($include)
    {
        $min = $include ? 1 : null;
        $max = $include ? null : 0;

        return $this->withRangeQuery('mediaObjectsCount', $min, $max);
    }

    /**
     * @inheritdoc
     */
    public function withUiTPASFilter($include)
    {
        $uitpasQuery = 'organizer.labels:(UiTPAS* OR Paspartoe)';

        if (!$include) {
            $uitpasQuery = "!({$uitpasQuery})";
        }

        return $this->withQueryStringQuery($uitpasQuery, [], BoolQuery::FILTER);
    }

    /**
     * @inheritdoc
     */
    public function withTermIdFilter(TermId $termId)
    {
        return $this->withMatchQuery('terms.id', $termId->toNative());
    }

    /**
     * @inheritdoc
     */
    public function withTermLabelFilter(TermLabel $termLabel)
    {
        return $this->withMatchQuery('terms.label', $termLabel->toNative());
    }

    /**
     * @inheritdoc
     */
    public function withLocationTermIdFilter(TermId $locationTermId)
    {
        return $this->withMatchQuery('location.terms.id', $locationTermId->toNative());
    }

    /**
     * @inheritdoc
     */
    public function withLocationTermLabelFilter(TermLabel $locationTermLabel)
    {
        return $this->withMatchQuery('location.terms.label', $locationTermLabel->toNative());
    }

    /**
     * @inheritdoc
     */
    public function withLabelFilter(LabelName $label)
    {
        return $this->withMatchQuery('labels', $label->toNative());
    }

    /**
     * @inheritdoc
     */
    public function withLocationLabelFilter(LabelName $locationLabel)
    {
        return $this->withMatchQuery('location.labels', $locationLabel->toNative());
    }

    /**
     * @inheritdoc
     */
    public function withOrganizerLabelFilter(LabelName $organizerLabel)
    {
        return $this->withMatchQuery('organizer.labels', $organizerLabel->toNative());
    }

    /**
     * @inheritdoc
     */
    public function withFacet(FacetName $facetName)
    {
        $facetName = $facetName->toNative();

        $facetFields = [
            FacetName::REGIONS()->toNative() => 'regions.keyword',
            FacetName::TYPES()->toNative() => 'typeIds',
            FacetName::THEMES()->toNative() => 'themeIds',
            FacetName::FACILITIES()->toNative() => 'facilityIds',
        ];

        if (!isset($facetFields[$facetName])) {
            return $this;
        }

        $facetField = $facetFields[$facetName];
        $aggregation = new TermsAggregation($facetName, $facetField);

        $c = $this->getClone();
        $c->search->addAggregation($aggregation);
        return $c;
    }

    /**
     * @inheritdoc
     */
    public function withSort(SortBy $sortBy, SortOrder $sortOrder)
    {
        $sortBy = $sortBy->toNative();
        $sortOrder = $sortOrder->toNative();

        $sortByFields = [
            SortBy::AVAILABLE_TO()->toNative() => 'availableTo',
            SortBy::SCORE()->toNative() => '_score',
        ];

        if (!isset($sortByFields[$sortBy])) {
            return $this;
        }

        $sortByField = $sortByFields[$sortBy];
        $sort = new FieldSort($sortByField, $sortOrder);

        $c = $this->getClone();
        $c->search->addSort($sort);
        return $c;
    }
}
