<?php

namespace CultuurNet\UDB3\Search\ElasticSearch\JsonDocument;

class CopyJsonComposite implements CopyJsonInterface
{
    /**
     * @var CopyJsonInterface[]
     */
    private $jsonCopiers;

    /**
     * @param CopyJsonInterface[] $jsonCopiers
     */
    public function __construct(CopyJsonInterface ...$jsonCopiers)
    {
        $this->jsonCopiers = $jsonCopiers;
    }

    /**
     * @param \stdClass $from
     * @param \stdClass $to
     */
    public function copy(\stdClass $from, \stdClass $to)
    {
        foreach ($this->jsonCopiers as $jsonCopier) {
            $jsonCopier->copy($from, $to);
        }
    }
}
