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
                "availableRange" => [
                    "type" => "date_range",
                    "format" => "yyyy-MM-dd'T'HH:mm:ssZZ",
                ],
                "dateRange" => [
                    "type" => "date_range",
                    "format" => "yyyy-MM-dd'T'HH:mm:ssZZ",
                ],
                "workflowStatus" => [
                    "type" => "string",
                    "analyzer" => "lowercase_exact_match_analyzer",
                    "search_analyzer" => "lowercase_exact_match_analyzer",
                ],
                "name" => [
                    "type" => "object",
                    "properties" => [
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
                    "type" => "object",
                    "properties" => [
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
                "languages" => [
                    "type" => "string",
                    "analyzer" => "lowercase_exact_match_analyzer",
                    "search_analyzer" => "lowercase_exact_match_analyzer",
                ],
                "labels" => [
                    "type" => "string",
                    "analyzer" => "lowercase_exact_match_analyzer",
                    "search_analyzer" => "lowercase_exact_match_analyzer",
                ],
                "labels_free_text" => [
                    "type" => "string",
                ],
                "terms" => [
                    "type" => "object",
                    "properties" => [
                        "id" => [
                            "type" => "string",
                            "analyzer" => "lowercase_exact_match_analyzer",
                            "search_analyzer" => "lowercase_exact_match_analyzer",
                        ],
                        "label" => [
                            "type" => "string",
                            "analyzer" => "lowercase_exact_match_analyzer",
                            "search_analyzer" => "lowercase_exact_match_analyzer",
                        ],
                    ],
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
                "typeIds" => [
                    "type" => "keyword",
                ],
                "themeIds" => [
                    "type" => "keyword",
                ],
                "facilityIds" => [
                    "type" => "keyword",
                ],
                "performer_free_text" => [
                    "type" => "object",
                    "properties" => [
                        "name" => [
                            "type" => "string",
                        ],
                    ],
                ],
                "typicalAgeRange" => [
                    "type" => "integer_range",
                ],
                "price" => [
                    "type" => "scaled_float",
                    "scaling_factor" => 100,
                ],
                "audienceType" => [
                    "type" => "string",
                    "analyzer" => "lowercase_exact_match_analyzer",
                    "search_analyzer" => "lowercase_exact_match_analyzer",
                ],
                "mediaObjectsCount" => [
                    "type" => "integer",
                ],
                "addressCountry" => [
                    "type" => "string",
                    "analyzer" => "lowercase_exact_match_analyzer",
                    "search_analyzer" => "lowercase_exact_match_analyzer",
                ],
                "addressLocality" => [
                    "type" => "string",
                ],
                "postalCode" => [
                    "type" => "string",
                    "analyzer" => "lowercase_exact_match_analyzer",
                    "search_analyzer" => "lowercase_exact_match_analyzer",
                ],
                "streetAddress" => [
                    "type" => "string",
                ],
                "geo" => [
                    "type" => "geo_shape",
                ],
                "geo_point" => [
                    "type" => "geo_point",
                ],
                "regions" => [
                    "type" => "string",
                    "analyzer" => "lowercase_exact_match_analyzer",
                    "search_analyzer" => "lowercase_exact_match_analyzer",
                    "fields" => [
                        "keyword" => [
                            "type" => "keyword",
                        ],
                    ],
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
                            "type" => "object",
                            "properties" => [
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
                        "terms" => [
                            "type" => "object",
                            "properties" => [
                                "id" => [
                                    "type" => "string",
                                    "analyzer" => "lowercase_exact_match_analyzer",
                                    "search_analyzer" => "lowercase_exact_match_analyzer",
                                ],
                                "label" => [
                                    "type" => "string",
                                    "analyzer" => "lowercase_exact_match_analyzer",
                                    "search_analyzer" => "lowercase_exact_match_analyzer",
                                ],
                            ],
                        ],
                        "labels" => [
                            "type" => "string",
                            "analyzer" => "lowercase_exact_match_analyzer",
                            "search_analyzer" => "lowercase_exact_match_analyzer",
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
                            "type" => "object",
                            "properties" => [
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
                        "labels" => [
                            "type" => "string",
                            "analyzer" => "lowercase_exact_match_analyzer",
                            "search_analyzer" => "lowercase_exact_match_analyzer",
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
