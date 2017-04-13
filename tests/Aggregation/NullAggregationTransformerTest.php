<?php

namespace CultuurNet\UDB3\Search\ElasticSearch\Aggregation;

class NullAggregationTransformerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var NullAggregationTransformer
     */
    private $transformer;

    public function setUp()
    {
        $this->transformer = new NullAggregationTransformer();
    }

    /**
     * @test
     */
    public function it_does_not_support_any_aggregation()
    {
        $aggregation = new Aggregation('mock');
        $this->assertFalse($this->transformer->supports($aggregation));
    }

    /**
     * @test
     */
    public function it_always_throws_a_logic_exception_when_trying_to_transform_an_aggregation()
    {
        $aggregation = new Aggregation('mock');
        $this->expectException(\LogicException::class);
        $this->transformer->toFacetTree($aggregation);
    }
}
