<?php

namespace CultuurNet\UDB3\Search\ElasticSearch\Operations;

use Elasticsearch\Client;
use Psr\Log\LoggerInterface;

class TestIndexExistsTest extends AbstractOperationTestCase
{
    /**
     * @param Client $client
     * @param LoggerInterface $logger
     * @return TestIndexExists
     */
    protected function createOperation(Client $client, LoggerInterface $logger)
    {
        return new TestIndexExists($client, $logger);
    }

    /**
     * @test
     * @dataProvider indexExistsDataProvider
     *
     * @param string $indexName
     * @param bool $exists
     */
    public function it_returns_the_status_of_the_given_index_returned_by_the_api_client(
        $indexName,
        $exists
    ) {
        $this->indices->expects($this->once())
            ->method('exists')
            ->with(['index' => $indexName])
            ->willReturn($exists);

        $this->assertEquals($exists, $this->operation->run($indexName));
    }

    /**
     * @return array
     */
    public function indexExistsDataProvider()
    {
        return [
            [
                'indexName' => 'acme',
                'exists' => true,
            ],
            [
                'indexName' => 'mock',
                'exists' => false,
            ],
        ];
    }
}
