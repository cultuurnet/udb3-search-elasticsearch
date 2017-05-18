<?php

namespace CultuurNet\UDB3\Search\ElasticSearch\Organizer;

use CultuurNet\UDB3\Language;
use CultuurNet\UDB3\Search\ElasticSearch\AbstractElasticSearchQueryBuilder;
use CultuurNet\UDB3\Search\Organizer\OrganizerQueryBuilderInterface;
use ONGR\ElasticsearchDSL\Query\Compound\BoolQuery;
use ONGR\ElasticsearchDSL\Query\FullText\MatchPhraseQuery;
use ONGR\ElasticsearchDSL\Query\FullText\MatchQuery;
use ValueObjects\StringLiteral\StringLiteral;
use ValueObjects\Web\Url;

class ElasticSearchOrganizerQueryBuilder extends AbstractElasticSearchQueryBuilder implements
    OrganizerQueryBuilderInterface
{
    /**
     * @inheritdoc
     */
    protected function getPredefinedQueryStringFields(Language ...$languages)
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function withAutoCompleteFilter(StringLiteral $input)
    {
        // Currently not translatable, just look in the Dutch version for now.
        $nameQuery = new MatchPhraseQuery('name.nl.autocomplete', $input->toNative());

        $c = $this->getClone();
        $c->boolQuery->add($nameQuery, BoolQuery::FILTER);
        return $c;
    }

    /**
     * @inheritdoc
     */
    public function withWebsiteFilter(Url $url)
    {
        $urlQuery = new MatchQuery('url', (string) $url);

        $c = $this->getClone();
        $c->boolQuery->add($urlQuery, BoolQuery::FILTER);
        return $c;
    }
}
