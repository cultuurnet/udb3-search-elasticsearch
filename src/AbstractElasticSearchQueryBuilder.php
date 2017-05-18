<?php

namespace CultuurNet\UDB3\Search\ElasticSearch;

use CultuurNet\UDB3\Language;
use CultuurNet\UDB3\Search\AbstractQueryString;
use CultuurNet\UDB3\Search\QueryBuilderInterface;
use DeepCopy\DeepCopy;
use ONGR\ElasticsearchDSL\Query\Compound\BoolQuery;
use ONGR\ElasticsearchDSL\Query\FullText\MatchPhraseQuery;
use ONGR\ElasticsearchDSL\Query\FullText\MatchQuery;
use ONGR\ElasticsearchDSL\Query\FullText\QueryStringQuery;
use ONGR\ElasticsearchDSL\Query\MatchAllQuery;
use ONGR\ElasticsearchDSL\Search;
use ValueObjects\Number\Natural;
use ValueObjects\StringLiteral\StringLiteral;

abstract class AbstractElasticSearchQueryBuilder implements QueryBuilderInterface
{
    /**
     * @var Search
     */
    protected $search;

    /**
     * @var BoolQuery
     */
    protected $boolQuery;

    public function __construct()
    {
        $this->boolQuery = new BoolQuery();
        $this->boolQuery->add(new MatchAllQuery(), BoolQuery::MUST);

        $this->search = new Search();
        $this->search->addQuery($this->boolQuery);

        $this->search->setFrom(0);
        $this->search->setSize(30);
    }

    /**
     * @inheritdoc
     */
    public function withAdvancedQuery(AbstractQueryString $queryString, Language ...$textLanguages)
    {
        return $this->withQueryStringQuery(
            $queryString,
            ...$textLanguages
        );
    }

    /**
     * @inheritdoc
     */
    public function withTextQuery(StringLiteral $text, Language ...$textLanguages)
    {
        $queryString = str_replace(':', '\\:', $text->toNative());

        return $this->withQueryStringQuery(
            new StringLiteral($queryString),
            ...$textLanguages
        );
    }

    /**
     * @inheritdoc
     */
    public function withStart(Natural $start)
    {
        $c = $this->getClone();
        $c->search->setFrom($start->toNative());
        return $c;
    }

    /**
     * @inheritdoc
     */
    public function withLimit(Natural $limit)
    {
        $c = $this->getClone();
        $c->search->setSize($limit->toNative());
        return $c;
    }

    /**
     * @return Search
     */
    public function build()
    {
        return $this->search;
    }

    /**
     * @param Language[] $languages
     * @return string[]
     */
    abstract protected function getPredefinedQueryStringFields(Language ...$languages);

    /**
     * @return static
     */
    protected function getClone()
    {
        $deepCopy = new DeepCopy();
        return $deepCopy->copy($this);
    }

    /**
     * @param string $fieldName
     * @param string $term
     * @return static
     */
    protected function withMatchQuery($fieldName, $term)
    {
        $matchQuery = new MatchQuery($fieldName, $term);

        $c = $this->getClone();
        $c->boolQuery->add($matchQuery, BoolQuery::FILTER);
        return $c;
    }

    /**
     * @param string $fieldName
     * @param string $term
     * @return static
     */
    protected function withMatchPhraseQuery($fieldName, $term)
    {
        $matchPhraseQuery = new MatchPhraseQuery($fieldName, $term);

        $c = $this->getClone();
        $c->boolQuery->add($matchPhraseQuery, BoolQuery::FILTER);
        return $c;
    }

    /**
     * @param StringLiteral $queryString
     * @param Language[] ...$languages
     * @return AbstractElasticSearchQueryBuilder
     */
    private function withQueryStringQuery(StringLiteral $queryString, Language ...$languages)
    {
        $fields = $this->getPredefinedQueryStringFields(...$languages);
        $queryStringQuery = new QueryStringQuery($queryString, ['fields' => $fields]);

        $c = $this->getClone();
        $c->boolQuery->add($queryStringQuery, BoolQuery::MUST);
        return $c;
    }
}
