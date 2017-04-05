<?php

namespace CultuurNet\UDB3\Search\ElasticSearch\Offer;

use CultuurNet\UDB3\Offer\OfferType;
use CultuurNet\UDB3\ReadModel\JsonDocument;
use Elasticsearch\Client;
use ValueObjects\StringLiteral\StringLiteral;

class PercolatorOfferRegionService implements OfferRegionServiceInterface
{
    /**
     * Amount of (matching) regions per page.
     */
    const PAGE_SIZE = 10;

    /**
     * @var Client
     */
    private $client;

    /**
     * @var StringLiteral
     */
    private $indexName;

    /**
     * @param Client $elasticSearchClient
     * @param StringLiteral $indexName
     */
    public function __construct(
        Client $elasticSearchClient,
        StringLiteral $indexName
    ) {
        $this->client = $elasticSearchClient;
        $this->indexName = $indexName;
    }

    /**
     * @param OfferType $offerType
     * @param JsonDocument $jsonDocument
     * @return \string[]
     */
    public function getRegionIds(OfferType $offerType, JsonDocument $jsonDocument)
    {
        $regionIds = [];

        $id = $jsonDocument->getId();
        $documentSource = json_decode($jsonDocument->getRawBody(), true);
        $documentType = strtolower($offerType->toNative());

        $query = [
            'query' => [
                'percolate' => [
                    'field' => 'percolate_query',
                    'document_type' => $documentType,
                    'document' => $documentSource,
                ]
            ]
        ];

        $pageNumber = 0;
        $processedHits = 0;

        do {
            $response = $this->client->search(
                [
                    'index' => $this->indexName->toNative(),
                    'body' => $query,
                    'size' => self::PAGE_SIZE,
                    'from' => self::PAGE_SIZE * $pageNumber,
                ]
            );

            if (!isset($response['hits']) || !isset($response['hits']['total']) || !isset($response['hits']['hits'])) {
                throw new \RuntimeException(
                    "Got invalid response from ElasticSearch when trying to find regions for $documentType $id."
                );
            }

            $total = $response['hits']['total'];

            foreach ($response['hits']['hits'] as $hit) {
                if ($hit['_type'] == 'region_query') {
                    $regionIds[] = $hit['_id'];
                }

                $processedHits++;
            }

            $pageNumber++;
        } while($total > $processedHits);

        return $regionIds;
    }
}
