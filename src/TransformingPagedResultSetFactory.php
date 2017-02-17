<?php

namespace CultuurNet\UDB3\Search\ElasticSearch;

use CultuurNet\UDB3\ReadModel\JsonDocument;
use CultuurNet\UDB3\Search\JsonDocument\JsonDocumentTransformerInterface;
use CultuurNet\UDB3\Search\PagedResultSet;
use ValueObjects\Number\Natural;

class TransformingPagedResultSetFactory implements ElasticSearchPagedResultSetFactoryInterface
{
    /**
     * @var JsonDocumentTransformerInterface
     */
    private $jsonDocumentTransformer;

    /**
     * @param JsonDocumentTransformerInterface $jsonDocumentTransformer
     */
    public function __construct(
        JsonDocumentTransformerInterface $jsonDocumentTransformer
    ) {
        $this->jsonDocumentTransformer = $jsonDocumentTransformer;
    }

    /**
     * @inheritdoc
     */
    public function createPagedResultSet(Natural $perPage, array $response)
    {
        $total = new Natural($response['hits']['total']);

        $results = array_map(
            function (array $result) {
                $jsonDocument = (new JsonDocument($result['_id']))
                    ->withBody($result['_source']);

                return $this->jsonDocumentTransformer->transform($jsonDocument);
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
