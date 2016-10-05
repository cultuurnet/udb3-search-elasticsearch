<?php

namespace CultuurNet\UDB3\Search;

use ValueObjects\Number\Natural;
use ValueObjects\String\String as StringLiteral;

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
                    'wildcard' => [
                        'name' => '*Collectief Cursief*',
                    ],
                ],
            ],
        ];

        $actualQueryArray = ElasticSearchOrganizerQuery::fromSearchParameters($searchParameters)
            ->toArray();

        $this->assertEquals($expectedQueryArray, $actualQueryArray);
    }
}
