<?php

namespace CultuurNet\UDB3\Search\ElasticSearch\Operations;

use Elasticsearch\Client;
use Psr\Log\LoggerInterface;

class UpdateOrganizerMappingTest extends AbstractMappingTestCase
{
    /**
     * @param Client $client
     * @param LoggerInterface $logger
     * @return UpdateOrganizerMapping
     */
    protected function createOperation(Client $client, LoggerInterface $logger)
    {
        return new UpdateOrganizerMapping($client, $logger);
    }

    /**
     * @return string
     */
    protected function getDocumentType()
    {
        return 'organizer';
    }

    /**
     * @return array
     */
    protected function getExpectedMappingBody()
    {
        return [
            "properties" => [
                "name" => [
                    "type" => "string",
                    "analyzer" => "lowercase_analyzer",
                ],
                "url" => [
                    "type" => "string",
                    "analyzer" => "lowercase_analyzer",
                ],
            ],
        ];
    }

    /**
     * @param string $indexName
     */
    protected function runOperation($indexName)
    {
        $this->operation->run($indexName, $this->getDocumentType());
    }
}
