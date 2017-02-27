<?php

namespace CultuurNet\UDB3\Search\ElasticSearch\Operations;

class UpdateRegionMapping extends AbstractMappingOperation
{
    /**
     * @param string $indexName
     */
    public function run($indexName)
    {
        $this->updateMapping(
            $indexName,
            'region',
            __DIR__ . '/json/mapping_region.json'
        );
    }
}
