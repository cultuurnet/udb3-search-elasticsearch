<?php

namespace CultuurNet\UDB3\Search\ElasticSearch\JsonDocument\CopyJson;

use CultuurNet\UDB3\Search\ElasticSearch\IdUrlParserInterface;
use Psr\Log\LoggerInterface;

class CopyJsonRelatedLocation extends AbstractCopyJSONLD
{
    /**
     * @var CopyJsonTerms
     */
    private $copyJsonTerms;

    /**
     * @var CopyJsonLabels
     */
    private $copyJsonLabels;

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
        parent::__construct($logger, $idUrlParser, $fallbackType);

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

        parent::copy($from->location, $to->location);

        $this->copyJsonTerms->copy($from->location, $to->location);

        $this->copyJsonLabels->copy($from->location, $to->location);
    }

    /**
     * @param $fieldName
     */
    private function logMissingExpectedField($fieldName)
    {
        $this->getLogger()->warning("Missing expected field '{$fieldName}'.");
    }
}
