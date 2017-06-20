<?php

namespace CultuurNet\UDB3\Search\ElasticSearch\JsonDocument\CopyJson\Components;

use CultuurNet\UDB3\Search\ElasticSearch\IdUrlParserInterface;
use CultuurNet\UDB3\Search\ElasticSearch\JsonDocument\CopyJson\CopyJsonInterface;
use CultuurNet\UDB3\Search\ElasticSearch\JsonDocument\CopyJson\Components\FallbackType;
use Psr\Log\LoggerInterface;

class CopyJsonIdentifier implements CopyJsonInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var IdUrlParserInterface
     */
    private $idUrlParser;

    /**
     * @var string
     */
    private $fallbackType;

    /**
     * CopyJsonName constructor.
     * @param LoggerInterface $logger
     * @param IdUrlParserInterface $idUrlParser
     * @param $fallbackType
     */
    public function __construct(
        LoggerInterface $logger,
        IdUrlParserInterface $idUrlParser,
        FallbackType $fallbackType
    ) {
        $this->logger = $logger;
        $this->idUrlParser = $idUrlParser;
        $this->fallbackType = $fallbackType;
    }

    /**
     * @param \stdClass $from
     * @param \stdClass $to
     */
    public function copy(\stdClass $from, \stdClass $to)
    {
        if (isset($from->{"@id"})) {
            $to->{"@id"} = $from->{"@id"};
        } else {
            $this->logMissingExpectedField("@id");
        }

        $to->{"@type"} = isset($from->{"@type"}) ? $from->{"@type"} :
            $this->fallbackType->toNative();

        // Not included in the if statement above because it should be under
        // @type in the JSON. No else statement because we don't want to log a
        // missing @id twice.
        if (isset($from->{"@id"})) {
            $to->id = $this->idUrlParser->getIdFromUrl($from->{"@id"});
        }
    }

    /**
     * @param $fieldName
     */
    private function logMissingExpectedField($fieldName)
    {
        $this->logger->warning("Missing expected field '{$fieldName}'.");
    }
}
