<?php

namespace CultuurNet\UDB3\Search\ElasticSearch;

use CultuurNet\UDB3\Search\OrganizerSearchParameters;
use ValueObjects\Number\Natural;
use ValueObjects\String\String as StringLiteral;
use ValueObjects\Web\Url;

class ElasticSearchOrganizerQueryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_can_be_created_from_minimal_organizer_search_parameters()
    {
        $searchParameters = (new OrganizerSearchParameters())
            ->withStart(new Natural(30))
            ->withLimit(new Natural(10));

        $expectedQueryArray = [
            'from' => 30,
            'size' => 10,
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
            'body' => [
                'query' => [
                    'bool' => [
                        'filter' => [
                            [
                                'prefix' => [
                                    'name' => 'collectief cursief',
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
            ->withWebsite(Url::fromNative('http://foo.bar'));

        $expectedQueryArray = [
            'from' => 0,
            'size' => 30,
            'body' => [
                'query' => [
                    'bool' => [
                        'filter' => [
                            [
                                'term' => [
                                    'url' => 'http://foo.bar',
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
