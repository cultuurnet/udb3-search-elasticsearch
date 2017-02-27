<?php

namespace CultuurNet\UDB3\Search\ElasticSearch\Operations;

use Elasticsearch\Client;
use Psr\Log\LoggerInterface;

class UpdateIndexAliasTest extends AbstractOperationTestCase
{
    /**
     * @param Client $client
     * @param LoggerInterface $logger
     * @return UpdateIndexAlias
     */
    protected function createOperation(Client $client, LoggerInterface $logger)
    {
        return new UpdateIndexAlias($client, $logger);
    }

    /**
     * @test
     */
    public function it_does_nothing_if_the_alias_already_exists_on_the_new_index()
    {
        $previousIndex = 'mock_v1';
        $newIndex = 'mock_v2';
        $alias = 'mock';

        $this->indices->expects($this->once())
            ->method('existsAlias')
            ->with(
                [
                    'index' => $newIndex,
                    'name' => $alias,
                ]
            )
            ->willReturn(true);

        $this->logger->expects($this->once())
            ->method('info')
            ->with('Alias mock already exists on index mock_v2.');

        $this->operation->run($alias, $newIndex, $previousIndex);
    }

    /**
     * @test
     */
    public function it_deletes_the_alias_from_the_previous_index_and_puts_it_on_the_new_index()
    {
        $previousIndex = 'mock_v1';
        $newIndex = 'mock_v2';
        $alias = 'mock';

        $this->indices->expects($this->exactly(2))
            ->method('existsAlias')
            ->withConsecutive(
                [
                    [
                        'index' => $newIndex,
                        'name' => $alias,
                    ],
                ],
                [
                    [
                        'index' => $previousIndex,
                        'name' => $alias,
                    ],
                ]
            )
            ->willReturnOnConsecutiveCalls(false, true);

        $this->indices->expects($this->once())
            ->method('putAlias')
            ->with(
                [
                    'index' => $newIndex,
                    'name' => $alias,
                ]
            );

        $this->logger->expects($this->exactly(2))
            ->method('info')
            ->withConsecutive(
                ['Deleted alias mock from index mock_v1.'],
                ['Created alias mock on index mock_v2.']
            );

        $this->operation->run($alias, $newIndex, $previousIndex);
    }

    /**
     * @test
     */
    public function it_does_not_delete_the_alias_from_the_previous_index_if_it_is_not_set_on_the_previous_index()
    {
        $previousIndex = 'mock_v1';
        $newIndex = 'mock_v2';
        $alias = 'mock';

        $this->indices->expects($this->exactly(2))
            ->method('existsAlias')
            ->withConsecutive(
                [
                    [
                        'index' => $newIndex,
                        'name' => $alias,
                    ],
                ],
                [
                    [
                        'index' => $previousIndex,
                        'name' => $alias,
                    ],
                ]
            )
            ->willReturnOnConsecutiveCalls(false, false);

        $this->indices->expects($this->once())
            ->method('putAlias')
            ->with(
                [
                    'index' => $newIndex,
                    'name' => $alias,
                ]
            );

        $this->logger->expects($this->once())
            ->method('info')
            ->with('Created alias mock on index mock_v2.');

        $this->operation->run($alias, $newIndex, $previousIndex);
    }

    /**
     * @test
     */
    public function it_does_not_delete_the_alias_from_the_previous_index_if_there_is_no_previous_index()
    {
        $previousIndex = null;
        $newIndex = 'mock_v1';
        $alias = 'mock';

        $this->indices->expects($this->once())
            ->method('existsAlias')
            ->with(
                [
                    'index' => $newIndex,
                    'name' => $alias,
                ]
            )
            ->willReturn(false);

        $this->indices->expects($this->once())
            ->method('putAlias')
            ->with(
                [
                    'index' => $newIndex,
                    'name' => $alias,
                ]
            );

        $this->logger->expects($this->once())
            ->method('info')
            ->with('Created alias mock on index mock_v1.');

        $this->operation->run($alias, $newIndex, $previousIndex);
    }
}
