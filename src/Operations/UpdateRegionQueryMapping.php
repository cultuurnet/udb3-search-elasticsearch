<?php

namespace CultuurNet\UDB3\Search\ElasticSearch\Operations;

class UpdateRegionQueryMapping extends AbstractMappingOperation
{
    /**
     * @param string $indexName
     * @param string $documentType
     */
    public function run($indexName, $documentType)
    {
        $this->updateMapping(
            $indexName,
            $documentType,
            __DIR__ . '/json/mapping_region_query.json'
        );
    }
}
