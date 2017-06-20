<?php

namespace CultuurNet\UDB3\Search\ElasticSearch\Event;

use CultuurNet\UDB3\Search\ElasticSearch\IdUrlParserInterface;
use CultuurNet\UDB3\Search\ElasticSearch\Offer\AbstractCopyOffer;
use CultuurNet\UDB3\Search\ElasticSearch\JsonDocument\CopyJson\CopyJsonRelatedLocation;
use CultuurNet\UDB3\Search\ElasticSearch\JsonDocument\CopyJson\Components\FallbackType;
use Psr\Log\LoggerInterface;

class CopyJsonEvent extends AbstractCopyOffer
{
    /**
     * @var CopyJsonRelatedLocation
     */
    private $copyJsonRelatedLocation;

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

        $this->copyJsonRelatedLocation = new CopyJsonRelatedLocation(
            $logger,
            $idUrlParser,
            FallbackType::PLACE()
        );
    }

    /**
     * @param \stdClass $from
     * @param \stdClass $to
     */
    public function copy(\stdClass $from, \stdClass $to)
    {
        parent::copy($from, $to);

        $this->copyJsonRelatedLocation->copy($from, $to);
    }
}
