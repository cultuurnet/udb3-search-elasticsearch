<?php

namespace CultuurNet\UDB3\Search\ElasticSearch;

use CultuurNet\UDB3\Search\OrganizerSearchParameters;

class ElasticSearchOrganizerQuery
{
    /**
     * @var array
     */
    private $query;

    /**
     * @param array $query
     */
    private function __construct(array $query)
    {
        $this->query = $query;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return $this->query;
    }

    /**
     * @param OrganizerSearchParameters $searchParameters
     * @return ElasticSearchOrganizerQuery
     */
    public static function fromSearchParameters(OrganizerSearchParameters $searchParameters)
    {
        $query = [
            'from' => $searchParameters->getStart()->toNative(),
            'size' => $searchParameters->getLimit()->toNative(),
        ];

        if (!is_null($searchParameters->getName())) {
            // Standard analyzer uses a "lowercase" token filter by default.
            $query['body']['query']['bool']['filter'][] = [
                'prefix' => [
                    'name' => strtolower($searchParameters->getName()->toNative())
                ]
            ];
        }

        if (!is_null($searchParameters->getWebsite())) {
            $query['body']['query']['bool']['filter'][] = [
                'term' => [
                    'url' => (string) $searchParameters->getWebsite(),
                ],
            ];
        }

        return new ElasticSearchOrganizerQuery($query);
    }
}
