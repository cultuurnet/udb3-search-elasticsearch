<?php

namespace CultuurNet\UDB3\Search\ElasticSearch\Operations;

use Elasticsearch\Client;
use Psr\Log\LoggerInterface;
use Symfony\Component\Finder\Finder;

class IndexRegionQueriesTest extends AbstractOperationTestCase
{
    /**
     * @var Finder
     */
    private $finder;

    public function setUp()
    {
        $this->finder = new Finder();
        parent::setUp();
    }

    /**
     * @param Client $client
     * @param LoggerInterface $logger
     * @return IndexRegionQueries
     */
    protected function createOperation(Client $client, LoggerInterface $logger)
    {
        return new IndexRegionQueries($client, $logger, $this->finder);
    }

    /**
     * @test
     */
    public function it_indexes_all_files_located_in_the_given_path_or_subdirectories_that_match_the_file_name_regex()
    {
        $index = 'mock';
        $geoShapeIndexName = 'geoshapes';
        $path = __DIR__ . '/data/regions/';

        $this->client->expects($this->exactly(3))
            ->method('index')
            ->withConsecutive(
                [
                    [
                        'index' => $index,
                        'id' => 'gem-antwerpen',
                        'type' => 'region_query',
                        'body' => [
                            'percolate_query' => [
                                'geo_shape' => [
                                    'geo' => [
                                        'indexed_shape' => [
                                            'index' => $geoShapeIndexName,
                                            'type' => 'region',
                                            'id' => 'gem-antwerpen',
                                            'path' => 'location',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    [
                        'index' => $index,
                        'id' => 'gem-leuven',
                        'type' => 'region_query',
                        'body' => [
                            'percolate_query' => [
                                'geo_shape' => [
                                    'geo' => [
                                        'indexed_shape' => [
                                            'index' => $geoShapeIndexName,
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
                [
                    [
                        'index' => $index,
                        'id' => 'prov-vlaams-brabant',
                        'type' => 'region_query',
                        'body' => [
                            'percolate_query' => [
                                'geo_shape' => [
                                    'geo' => [
                                        'indexed_shape' => [
                                            'index' => $geoShapeIndexName,
                                            'type' => 'region',
                                            'id' => 'prov-vlaams-brabant',
                                            'path' => 'location',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ]
            );

        $this->operation->run($index, $geoShapeIndexName, $path);
    }
}
