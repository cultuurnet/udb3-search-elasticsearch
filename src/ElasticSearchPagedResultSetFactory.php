<?php

namespace CultuurNet\UDB3\Search\ElasticSearch;

use CultuurNet\UDB3\ReadModel\JsonDocument;
use CultuurNet\UDB3\Search\ElasticSearch\Validation\ElasticSearchResponseValidatorInterface;
use CultuurNet\UDB3\Search\ElasticSearch\Validation\PagedResultSetResponseValidator;
use CultuurNet\UDB3\Search\PagedResultSet;
use ValueObjects\Number\Natural;

class ElasticSearchPagedResultSetFactory implements ElasticSearchPagedResultSetFactoryInterface
{
    /**
     * @var ElasticSearchResponseValidatorInterface|null
     */
    private $responseValidator;

    /**
     * @param ElasticSearchResponseValidatorInterface|null $responseValidator
     */
    public function __construct(
        ElasticSearchResponseValidatorInterface $responseValidator = null
    ) {
        if (is_null($responseValidator)) {
            $responseValidator = new PagedResultSetResponseValidator();
        }

        $this->responseValidator = $responseValidator;
    }

    /**
     * @inheritdoc
     */
    public function createPagedResultSet(Natural $perPage, array $response)
    {
        $this->responseValidator->validate($response);

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
