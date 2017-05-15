<?php

namespace CultuurNet\UDB3\Search\ElasticSearch\Operations;

use Elasticsearch\Client;
use Psr\Log\LoggerInterface;
use Symfony\Component\Finder\Finder;

class IndexRegionQueries extends AbstractElasticSearchOperation
{
    /**
     * @var Finder
     */
    private $finder;

    /**
     * @param Client $client
     * @param LoggerInterface $logger
     * @param Finder $finder
     */
    public function __construct(
        Client $client,
        LoggerInterface $logger,
        Finder $finder
    ) {
        parent::__construct($client, $logger);
        $this->finder = $finder;
    }

    /**
     * @param string $indexName
     *   Name of the index in which the region_query documents will be
     *   stored.
     * @param string $regionIndexName
     *   Name of the index in which the region documents themselves are
     *   stored.
     * @param string $pathToScan
     *   Path to scan recursively.
     * @param string $fileNameRegex
     *   File name (regex) to match.
     */
    public function run($indexName, $regionIndexName, $pathToScan, $fileNameRegex = '*.json')
    {
        $files = $this->finder->files()->name($fileNameRegex)->in($pathToScan);

        /* @var \Symfony\Component\Finder\SplFileInfo $file */
        foreach ($files as $file) {
            $id = pathinfo($file->getFilename(), PATHINFO_FILENAME);

            $this->logger->info("Indexing region query {$id}...");

            $this->client->index(
                [
                    'index' => $indexName,
                    'type' => 'region_query',
                    'id' => $id,
                    'body' => [
                        'percolate_query' => [
                            'geo_shape' => [
                                'geo' => [
                                    'indexed_shape' => [
                                        'index' => $regionIndexName,
                                        'type' => 'region',
                                        'id' => $id,
                                        'path' => 'location',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ]
            );
        }
    }
}
