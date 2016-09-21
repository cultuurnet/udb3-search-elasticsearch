<?php

namespace CultuurNet\UDB3\Search;

use CultuurNet\UDB3\ReadModel\JsonDocument;
use Elasticsearch\Client;
use ValueObjects\Number\Natural;
use ValueObjects\String\String as StringLiteral;

class ElasticSearchOrganizerSearchService implements OrganizerSearchServiceInterface
{
    use HasElasticSearchClient;

    /**
     * @param Client $elasticSearchClient
     * @param StringLiteral $indexName
     * @param StringLiteral $documentType
     */
    public function __construct(
        Client $elasticSearchClient,
        StringLiteral $indexName,
        StringLiteral $documentType
    ) {
        $this->elasticSearchClient = $elasticSearchClient;
        $this->indexName = $indexName;
        $this->documentType = $documentType;
    }

    /**
     * @param OrganizerSearchParameters $searchParameters
     * @return PagedResultSet
     */
    public function search(OrganizerSearchParameters $searchParameters)
    {
        $query = ElasticSearchOrganizerQuery::fromSearchParameters($searchParameters);

        $response = $this->elasticSearchClient->search(
            $this->createParameters($query->toArray())
        );

        $total = new Natural($response['hits']['total']);
        $perPage = $searchParameters->getLimit();

        $results = array_map(
            function (array $result) {
                return (new JsonDocument($result['_id']))
                    ->withBody($result['_source']);
            },
            $response['hits']['hits']
        );

        return new PagedResultSet(
            $total,
            $perPage,
            $results
        );
    }
}
