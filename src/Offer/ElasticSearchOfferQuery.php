<?php

namespace CultuurNet\UDB3\Search\ElasticSearch\Offer;

use CultuurNet\UDB3\Label\ValueObjects\LabelName;
use CultuurNet\UDB3\Search\Offer\OfferSearchParameters;
use ONGR\ElasticsearchDSL\Query\Compound\BoolQuery;
use ONGR\ElasticsearchDSL\Query\FullText\QueryStringQuery;
use ONGR\ElasticsearchDSL\Query\Geo\GeoShapeQuery;
use ONGR\ElasticsearchDSL\Query\MatchAllQuery;
use ONGR\ElasticsearchDSL\Query\TermLevel\TermQuery;
use ONGR\ElasticsearchDSL\Query\TermLevel\TermsQuery;
use ONGR\ElasticsearchDSL\Search;

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
        $boolQuery = new BoolQuery();

        $matchAllQuery = new MatchAllQuery();
        $boolQuery->add($matchAllQuery, BoolQuery::MUST);

        if ($searchParameters->hasQueryString()) {
            $queryStringQuery = new QueryStringQuery(
                $searchParameters->getQueryString()->toNative(),
                [
                    'fields' => [
                        'id',
                        'name.nl',
                        'description.nl',
                        'labels_free_text',
                        'terms_free_text.id',
                        'terms_free_text.label',
                        'performer_free_text.name',
                        'addressLocality',
                        'postalCode',
                        'streetAddress',
                        'location.id',
                        'location.name.nl',
                        'location.labels_free_text',
                        'organizer.id',
                        'organizer.name.nl',
                        'organizer.labels_free_text',
                    ],
                ]
            );

            $boolQuery->add($queryStringQuery);
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

        self::addLabelsQuery($boolQuery, 'labels', $searchParameters->getLabels());
        self::addLabelsQuery($boolQuery, 'location.labels', $searchParameters->getLocationLabels());
        self::addLabelsQuery($boolQuery, 'organizer.labels', $searchParameters->getOrganizerLabels());

        $search = new Search();
        $search->setFrom($searchParameters->getStart()->toNative());
        $search->setSize($searchParameters->getLimit()->toNative());
        $search->addQuery($boolQuery);

        return new ElasticSearchOfferQuery($search->toArray());
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
            $termQuery = new TermQuery($field, $label);
            $boolQuery->add($termQuery, BoolQuery::FILTER);
        }
    }
}
