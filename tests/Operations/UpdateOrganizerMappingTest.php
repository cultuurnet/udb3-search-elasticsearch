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
                "address" => [
                    "type" => "object",
                    "properties" => [
                        "nl" => [
                            "type" => "object",
                            "properties" => [
                                "addressCountry" => [
                                    "type" => "string",
                                    "analyzer" => "lowercase_exact_match_analyzer",
                                    "search_analyzer" => "lowercase_exact_match_analyzer",
                                ],
                                "addressLocality" => [
                                    "type" => "string",
                                    "analyzer" => "dutch",
                                ],
                                "postalCode" => [
                                    "type" => "string",
                                    "analyzer" => "lowercase_exact_match_analyzer",
                                    "search_analyzer" => "lowercase_exact_match_analyzer",
                                ],
                                "streetAddress" => [
                                    "type" => "string",
                                    "analyzer" => "dutch",
                                ],
                            ],
                        ],
                        "fr" => [
                            "type" => "object",
                            "properties" => [
                                "addressCountry" => [
                                    "type" => "string",
                                    "analyzer" => "lowercase_exact_match_analyzer",
                                    "search_analyzer" => "lowercase_exact_match_analyzer",
                                ],
                                "addressLocality" => [
                                    "type" => "string",
                                    "analyzer" => "french",
                                ],
                                "postalCode" => [
                                    "type" => "string",
                                    "analyzer" => "lowercase_exact_match_analyzer",
                                    "search_analyzer" => "lowercase_exact_match_analyzer",
                                ],
                                "streetAddress" => [
                                    "type" => "string",
                                    "analyzer" => "french",
                                ],
                            ],
                        ],
                        "de" => [
                            "type" => "object",
                            "properties" => [
                                "addressCountry" => [
                                    "type" => "string",
                                    "analyzer" => "lowercase_exact_match_analyzer",
                                    "search_analyzer" => "lowercase_exact_match_analyzer",
                                ],
                                "addressLocality" => [
                                    "type" => "string",
                                    "analyzer" => "german",
                                ],
                                "postalCode" => [
                                    "type" => "string",
                                    "analyzer" => "lowercase_exact_match_analyzer",
                                    "search_analyzer" => "lowercase_exact_match_analyzer",
                                ],
                                "streetAddress" => [
                                    "type" => "string",
                                    "analyzer" => "german",
                                ],
                            ],
                        ],
                        "en" => [
                            "type" => "object",
                            "properties" => [
                                "addressCountry" => [
                                    "type" => "string",
                                    "analyzer" => "lowercase_exact_match_analyzer",
                                    "search_analyzer" => "lowercase_exact_match_analyzer",
                                ],
                                "addressLocality" => [
                                    "type" => "string",
                                    "analyzer" => "english",
                                ],
                                "postalCode" => [
                                    "type" => "string",
                                    "analyzer" => "lowercase_exact_match_analyzer",
                                    "search_analyzer" => "lowercase_exact_match_analyzer",
                                ],
                                "streetAddress" => [
                                    "type" => "string",
                                    "analyzer" => "english",
                                ],
                            ],
                        ],
                    ],
                ],
                "originalEncodedJsonLd" => [
                    "type" => "string",
                    "index" => "not_analyzed",
                ],
                "creator" => [
                    "type" => "string",
                    "analyzer" => "lowercase_exact_match_analyzer",
                    "search_analyzer" => "lowercase_exact_match_analyzer",
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
