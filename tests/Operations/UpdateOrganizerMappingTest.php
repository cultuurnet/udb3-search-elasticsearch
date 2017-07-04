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
                    "type" => "object",
                    "properties" => [
                        "nl" => [
                            "type" => "string",
                            "analyzer" => "dutch",
                            "fields" => [
                                "autocomplete" => [
                                    "type" => "string",
                                    "analyzer" => "autocomplete_analyzer",
                                    "search_analyzer" => "standard",
                                ],
                            ],
                        ],
                        "fr" => [
                            "type" => "string",
                            "analyzer" => "french",
                        ],
                        "en" => [
                            "type" => "string",
                            "analyzer" => "english",
                        ],
                        "de" => [
                            "type" => "string",
                            "analyzer" => "german",
                        ],
                    ],
                ],
                "url" => [
                    "type" => "string",
                    "analyzer" => "lowercase_exact_match_analyzer",
                ],
                "originalEncodedJsonLd" => [
                    "type" => "string",
                    "index" => "not_analyzed",
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
