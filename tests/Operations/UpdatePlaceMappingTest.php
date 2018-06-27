<?php

namespace CultuurNet\UDB3\Search\ElasticSearch\Operations;

use Elasticsearch\Client;
use Psr\Log\LoggerInterface;

class UpdatePlaceMappingTest extends AbstractMappingTestCase
{
    /**
     * @param Client $client
     * @param LoggerInterface $logger
     * @return UpdatePlaceMapping
     */
    protected function createOperation(Client $client, LoggerInterface $logger)
    {
        return new UpdatePlaceMapping($client, $logger);
    }

    /**
     * @return string
     */
    protected function getDocumentType()
    {
        return 'place';
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
                "availableTo" => [
                    "type" => "date",
                    "format" => "yyyy-MM-dd'T'HH:mm:ssZZ",
                ],
                "dateRange" => [
                    "type" => "date_range",
                    "format" => "yyyy-MM-dd'T'HH:mm:ssZZ",
                ],
                "calendarType" => [
                    "type" => "string",
                    "analyzer" => "lowercase_exact_match_analyzer",
                    "search_analyzer" => "lowercase_exact_match_analyzer",
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
                "mainLanguage" => [
                    "type" => "string",
                    "analyzer" => "lowercase_exact_match_analyzer",
                    "search_analyzer" => "lowercase_exact_match_analyzer",
                ],
                "languages" => [
                    "type" => "string",
                    "analyzer" => "lowercase_exact_match_analyzer",
                    "search_analyzer" => "lowercase_exact_match_analyzer",
                ],
                "completedLanguages" => [
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
                "typicalAgeRange" => [
                    "type" => "integer_range",
                ],
                "allAges" => [
                    "type" => "boolean",
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
                "created" => [
                    "type" => "date",
                    "format" => "yyyy-MM-dd'T'HH:mm:ssZZ",
                ],
                "modified" => [
                    "type" => "date",
                    "format" => "yyyy-MM-dd'T'HH:mm:ssZZ",
                ],
                "creator" => [
                    "type" => "string",
                    "analyzer" => "lowercase_exact_match_analyzer",
                    "search_analyzer" => "lowercase_exact_match_analyzer",
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
