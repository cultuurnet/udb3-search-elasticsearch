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
            $query['body']['query']['filtered']['query']['match']['name'] = $searchParameters->getName()->toNative();
        }

        if (!is_null($searchParameters->getWebsite())) {
            $query['body']['query']['filtered']['filter']['terms']['url'] = [(string) $searchParameters->getWebsite()];
        }

        return new ElasticSearchOrganizerQuery($query);
    }
}
