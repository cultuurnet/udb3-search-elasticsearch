<?php

namespace CultuurNet\UDB3\Search\ElasticSearch\Aggregation;

use CultuurNet\UDB3\Language;
use CultuurNet\UDB3\Search\Facet\FacetFilter;
use CultuurNet\UDB3\Search\Facet\FacetNode;
use CultuurNet\UDB3\Search\Facet\FacetTreeInterface;
use CultuurNet\UDB3\Search\Offer\FacetName;
use CultuurNet\UDB3\ValueObject\MultilingualString;
use ValueObjects\StringLiteral\StringLiteral;

class LabelsAggregationTransformer implements AggregationTransformerInterface
{
    /**
     * @var FacetName
     */
    private $facetName;

    public function __construct(
        FacetName $facetName
    ) {
        $this->facetName = $facetName;
    }

    /**
     * @param Aggregation $aggregation
     * @return bool
     */
    public function supports(Aggregation $aggregation)
    {
        return $aggregation->getName()->sameValueAs($this->facetName);
    }

    /**
     * @param Aggregation $aggregation
     * @return FacetTreeInterface
     */
    public function toFacetTree(Aggregation $aggregation)
    {
        if (!$this->supports($aggregation)) {
            $name = $aggregation->getName()->toNative();
            throw new \LogicException("Aggregation $name not supported for transformation.");
        }

        $nodes = [];
        foreach ($aggregation->getBuckets() as $bucket) {
            if ($bucket->getCount() == 0) {
                continue;
            }

            $nodes[] = new FacetNode(
                $bucket->getKey(),
                new MultilingualString(
                    new Language('nl'),
                    new StringLiteral($bucket->getKey())
                ),
                $bucket->getCount()
            );
        }

        return new FacetFilter($this->facetName->toNative(), $nodes);
    }
}
