<?php

namespace CultuurNet\UDB3\Search\ElasticSearch\JsonDocument\CopyJson\Components;

use CultuurNet\UDB3\Search\ElasticSearch\JsonDocument\CopyJson\CopyJsonInterface;
use CultuurNet\UDB3\Search\ElasticSearch\JsonDocument\CopyJson\Logging\CopyJsonLoggerInterface;

class CopyJsonWorkflowStatus implements CopyJsonInterface
{
    /**
     * @var CopyJsonLoggerInterface
     */
    private $logger;

    /**
     * @var string|null
     */
    private $default;

    /**
     * CopyJsonCreator constructor.
     *
     * @param CopyJsonLoggerInterface $logger
     * @param string|null $default
     */
    public function __construct(CopyJsonLoggerInterface $logger, string $default = null)
    {
        $this->logger = $logger;
        $this->default = $default;
    }

    /**
     * @inheritdoc
     */
    public function copy(\stdClass $from, \stdClass $to)
    {
        if (isset($from->workflowStatus)) {
            $to->workflowStatus = $from->workflowStatus;
        } elseif (!is_null($this->default)) {
            $to->workflowStatus = $this->default;
        } else {
            $this->logger->logMissingExpectedField('workflowStatus');
        }
    }
}
