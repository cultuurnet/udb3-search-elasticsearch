<?php

namespace CultuurNet\UDB3\Search\ElasticSearch\Operations;

use Elasticsearch\Client;
use Psr\Log\LoggerInterface;

class UpdateEventMappingTest extends AbstractMappingTestCase
{
    /**
     * @param Client $client
     * @param LoggerInterface $logger
     * @return UpdateEventMapping
     */
    protected function createOperation(Client $client, LoggerInterface $logger)
    {
        return new UpdateEventMapping($client, $logger);
    }

    /**
     * @return string
     */
    protected function getDocumentType()
    {
        return 'event';
    }

    /**
     * @return array
     */
    protected function getExpectedMappingBody()
    {
        return [
            "properties" => [
                "@id" => [
                    "type" => "string",
                    "analyzer" => "lowercase_exact_match_analyzer",
                    "search_analyzer" => "lowercase_exact_match_analyzer",
                ],
                "@type" => [
                    "type" => "string",
                    "analyzer" => "lowercase_exact_match_analyzer",
                    "search_analyzer" => "lowercase_exact_match_analyzer",
                ],
                "id" => [
                    "type" => "string",
                    "analyzer" => "lowercase_exact_match_analyzer",
                    "search_analyzer" => "lowercase_exact_match_analyzer",
                ],
                "name" => [
                    "type" => "string",
                    "fields" => [
                        "nl" => [
                            "type" => "string",
                            "analyzer" => "dutch",
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
                "description" => [
                    "type" => "string",
                    "fields" => [
                        "nl" => [
                            "type" => "string",
                            "analyzer" => "dutch",
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
                "labels_free_text" => [
                    "type" => "string",
                ],
                "terms_free_text" => [
                    "type" => "object",
                    "properties" => [
                        "id" => [
                            "type" => "string",
                            "analyzer" => "lowercase_exact_match_analyzer",
                            "search_analyzer" => "lowercase_exact_match_analyzer",
                        ],
                        "label" => [
                            "type" => "string",
                        ],
                    ],
                ],
                "performer_free_text" => [
                    "type" => "object",
                    "properties" => [
                        "name" => [
                            "type" => "string",
                        ],
                    ],
                ],
                "addressLocality" => [
                    "type" => "string",
                ],
                "postalCode" => [
                    "type" => "string",
                ],
                "streetAddress" => [
                    "type" => "string",
                ],
                "geo" => [
                    "type" => "geo_shape",
                ],
                "location" => [
                    "type" => "object",
                    "properties" => [
                        "@id" => [
                            "type" => "string",
                            "analyzer" => "lowercase_exact_match_analyzer",
                            "search_analyzer" => "lowercase_exact_match_analyzer",
                        ],
                        "@type" => [
                            "type" => "string",
                            "analyzer" => "lowercase_exact_match_analyzer",
                            "search_analyzer" => "lowercase_exact_match_analyzer",
                        ],
                        "id" => [
                            "type" => "string",
                            "analyzer" => "lowercase_exact_match_analyzer",
                            "search_analyzer" => "lowercase_exact_match_analyzer",
                        ],
                        "name" => [
                            "type" => "string",
                            "fields" => [
                                "nl" => [
                                    "type" => "string",
                                    "analyzer" => "dutch",
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
                        "labels_free_text" => [
                            "type" => "string",
                        ],
                    ],
                ],
                "organizer" => [
                    "type" => "object",
                    "properties" => [
                        "@id" => [
                            "type" => "string",
                            "analyzer" => "lowercase_exact_match_analyzer",
                            "search_analyzer" => "lowercase_exact_match_analyzer",
                        ],
                        "@type" => [
                            "type" => "string",
                            "analyzer" => "lowercase_exact_match_analyzer",
                            "search_analyzer" => "lowercase_exact_match_analyzer",
                        ],
                        "id" => [
                            "type" => "string",
                            "analyzer" => "lowercase_exact_match_analyzer",
                            "search_analyzer" => "lowercase_exact_match_analyzer",
                        ],
                        "name" => [
                            "type" => "string",
                            "fields" => [
                                "nl" => [
                                    "type" => "string",
                                    "analyzer" => "dutch",
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
                        "labels_free_text" => [
                            "type" => "string",
                        ],
                    ],
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
