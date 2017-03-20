<?php

namespace CultuurNet\UDB3\Search\ElasticSearch\Offer;

use CultuurNet\UDB3\Label\ValueObjects\LabelName;
use CultuurNet\UDB3\Language;
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
                                    'labels_free_text',
                                    'terms_free_text.id',
                                    'terms_free_text.label',
                                    'performer_free_text.name',
                                    'addressLocality',
                                    'postalCode',
                                    'streetAddress',
                                    'location.id',
                                    'organizer.id',
                                    'name.nl',
                                    'description.nl',
                                    'location.name.nl',
                                    'organizer.name.nl',
                                    'name.fr',
                                    'description.fr',
                                    'location.name.fr',
                                    'organizer.name.fr',
                                    'name.en',
                                    'description.en',
                                    'location.name.en',
                                    'organizer.name.en',
                                    'name.de',
                                    'description.de',
                                    'location.name.de',
                                    'organizer.name.de',
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
    public function it_can_be_created_with_a_query_string_query_and_a_subset_of_text_languages()
    {
        $searchParameters = (new OfferSearchParameters())
            ->withStart(new Natural(30))
            ->withLimit(new Natural(10))
            ->withQueryString(
                new LuceneQueryString('foo AND bar')
            )
            ->withTextLanguages(
                new Language('nl'),
                new Language('fr')
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
                                    'labels_free_text',
                                    'terms_free_text.id',
                                    'terms_free_text.label',
                                    'performer_free_text.name',
                                    'addressLocality',
                                    'postalCode',
                                    'streetAddress',
                                    'location.id',
                                    'organizer.id',
                                    'name.nl',
                                    'description.nl',
                                    'location.name.nl',
                                    'organizer.name.nl',
                                    'name.fr',
                                    'description.fr',
                                    'location.name.fr',
                                    'organizer.name.fr',
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

    /**
     * @test
     */
    public function it_can_be_created_with_a_labels_query()
    {
        $searchParameters = (new OfferSearchParameters())
            ->withStart(new Natural(30))
            ->withLimit(new Natural(10))
            ->withLabels(
                new LabelName('foo'),
                new LabelName('bar')
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
                            'term' => [
                                'labels' => 'foo',
                            ],
                        ],
                        [
                            'term' => [
                                'labels' => 'bar',
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
    public function it_can_be_created_with_a_location_labels_query()
    {
        $searchParameters = (new OfferSearchParameters())
            ->withStart(new Natural(30))
            ->withLimit(new Natural(10))
            ->withLocationLabels(
                new LabelName('foo'),
                new LabelName('bar')
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
                            'term' => [
                                'location.labels' => 'foo',
                            ],
                        ],
                        [
                            'term' => [
                                'location.labels' => 'bar',
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
    public function it_can_be_created_with_an_organizer_labels_query()
    {
        $searchParameters = (new OfferSearchParameters())
            ->withStart(new Natural(30))
            ->withLimit(new Natural(10))
            ->withOrganizerLabels(
                new LabelName('foo'),
                new LabelName('bar')
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
                            'term' => [
                                'organizer.labels' => 'foo',
                            ],
                        ],
                        [
                            'term' => [
                                'organizer.labels' => 'bar',
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
