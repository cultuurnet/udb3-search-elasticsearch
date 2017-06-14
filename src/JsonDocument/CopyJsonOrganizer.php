<?php

namespace CultuurNet\UDB3\Search\ElasticSearch\JsonDocument;

use Psr\Log\LoggerInterface;

class CopyJsonOrganizer implements CopyJsonInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var CopyJsonComposite
     */
    private $copyJsonComposite;

    /**
     * CopyJsonOrganizer constructor.
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;

        $this->copyJsonComposite = new CopyJsonComposite(
            new CopyJsonName($this->logger)
        );
    }

    /**
     * @param \stdClass $from
     * @param \stdClass $to
     */
    public function copy(\stdClass $from, \stdClass $to)
    {
        $this->copyJsonComposite->copy($from, $to);
    }
}
