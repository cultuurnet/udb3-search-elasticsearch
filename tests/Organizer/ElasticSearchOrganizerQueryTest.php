<?php

namespace CultuurNet\UDB3\Search\ElasticSearch\Organizer;

use CultuurNet\UDB3\Search\Organizer\OrganizerSearchParameters;
use ValueObjects\Number\Natural;
use ValueObjects\StringLiteral\StringLiteral;
use ValueObjects\Web\Url;

class ElasticSearchOrganizerQueryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_can_be_created_from_minimal_organizer_search_parameters()
    {
        /** @var OrganizerSearchParameters $searchParameters */
        $searchParameters = (new OrganizerSearchParameters())
            ->withStart(new Natural(30))
            ->withLimit(new Natural(10));

        $expectedQueryArray = [
            'from' => 30,
            'size' => 10,
            'query' => [
                'match_all' => (object) [],
            ],
        ];

        $actualQueryArray = ElasticSearchOrganizerQuery::fromSearchParameters($searchParameters)
            ->toArray();

        $this->assertEquals($expectedQueryArray, $actualQueryArray);
    }

    /**
     * @test
     */
    public function it_can_be_created_from_organizer_search_parameters_with_a_name_parameter()
    {
        $searchParameters = (new OrganizerSearchParameters())
            ->withName(new StringLiteral('Collectief Cursief'));

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

        $actualQueryArray = ElasticSearchOrganizerQuery::fromSearchParameters($searchParameters)
            ->toArray();

        $this->assertEquals($expectedQueryArray, $actualQueryArray);
    }

    /**
     * @test
     */
    public function it_can_be_created_from_organizer_search_parameters_with_a_website_parameter()
    {
        $searchParameters = (new OrganizerSearchParameters())
            ->withWebsite(Url::fromNative('http://Foo.bar'));

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
                                    'query' => 'http://Foo.bar',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $actualQueryArray = ElasticSearchOrganizerQuery::fromSearchParameters($searchParameters)
            ->toArray();

        $this->assertEquals($expectedQueryArray, $actualQueryArray);
    }
}
