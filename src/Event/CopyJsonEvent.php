<?php

namespace CultuurNet\UDB3\Search\ElasticSearch\Event;

use CultuurNet\UDB3\Search\ElasticSearch\IdUrlParserInterface;
use CultuurNet\UDB3\Search\ElasticSearch\JsonDocument\CopyJson\CopyJsonInterface;
use CultuurNet\UDB3\Search\ElasticSearch\JsonDocument\CopyJson\CopyJsonOffer;
use CultuurNet\UDB3\Search\ElasticSearch\JsonDocument\CopyJson\CopyJsonRelatedLocation;
use CultuurNet\UDB3\Search\ElasticSearch\JsonDocument\CopyJson\Components\FallbackType;
use CultuurNet\UDB3\Search\ElasticSearch\JsonDocument\CopyJson\Logging\CopyJsonLoggerInterface;

class CopyJsonEvent implements CopyJsonInterface
{
    /**
     * @var CopyJsonLoggerInterface
     */
    private $copyJsonOffer;

    /**
     * @var CopyJsonRelatedLocation
     */
    private $copyJsonRelatedLocation;

    /**
     * @param CopyJsonLoggerInterface $logger
     * @param IdUrlParserInterface $idUrlParser
     * @param FallbackType $fallbackType
     */
    public function __construct(
        CopyJsonLoggerInterface $logger,
        IdUrlParserInterface $idUrlParser,
        FallbackType $fallbackType
    ) {
        $this->copyJsonOffer = new CopyJsonOffer(
            $logger,
            $idUrlParser,
            FallbackType::EVENT()
        );

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
        $this->copyJsonOffer->copy($from, $to);

        $this->copyJsonRelatedLocation->copy($from, $to);
    }
}
