<?php

namespace CultuurNet\UDB3\Search\ElasticSearch\Offer;

use CultuurNet\UDB3\Search\ElasticSearch\LuceneQueryString;
use CultuurNet\UDB3\Search\Offer\OfferSearchParameters;
use CultuurNet\UDB3\Search\Region\RegionId;
use ValueObjects\Number\Natural;
use ValueObjects\StringLiteral\StringLiteral;

class ElasticSearchOfferQueryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_can_be_created_from_minimal_offer_search_parameters()
    {
        $searchParameters = (new OfferSearchParameters())
            ->withStart(new Natural(30))
            ->withLimit(new Natural(10));

        $expectedQueryArray = [
            'from' => 30,
            'size' => 10,
            'query' => [
                'match_all' => (object) [],
            ],
        ];

        $actualQueryArray = ElasticSearchOfferQuery::fromSearchParameters($searchParameters)
            ->toArray();

        $this->assertEquals($expectedQueryArray, $actualQueryArray);
    }

    /**
     * @test
     */
    public function it_can_be_created_with_a_query_string_query()
    {
        $searchParameters = (new OfferSearchParameters())
            ->withStart(new Natural(30))
            ->withLimit(new Natural(10))
            ->withQueryString(
                new LuceneQueryString('foo AND bar')
            );

        $expectedQueryArray = [
            'from' => 30,
            'size' => 10,
            'query' => [
                'bool' => [
                    'must' => [
                        [
                            'match_all' => (object) [],
                        ],
                        [
                            'query_string' => [
                                'query' => 'foo AND bar',
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
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $actualQueryArray = ElasticSearchOfferQuery::fromSearchParameters($searchParameters)
            ->toArray();

        $this->assertEquals($expectedQueryArray, $actualQueryArray);
    }

    /**
     * @test
     */
    public function it_can_be_created_with_a_geoshape_query()
    {
        $searchParameters = (new OfferSearchParameters())
            ->withStart(new Natural(30))
            ->withLimit(new Natural(10))
            ->withRegion(
                new RegionId('gem-leuven'),
                new StringLiteral('geoshapes'),
                new StringLiteral('region')
            );

        $expectedQueryArray = [
            'from' => 30,
            'size' => 10,
            'query' => [
                'bool' => [
                    'must' => [
                        [
                            'match_all' => (object) [],
                        ],
                    ],
                    'filter' => [
                        [
                            'geo_shape' => [
                                'geo' => [
                                    'indexed_shape' => [
                                        'index' => 'geoshapes',
                                        'type' => 'region',
                                        'id' => 'gem-leuven',
                                        'path' => 'location',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $actualQueryArray = ElasticSearchOfferQuery::fromSearchParameters($searchParameters)
            ->toArray();

        $this->assertEquals($expectedQueryArray, $actualQueryArray);
    }
}
