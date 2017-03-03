<?php

namespace CultuurNet\UDB3\Search\ElasticSearch\Operations;

use Elasticsearch\Client;
use Psr\Log\LoggerInterface;

class CreateLowerCaseAnalyzerTest extends AbstractOperationTestCase
{
    /**
     * @param Client $client
     * @param LoggerInterface $logger
     * @return CreateLowerCaseAnalyzer
     */
    protected function createOperation(Client $client, LoggerInterface $logger)
    {
        return new CreateLowerCaseAnalyzer($client, $logger);
    }

    /**
     * @test
     */
    public function it_puts_a_new_or_updated_index_template_for_a_lowercase_analyzer()
    {
        $this->indices->expects($this->once())
            ->method('putTemplate')
            ->with(
                [
                    'name' => 'lowercase_analyzer',
                    'body' => [
                        'template' => '*',
                        'settings' => [
                            'analysis' => [
                                'analyzer' => [
                                    'lowercase_analyzer' => [
                                        'tokenizer' => 'keyword',
                                        'filter' => ['lowercase'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ]
            );

        $this->logger->expects($this->once())
            ->method('info')
            ->with('Lowercase analyzer created.');

        $this->operation->run();
    }
}
