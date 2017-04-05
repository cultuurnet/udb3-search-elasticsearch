<?php

namespace CultuurNet\UDB3\Search\ElasticSearch\Offer;

use CultuurNet\UDB3\Offer\OfferType;
use CultuurNet\UDB3\ReadModel\JsonDocument;
use Elasticsearch\Client;
use ValueObjects\StringLiteral\StringLiteral;

class PercolatorOfferRegionServiceTest extends \PHPUnit_Framework_TestCase
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
     * @var PercolatorOfferRegionService
     */
    private $offerRegionService;

    public function setUp()
    {
        $this->client = $this->createMock(Client::class);
        $this->indexName = new StringLiteral('mock');

        $this->offerRegionService = new PercolatorOfferRegionService(
            $this->client,
            $this->indexName
        );
    }

    /**
     * @test
     */
    public function it_uses_a_percolate_query_and_returns_all_region_ids_of_the_matching_queries()
    {
        $id = '0ec56b06-d854-4a4b-8aeb-4848433170bc';
        $jsonData = [
            '@id' => 'http://mock.io/event/0ec56b06-d854-4a4b-8aeb-4848433170bc',
            '@type' => 'Event',
            'geo' => [80.9, -4.5],
        ];

        $jsonDocument = new JsonDocument($id, json_encode($jsonData));

        $this->client->expects($this->exactly(2))
            ->method('search')
            ->withConsecutive(
                [
                    [
                        'index' => $this->indexName->toNative(),
                        'body' => [
                            'query' => [
                                'percolate' => [
                                    'field' => 'percolate_query',
                                    'document_type' => 'event',
                                    'document' => $jsonData,
                                ],
                            ],
                        ],
                        'size' => 10,
                        'from' => 0,
                    ],
                ],
                [
                    [
                        'index' => $this->indexName->toNative(),
                        'body' => [
                            'query' => [
                                'percolate' => [
                                    'field' => 'percolate_query',
                                    'document_type' => 'event',
                                    'document' => $jsonData,
                                ],
                            ],
                        ],
                        'size' => 10,
                        'from' => 10,
                    ],
                ]
            )
            ->willReturnOnConsecutiveCalls(
                json_decode(file_get_contents(__DIR__ . '/data/region_queries_1.json'), true),
                json_decode(file_get_contents(__DIR__ . '/data/region_queries_2.json'), true)
            );

        $expectedRegionIds = [
            'gem-nieuwerkerken',
            'gem-oostkamp',
            'gem-oostrozebeke',
            'gem-opglabbeek',
            'gem-peer',
            'gem-pittem',
            'gem-putte',
            'gem-ronse',
            'gem-roosdaal',
            'gem-ruiselede',
            'gem-rumst',
            'gem-sint-amands',
            'gem-sint-genesius-rode',
            'gem-sint-laureins',
            'gem-ternat',
            'gem-tervuren',
            'gem-kalmthout',
            'gem-kinrooi',
            'gem-kluisbergen',
            'gem-kortenaken',
        ];

        $actualRegionIds = $this->offerRegionService->getRegionIds(OfferType::EVENT(), $jsonDocument);

        $this->assertEquals($expectedRegionIds, $actualRegionIds);
    }

    /**
     * @test
     */
    public function it_throws_an_exception_if_it_gets_an_invalid_response_from_elasticsearch()
    {
        $id = '0ec56b06-d854-4a4b-8aeb-4848433170bc';
        $jsonData = [
            '@id' => 'http://mock.io/event/0ec56b06-d854-4a4b-8aeb-4848433170bc',
            '@type' => 'Event',
            'geo' => [80.9, -4.5],
        ];

        $jsonDocument = new JsonDocument($id, json_encode($jsonData));

        $this->client->expects($this->once())
            ->method('search')
            ->willReturn(json_decode(file_get_contents(__DIR__ . '/data/region_queries_invalid.json'), true));

        $this->expectException(\RuntimeException::class);

        $this->offerRegionService->getRegionIds(OfferType::EVENT(), $jsonDocument);
    }
}
