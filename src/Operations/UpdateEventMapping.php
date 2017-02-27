<?php

namespace CultuurNet\UDB3\Search\ElasticSearch\Operations;

class UpdateEventMapping extends AbstractMappingOperation
{
    /**
     * @param string $indexName
     */
    public function run($indexName)
    {
        $this->updateMapping(
            $indexName,
            'event',
            __DIR__ . '/json/mapping_event.json'
        );
    }
}
