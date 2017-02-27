<?php

namespace CultuurNet\UDB3\Search\ElasticSearch\Operations;

class UpdateOrganizerMapping extends AbstractMappingOperation
{
    /**
     * @param string $indexName
     */
    public function run($indexName)
    {
        $this->updateMapping(
            $indexName,
            'organizer',
            __DIR__ . '/json/mapping_organizer.json'
        );
    }
}
