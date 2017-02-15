<?php

namespace CultuurNet\UDB3\Search\ElasticSearch\Region;

use CultuurNet\UDB3\Search\Region\RegionId;
use Elasticsearch\Client;
use ValueObjects\Number\Natural;
use ValueObjects\StringLiteral\StringLiteral;

class ElasticSearchRegionSearchServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Client|\PHPUnit_Framework_MockObject_MockObject
     */
    private $client;

    /**
     * @var StringLiteral
     */
    private $indexName;

    /**
     * @var StringLiteral
     */
    private $documentType;

    /**
     * @var ElasticSearchRegionSearchService
     */
    private $service;

    public function setUp()
    {
        $this->client = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->indexName = new StringLiteral('udb3-geography');
        $this->documentType = new StringLiteral('region');

        $this->service = new ElasticSearchRegionSearchService(
            $this->client,
            $this->indexName,
            $this->documentType
        );
    }

    /**
     * @test
     */
    public function it_returns_all_region_ids_that_match_the_given_input()
    {
        $expectedQuery = [
            'index' => $this->indexName->toNative(),
            'type' => $this->documentType->toNative(),
            'body' => [
                'size' => 0,
                'suggest' => [
                    'regions' => [
                        'text' => 'l',
                        'completion' => [
                            'field' => 'name_suggest',
                        ],
                    ],
                ],
            ],
        ];

        $results = json_decode(file_get_contents(__DIR__  . '/region-suggest.json'), true);

        $this->client->expects($this->once())
            ->method('search')
            ->with($expectedQuery)
            ->willReturn($results);

        $expectedIds = [
            new RegionId('24062'),
            new RegionId('38008'),
        ];

        $actualIds = $this->service->suggest(new StringLiteral('l'));

        $this->assertEquals($expectedIds, $actualIds);
    }

    /**
     * @test
     */
    public function it_sets_a_limit_on_suggestions_if_one_is_given()
    {
        $expectedQuery = [
            'index' => $this->indexName->toNative(),
            'type' => $this->documentType->toNative(),
            'body' => [
                'size' => 0,
                'suggest' => [
                    'regions' => [
                        'text' => 'l',
                        'completion' => [
                            'field' => 'name_suggest',
                            'size' => 1
                        ],
                    ],
                ],
            ],
        ];

        $results = json_decode(file_get_contents(__DIR__  . '/region-suggest-limited-size.json'), true);

        $this->client->expects($this->once())
            ->method('search')
            ->with($expectedQuery)
            ->willReturn($results);

        $expectedIds = [
            new RegionId('24062'),
        ];

        $actualIds = $this->service->suggest(new StringLiteral('l'), new Natural(1));

        $this->assertEquals($expectedIds, $actualIds);
    }
}
