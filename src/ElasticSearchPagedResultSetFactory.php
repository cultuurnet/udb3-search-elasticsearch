<?php

namespace CultuurNet\UDB3\Search\ElasticSearch;

use CultuurNet\UDB3\ReadModel\JsonDocument;
use CultuurNet\UDB3\Search\PagedResultSet;
use ValueObjects\Number\Natural;

class ElasticSearchPagedResultSetFactory implements ElasticSearchPagedResultSetFactoryInterface
{
    /**
     * @inheritdoc
     */
    public function createPagedResultSet(Natural $perPage, array $response)
    {
        $total = new Natural($response['hits']['total']);

        $results = array_map(
            function (array $result) {
                return (new JsonDocument($result['_id']))
                    ->withBody($result['_source']);
            },
            $response['hits']['hits']
        );

        return new PagedResultSet(
            $total,
            $perPage,
            $results
        );
    }
}
