<?php

namespace CultuurNet\UDB3\Search\ElasticSearch\JsonDocument\CopyJson;

use CultuurNet\UDB3\Search\ElasticSearch\IdUrlParserInterface;
use Psr\Log\LoggerInterface;

abstract class AbstractCopyJSONLD implements CopyJsonInterface
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
     * @var FallbackType
     */
    private $fallbackType;

    /**
     * @var CopyJsonName
     */
    private $copyJsonName;

    /**
     * @var CopyJsonIdentifier
     */
    private $copyJsonIdentifier;

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

        $this->copyJsonName = new CopyJsonName($this->logger);

        $this->copyJsonIdentifier = new CopyJsonIdentifier(
            $this->logger,
            $this->idUrlParser,
            $this->fallbackType
        );
    }

    /**
     * @inheritdoc
     */
    public function copy(\stdClass $from, \stdClass $to)
    {
        $this->copyJsonIdentifier->copy($from, $to);

        $this->copyJsonName->copy($from, $to);
    }

    /**
     * @return LoggerInterface
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * @return IdUrlParserInterface
     */
    public function getIdUrlParser()
    {
        return $this->idUrlParser;
    }

    /**
     * @return FallbackType
     */
    public function getFallbackType()
    {
        return $this->fallbackType;
    }
}
