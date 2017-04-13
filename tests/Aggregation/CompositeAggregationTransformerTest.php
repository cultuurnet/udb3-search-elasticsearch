<?php

namespace CultuurNet\UDB3\Search\ElasticSearch\Aggregation;

use CultuurNet\UDB3\Search\Facet\FacetFilter;

class CompositeAggregationTransformerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AggregationTransformerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $transformer1;

    /**
     * @var AggregationTransformerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $transformer2;

    /**
     * @var string
     */
    private $aggregationNameSupportedByTransformer1;

    /**
     * @var string
     */
    private $aggregationNameSupportedByTransformer2;

    /**
     * @var string
     */
    private $aggregationNameSupportedByBoth;

    /**
     * @var CompositeAggregationTransformer
     */
    private $compositeTransformer;

    public function setUp()
    {
        $this->transformer1 = $this->createMock(AggregationTransformerInterface::class);
        $this->transformer2 = $this->createMock(AggregationTransformerInterface::class);

        $this->aggregationNameSupportedByTransformer1 = 'agg1';
        $this->aggregationNameSupportedByTransformer2 = 'agg2';
        $this->aggregationNameSupportedByBoth = 'agg*';

        $this->transformer1->expects($this->any())
            ->method('supports')
            ->willReturnCallback(
                function (Aggregation $aggregation) {
                    return $aggregation->getName() == $this->aggregationNameSupportedByTransformer1 ||
                        $aggregation->getName() == $this->aggregationNameSupportedByBoth;
                }
            );

        $this->transformer2->expects($this->any())
            ->method('supports')
            ->willReturnCallback(
                function (Aggregation $aggregation) {
                    return $aggregation->getName() == $this->aggregationNameSupportedByTransformer2 ||
                        $aggregation->getName() == $this->aggregationNameSupportedByBoth;
                }
            );

        $this->compositeTransformer = new CompositeAggregationTransformer();
        $this->compositeTransformer->register($this->transformer1);
        $this->compositeTransformer->register($this->transformer2);
    }

    /**
     * @test
     */
    public function it_supports_any_aggregation_supported_by_at_least_one_registered_transformer()
    {
        $supportedAggregation1 = new Aggregation($this->aggregationNameSupportedByTransformer1);
        $supportedAggregation2 = new Aggregation($this->aggregationNameSupportedByTransformer2);
        $supportedAggregation3 = new Aggregation($this->aggregationNameSupportedByBoth);
        $unsupportedAggregation = new Aggregation('not_supported');

        $this->assertTrue($this->compositeTransformer->supports($supportedAggregation1));
        $this->assertTrue($this->compositeTransformer->supports($supportedAggregation2));
        $this->assertTrue($this->compositeTransformer->supports($supportedAggregation3));
        $this->assertFalse($this->compositeTransformer->supports($unsupportedAggregation));
    }

    /**
     * @test
     */
    public function it_delegates_to_the_first_transformer_that_supports_the_aggregation()
    {
        $aggregation = new Aggregation($this->aggregationNameSupportedByBoth);
        $expectedFacetTree = new FacetFilter($this->aggregationNameSupportedByBoth);

        $this->transformer1->expects($this->once())
            ->method('toFacetTree')
            ->with($aggregation)
            ->willReturn($expectedFacetTree);

        $this->transformer2->expects($this->never())
            ->method('toFacetTree');

        $actualFacetTree = $this->compositeTransformer->toFacetTree($aggregation);

        $this->assertEquals($expectedFacetTree, $actualFacetTree);
    }

    /**
     * @test
     */
    public function it_works_without_any_registered_transformers_but_then_it_does_not_support_any_aggregation_at_all()
    {
        $aggregation = new Aggregation('mock');
        $transformer = new CompositeAggregationTransformer();

        $this->assertFalse($transformer->supports($aggregation));

        $this->expectException(\LogicException::class);
        $transformer->toFacetTree($aggregation);
    }
}
