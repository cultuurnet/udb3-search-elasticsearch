<?php

namespace CultuurNet\UDB3\Search\ElasticSearch\Organizer;

use CultuurNet\UDB3\Search\Organizer\OrganizerSearchParameters;
use ONGR\ElasticsearchDSL\Query\Compound\BoolQuery;
use ONGR\ElasticsearchDSL\Query\FullText\MatchPhraseQuery;
use ONGR\ElasticsearchDSL\Query\FullText\MatchQuery;
use ONGR\ElasticsearchDSL\Query\MatchAllQuery;
use ONGR\ElasticsearchDSL\Search;

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
        $boolQuery = new BoolQuery();

        $matchAllQuery = new MatchAllQuery();
        $boolQuery->add($matchAllQuery, BoolQuery::MUST);

        if (!is_null($searchParameters->getName())) {
            // Currently not translatable so only look in the Dutch version for
            // now.
            $nameQuery = new MatchPhraseQuery('name.nl.autocomplete', $searchParameters->getName()->toNative());
            $boolQuery->add($nameQuery, BoolQuery::FILTER);
        }

        if (!is_null($searchParameters->getWebsite())) {
            $urlQuery = new MatchQuery('url', (string) $searchParameters->getWebsite());
            $boolQuery->add($urlQuery, BoolQuery::FILTER);
        }

        $search = new Search();
        $search->setFrom($searchParameters->getStart()->toNative());
        $search->setSize($searchParameters->getLimit()->toNative());
        $search->addQuery($boolQuery);

        return new ElasticSearchOrganizerQuery($search->toArray());
    }
}
