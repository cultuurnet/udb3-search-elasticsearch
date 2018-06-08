<?php

namespace CultuurNet\UDB3\Search\ElasticSearch\Organizer;

use CultuurNet\UDB3\Address\PostalCode;
use CultuurNet\UDB3\Search\ElasticSearch\LuceneQueryString;
use ValueObjects\Number\Natural;
use ValueObjects\StringLiteral\StringLiteral;
use ValueObjects\Web\Url;

class ElasticSearchOrganizerQueryBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_should_build_a_query_with_pagination_parameters()
    {
        $builder = (new ElasticSearchOrganizerQueryBuilder())
            ->withStart(new Natural(30))
            ->withLimit(new Natural(10));

        $expectedQueryArray = [
            'from' => 30,
            'size' => 10,
            'query' => [
                'match_all' => (object) [],
            ],
        ];

        $actualQueryArray = $builder->build()->toArray();

        $this->assertEquals($expectedQueryArray, $actualQueryArray);
    }

    /**
     * @test
     */
    public function it_should_build_a_query_with_an_advanced_query()
    {
        $builder = (new ElasticSearchOrganizerQueryBuilder())
            ->withStart(new Natural(30))
            ->withLimit(new Natural(10))
            ->withAdvancedQuery(
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
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $actualQueryArray = $builder->build()->toArray();

        $this->assertEquals($expectedQueryArray, $actualQueryArray);
    }

    /**
     * @test
     */
    public function it_should_build_a_query_with_a_free_text_query()
    {
        $builder = (new ElasticSearchOrganizerQueryBuilder())
            ->withStart(new Natural(30))
            ->withLimit(new Natural(10))
            ->withTextQuery(
                new StringLiteral('foo bar baz')
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
                            'multi_match' => [
                                'type' => 'cross_fields',
                                'query' => 'foo bar baz',
                                'fields' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $actualQueryArray = $builder->build()->toArray();

        $this->assertEquals($expectedQueryArray, $actualQueryArray);
    }

    /**
     * @test
     */
    public function it_should_build_a_query_with_an_autocomplete_filter()
    {
        $builder = (new ElasticSearchOrganizerQueryBuilder())
            ->withAutoCompleteFilter(new StringLiteral('Collectief Cursief'));

        $expectedQueryArray = [
            'from' => 0,
            'size' => 30,
            'query' => [
                'bool' => [
                    'must' => [
                        [
                            'match_all' => (object) [],
                        ],
                    ],
                    'filter' => [
                        [
                            'match_phrase' => [
                                'name.nl.autocomplete' => [
                                    'query' => 'Collectief Cursief',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $actualQueryArray = $builder->build()->toArray();

        $this->assertEquals($expectedQueryArray, $actualQueryArray);
    }

    /**
     * @test
     */
    public function it_should_build_a_query_with_a_website_filter()
    {
        $builder = (new ElasticSearchOrganizerQueryBuilder())
            ->withWebsiteFilter(Url::fromNative('http://foo.bar'));

        $expectedQueryArray = [
            'from' => 0,
            'size' => 30,
            'query' => [
                'bool' => [
                    'must' => [
                        [
                            'match_all' => (object) [],
                        ],
                    ],
                    'filter' => [
                        [
                            'match' => [
                                'url' => [
                                    'query' => 'http://foo.bar',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $actualQueryArray = $builder->build()->toArray();

        $this->assertEquals($expectedQueryArray, $actualQueryArray);
    }

    /**
     * @test
     */
    public function it_should_build_a_query_with_multiple_filters()
    {
        /* @var ElasticSearchOrganizerQueryBuilder $builder */
        $builder = (new ElasticSearchOrganizerQueryBuilder())
            ->withStart(new Natural(30))
            ->withLimit(new Natural(10))
            ->withAutoCompleteFilter(new StringLiteral('foo'))
            ->withWebsiteFilter(Url::fromNative('http://foo.bar'));

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
                            'match_phrase' => [
                                'name.nl.autocomplete' => [
                                    'query' => 'foo',
                                ],
                            ],
                        ],
                        [
                            'match' => [
                                'url' => [
                                    'query' => 'http://foo.bar',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $actualQueryArray = $builder->build()->toArray();

        $this->assertEquals($expectedQueryArray, $actualQueryArray);
    }

    /**
     * @test
     */
    public function it_should_build_a_query_with_a_postal_code_filter()
    {
        /* @var ElasticSearchOrganizerQueryBuilder $builder */
        $builder = (new ElasticSearchOrganizerQueryBuilder())
            ->withStart(new Natural(30))
            ->withLimit(new Natural(10))
            ->withPostalCodeFilter(new PostalCode("3000"));

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
                            'bool' => [
                                'should' => [
                                    [
                                        'match' => [
                                            'address.nl.postalCode' => [
                                                'query' => '3000',
                                            ],
                                        ],
                                    ],
                                    [
                                        'match' => [
                                            'address.fr.postalCode' => [
                                                'query' => '3000',
                                            ],
                                        ],
                                    ],
                                    [
                                        'match' => [
                                            'address.de.postalCode' => [
                                                'query' => '3000',
                                            ],
                                        ],
                                    ],
                                    [
                                        'match' => [
                                            'address.en.postalCode' => [
                                                'query' => '3000',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $actualQueryArray = $builder->build()->toArray();

        $this->assertEquals($expectedQueryArray, $actualQueryArray);
    }

    /**
     * @test
     */
    public function it_should_always_return_a_clone_for_each_mutation()
    {
        $originalBuilder = new ElasticSearchOrganizerQueryBuilder();

        /* @var ElasticSearchOrganizerQueryBuilder $mutatedBuilder */
        $mutatedBuilder = $originalBuilder
            ->withStart(new Natural(30))
            ->withLimit(new Natural(10))
            ->withAutoCompleteFilter(new StringLiteral('foo'))
            ->withWebsiteFilter(Url::fromNative('http://foo.bar'));

        $expectedQueryArray = [
            'from' => 0,
            'size' => 30,
            'query' => [
                'match_all' => (object) [],
            ],
        ];

        $actualQueryArray = $originalBuilder->build()->toArray();
        $mutatedQueryArray = $mutatedBuilder->build()->toArray();

        $this->assertEquals($expectedQueryArray, $actualQueryArray);
        $this->assertNotEquals($expectedQueryArray, $mutatedQueryArray);
    }
}
