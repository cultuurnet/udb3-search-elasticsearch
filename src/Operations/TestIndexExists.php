<?php

namespace CultuurNet\UDB3\Search\ElasticSearch\Operations;

class TestIndexExists extends AbstractElasticSearchOperation
{
    /**
     * @param string $indexName
     * @return bool
     */
    public function run($indexName)
    {
        return $this->client->indices()->exists(['index' => $indexName]);
    }
}
