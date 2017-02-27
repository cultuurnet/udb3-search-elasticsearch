<?php

namespace CultuurNet\UDB3\Search\ElasticSearch\Operations;

class UpdateIndexAlias extends AbstractElasticSearchOperation
{
    /**
     * @param string $aliasName
     * @param string $newIndexName
     * @param string|null $previousIndexName
     */
    public function run(
        $aliasName,
        $newIndexName,
        $previousIndexName = null
    ) {
        $aliasOnNewIndex = [
            'index' => $newIndexName,
            'name' => $aliasName,
        ];

        $aliasOnPreviousIndex = [
            'index' => $previousIndexName,
            'name' => $aliasName,
        ];

        if ($this->client->indices()->existsAlias($aliasOnNewIndex)) {
            $this->logger->info("Alias {$aliasName} already exists on index {$newIndexName}.");
            return;
        }

        if (!is_null($previousIndexName) && $this->client->indices()->existsAlias($aliasOnPreviousIndex)) {
            $this->client->indices()->deleteAlias($aliasOnPreviousIndex);
            $this->logger->info("Deleted alias {$aliasName} from index {$previousIndexName}.");
        }

        $this->client->indices()->putAlias($aliasOnNewIndex);

        $this->logger->info("Created alias {$aliasName} on index {$newIndexName}.");
    }
}
