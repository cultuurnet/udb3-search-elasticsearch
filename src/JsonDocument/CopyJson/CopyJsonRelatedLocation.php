<?php

namespace CultuurNet\UDB3\Search\ElasticSearch\JsonDocument\CopyJson;

use CultuurNet\UDB3\Search\ElasticSearch\IdUrlParserInterface;
use Psr\Log\LoggerInterface;

class CopyJsonRelatedLocation implements CopyJsonInterface
{
    /**
     * @var CopyJsonIdentifier
     */
    private $copyJsonIdentifier;

    /**
     * @var CopyJsonName
     */
    private $copyJsonName;

    /**
     * @var CopyJsonTerms
     */
    private $copyJsonTerms;

    /**
     * @var CopyJsonLabels
     */
    private $copyJsonLabels;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param LoggerInterface $logger
     * @param IdUrlParserInterface $idUrlParser
     * @param FallbackType $fallbackType
     */
    public function __construct(
        LoggerInterface $logger,
        IdUrlParserInterface $idUrlParser,
        FallbackType $fallbackType
    ) {
        $this->logger = $logger;

        $this->copyJsonIdentifier = new CopyJsonIdentifier(
            $logger,
            $idUrlParser,
            $fallbackType
        );

        $this->copyJsonName = new CopyJsonName($logger);

        $this->copyJsonTerms = new CopyJsonTerms();

        $this->copyJsonLabels = new CopyJsonLabels();
    }

    /**
     * @inheritdoc
     */
    public function copy(\stdClass $from, \stdClass $to)
    {
        if (!isset($from->location)) {
            $this->logMissingExpectedField('location');
            return;
        }

        if (!isset($to->location)) {
            $to->location = new \stdClass();
        }

        $this->copyJsonIdentifier->copy($from->location, $to->location);

        $this->copyJsonName->copy($from->location, $to->location);

        $this->copyJsonTerms->copy($from->location, $to->location);

        $this->copyJsonLabels->copy($from->location, $to->location);
    }

    /**
     * @param $fieldName
     */
    private function logMissingExpectedField($fieldName)
    {
        $this->logger->warning("Missing expected field '{$fieldName}'.");
    }
}
