<?php

namespace CultuurNet\UDB3\Search\ElasticSearch;

use Elasticsearch\Client;
use ValueObjects\StringLiteral\StringLiteral;

trait HasElasticSearchClient
{
    /**
     * @var Client
     */
    private $elasticSearchClient;

    /**
     * @var StringLiteral
     */
    private $indexName;

    /**
     * @var StringLiteral
     */
    private $documentType;

    /**
     * @return array
     */
    private function getDefaultParameters()
    {
        return [
            'index' => $this->indexName->toNative(),
            'type' => $this->documentType->toNative(),
        ];
    }

    /**
     * @param array $parameters
     * @return array
     */
    private function createParameters(array $parameters)
    {
        return $this->getDefaultParameters() + $parameters;
    }
}
