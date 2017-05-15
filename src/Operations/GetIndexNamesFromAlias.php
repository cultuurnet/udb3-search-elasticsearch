<?php

namespace CultuurNet\UDB3\Search\ElasticSearch\Operations;

class GetIndexNamesFromAlias extends AbstractElasticSearchOperation
{
    /**
     * Key returned when the alias does not exist.
     */
    const ERROR_KEY = 'error';

    /**
     * @param string $aliasName
     *   If an actual index name is given instead of an alias, the operation
     *   will return the same index name.
     *
     * @return string[]
     *   All index names the alias points to.
     */
    public function run($aliasName)
    {
        /* @var array $response */
        $response = $this->client->indices()->get(['index' => $aliasName]);

        $indexNames = array_keys($response);

        if (in_array(self::ERROR_KEY, $indexNames)) {
            return [];
        }

        return $indexNames;
    }
}
