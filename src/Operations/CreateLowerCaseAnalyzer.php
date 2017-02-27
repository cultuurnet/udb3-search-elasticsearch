<?php

namespace CultuurNet\UDB3\Search\ElasticSearch\Operations;

class CreateLowerCaseAnalyzer extends AbstractElasticSearchOperation
{
    public function run()
    {
        $this->client->indices()->putTemplate(
            [
                'name' => 'lowercase_analyzer',
                'body' => json_decode(
                    file_get_contents(__DIR__ . '/json/analyzer_lowercase.json'),
                    true
                ),
            ]
        );

        $this->logger->info('Lowercase analyzer created.');
    }
}
