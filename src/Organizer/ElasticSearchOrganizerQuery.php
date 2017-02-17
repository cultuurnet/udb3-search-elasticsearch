<?php

namespace CultuurNet\UDB3\Search\ElasticSearch\Organizer;

use CultuurNet\UDB3\Search\Organizer\OrganizerSearchParameters;

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
     * @todo Use DSL objects to construct query.
     *
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
            // @todo Use different search_analyzer so we don't have to
            // transform input to lowercase ourselves.
            $query['query']['bool']['filter'][] = [
                'wildcard' => [
                    'name' => '*' . strtolower($searchParameters->getName()->toNative()) . '*',
                ]
            ];
        }

        if (!is_null($searchParameters->getWebsite())) {
            // @todo Use different search_analyzer so we don't have to
            // transform input to lowercase ourselves.
            $query['query']['bool']['filter'][] = [
                'term' => [
                    'url' => strtolower((string) $searchParameters->getWebsite()),
                ],
            ];
        }

        return new ElasticSearchOrganizerQuery($query);
    }
}
