<?php

namespace CultuurNet\UDB3\Search\ElasticSearch\Aggregation;

use CultuurNet\UDB3\Search\Facet\FacetTreeInterface;

class CompositeAggregationTransformer implements AggregationTransformerInterface
{
    /**
     * @var AggregationTransformerInterface[]
     */
    private $transformers = [];

    /**
     * @param AggregationTransformerInterface $aggregationTransformer
     */
    public function register(AggregationTransformerInterface $aggregationTransformer)
    {
        $this->transformers[] = $aggregationTransformer;
    }

    /**
     * @param Aggregation $aggregation
     * @return bool
     */
    public function supports(Aggregation $aggregation)
    {
        foreach ($this->transformers as $transformer) {
            if ($transformer->supports($aggregation)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param Aggregation $aggregation
     * @return FacetTreeInterface
     * @throws \LogicException
     */
    public function toFacetTree(Aggregation $aggregation)
    {
        foreach ($this->transformers as $transformer) {
            if ($transformer->supports($aggregation)) {
                return $transformer->toFacetTree($aggregation);
            }
        }

        throw new \LogicException("Aggregation {$aggregation->getName()} not supported for transformation.");
    }
}
