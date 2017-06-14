<?php

namespace CultuurNet\UDB3\Search\ElasticSearch\JsonDocument;

class CopyJsonCompositeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CopyJsonComposite
     */
    private $copyJsonComposite;

    /**
     * @var CopyJsonInterface[]|\PHPUnit_Framework_MockObject_MockObject[]
     */
    private $jsonCopiers;

    protected function setUp()
    {
        $this->jsonCopiers = [
            $this->createMock(CopyJsonInterface::class),
            $this->createMock(CopyJsonInterface::class)
        ];

        $this->copyJsonComposite = new CopyJsonComposite(
            ...$this->jsonCopiers
        );
    }

    /**
     * @test
     */
    public function it_calls_copy_method_of_all_specified_copiers()
    {
        $from = new \StdClass();
        $from->isFrom = true;
        $from->isTo = false;

        $to = new \StdClass();
        $to->isFrom = false;
        $to->isTo = true;

        foreach ($this->jsonCopiers as $jsonCopier) {
            $jsonCopier->expects($this->once())
                ->method('copy')
                ->with($from, $to);
        }

        $this->copyJsonComposite->copy($from, $to);
    }
}
