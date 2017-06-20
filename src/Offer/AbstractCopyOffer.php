<?php

namespace CultuurNet\UDB3\Search\ElasticSearch\Offer;

use CultuurNet\UDB3\Search\ElasticSearch\IdUrlParserInterface;
use CultuurNet\UDB3\Search\ElasticSearch\JsonDocument\CopyJson\CopyJsonIdentifier;
use CultuurNet\UDB3\Search\ElasticSearch\JsonDocument\CopyJson\CopyJsonName;
use CultuurNet\UDB3\Search\ElasticSearch\JsonDocument\CopyJson\CopyJsonRelatedOrganizer;
use CultuurNet\UDB3\Search\ElasticSearch\JsonDocument\CopyJson\CopyJsonTerms;
use CultuurNet\UDB3\Search\ElasticSearch\JsonDocument\CopyJson\FallbackType;
use Psr\Log\LoggerInterface;

abstract class AbstractCopyOffer
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
     * @var CopyJsonRelatedOrganizer
     */
    private $copyJsonRelatedOrganizer;

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
        $this->copyJsonIdentifier = new CopyJsonIdentifier(
            $logger,
            $idUrlParser,
            $fallbackType
        );

        $this->copyJsonName = new CopyJsonName($logger);

        $this->copyJsonTerms = new CopyJsonTerms();

        $this->copyJsonRelatedOrganizer = new CopyJsonRelatedOrganizer(
            $logger,
            $idUrlParser,
            FallbackType::ORGANIZER()
        );
    }

    /**
     * @param \stdClass $from
     * @param \stdClass $to
     */
    public function copy(\stdClass $from, \stdClass $to)
    {
        $this->copyJsonIdentifier->copy($from, $to);

        $this->copyJsonName->copy($from, $to);

        $this->copyJsonTerms->copy($from, $to);

        $this->copyJsonRelatedOrganizer->copy($from, $to);
    }
}
