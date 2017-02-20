<?php

namespace CultuurNet\UDB3\Search\ElasticSearch\Region;

use CultuurNet\UDB3\Search\ElasticSearch\HasElasticSearchClient;
use CultuurNet\UDB3\Search\ElasticSearch\Validation\ElasticSearchResponseValidatorInterface;
use CultuurNet\UDB3\Search\ElasticSearch\Validation\SuggestionsResponseValidator;
use CultuurNet\UDB3\Search\Region\RegionId;
use CultuurNet\UDB3\Search\Region\RegionSearchServiceInterface;
use Elasticsearch\Client;
use ONGR\ElasticsearchDSL\Search;
use ONGR\ElasticsearchDSL\Suggest\Suggest;
use ValueObjects\Number\Natural;
use ValueObjects\StringLiteral\StringLiteral;

class ElasticSearchRegionSearchService implements RegionSearchServiceInterface
{
    use HasElasticSearchClient;

    /**
     * @var ElasticSearchResponseValidatorInterface
     */
    private $responseValidator;

    /**
     * @param Client $elasticSearchClient
     * @param StringLiteral $indexName
     * @param StringLiteral $documentType
     * @param ElasticSearchResponseValidatorInterface $responseValidator
     */
    public function __construct(
        Client $elasticSearchClient,
        StringLiteral $indexName,
        StringLiteral $documentType,
        ElasticSearchResponseValidatorInterface $responseValidator = null
    ) {
        if (is_null($responseValidator)) {
            $responseValidator = new SuggestionsResponseValidator(['regions']);
        }

        $this->elasticSearchClient = $elasticSearchClient;
        $this->indexName = $indexName;
        $this->documentType = $documentType;
        $this->responseValidator = $responseValidator;
    }

    /**
     * @inheritdoc
     * @see https://www.elastic.co/blog/you-complete-me
     */
    public function suggest(StringLiteral $input, Natural $maxSuggestions = null)
    {
        $suggestName = 'regions';
        $suggestType = 'completion';
        $suggestText = $input->toNative();
        $suggestField = 'name_suggest';
        $suggestParameters = [];

        if (!is_null($maxSuggestions)) {
            $suggestParameters['size'] = $maxSuggestions->toNative();
        }

        $suggest = new Suggest(
            $suggestName,
            $suggestType,
            $suggestText,
            $suggestField,
            $suggestParameters
        );

        $search = new Search();
        $search->addSuggest($suggest);

        // Set size of the actual results to zero, as we're only interested
        // in the options given by the suggest and not any actual executeSearch
        // results.
        $search->setSize(0);

        $results = $this->executeQuery($search->toArray());

        $this->responseValidator->validate($results);

        $regionIds = [];
        foreach ($results['suggest']['regions'][0]['options'] as $option) {
            $regionId = new RegionId($option['text']);
            $regionIds[] = $regionId;
        }

        return $regionIds;
    }
}
