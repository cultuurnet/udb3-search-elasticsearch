<?php

namespace CultuurNet\UDB3\Search\ElasticSearch\Operations;

class UpdatePlaceMapping extends AbstractMappingOperation
{
    /**
     * @param string $indexName
     */
    public function run($indexName)
    {
        $this->updateMapping(
            $indexName,
            'place',
            __DIR__ . '/json/mapping_place.json'
        );
    }
}
