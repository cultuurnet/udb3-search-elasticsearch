<?php

namespace CultuurNet\UDB3\Search\ElasticSearch\Migrations;

use Elasticsearch\Client;

class Version20161011000440
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function up()
    {

        if ($this->client->indices()->existsAlias(["name" => "organizer"])) {
            return;
        }

        $lowercaseAnalyzer = [
            "name" => "lowercase_analyzer",
            "body" => [
                "template" => "*",
                "settings" => [
                    "analysis" => [
                        "analyzer" => [
                            "lowercase_analyzer" => [
                                "tokenizer" => "keyword",
                                "filter" => ["lowercase"]
                            ]
                        ]
                    ]
                ]
            ]
        ];


        $organizer = [
            "index" => "organizer_20161011000440",
            "body" => [
                "mappings" => [
                    "organizer" => [
                        "properties" => [
                            "name" => [
                                "type" => "string",
                                "analyzer" => "lowercase_analyzer"
                            ],
                            "url" => [
                                "type" => "string",
                                "analyzer" => "lowercase_analyzer"
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $aliasActions = [
            "body" => [
                "actions" => [
                    [
                        "add" => [
                            "index" => "organizer_20161011000440",
                            "alias" => "organizer"
                        ]
                    ]
                ]
            ]
        ];

        $this->client->indices()->putTemplate($lowercaseAnalyzer);
        $this->client->indices()->create($organizer);
        $this->client->indices()->updateAliases($aliasActions);
    }
}
