<?php

namespace CultuurNet\UDB3\Search\ElasticSearch\Place;

use CultuurNet\UDB3\Place\ReadModel\JSONLD\PlaceJsonDocumentLanguageAnalyzer;
use CultuurNet\UDB3\Search\ElasticSearch\IdUrlParserInterface;
use CultuurNet\UDB3\Search\ElasticSearch\JsonDocument\CopyJson\Components\CopyJsonAddress;
use CultuurNet\UDB3\Search\ElasticSearch\JsonDocument\CopyJson\Components\CopyJsonLanguages;
use CultuurNet\UDB3\Search\ElasticSearch\JsonDocument\CopyJson\Components\FallbackType;
use CultuurNet\UDB3\Search\ElasticSearch\JsonDocument\CopyJson\CopyJsonInterface;
use CultuurNet\UDB3\Search\ElasticSearch\JsonDocument\CopyJson\CopyJsonOffer;
use CultuurNet\UDB3\Search\ElasticSearch\JsonDocument\CopyJson\Logging\CopyJsonLoggerInterface;

class CopyJsonPlace implements CopyJsonInterface
{
    /**
     * @var CopyJsonOffer
     */
    private $copyJsonOffer;

    /**
     * @var CopyJsonLanguages
     */
    private $copyJsonLanguages;

    /**
     * @var CopyJsonAddress
     */
    private $copyJsonAddress;

    /**
     * @param CopyJsonLoggerInterface $logger
     * @param IdUrlParserInterface $idUrlParser
     */
    public function __construct(
        CopyJsonLoggerInterface $logger,
        IdUrlParserInterface $idUrlParser
    ) {
        $this->copyJsonOffer = new CopyJsonOffer(
            $logger,
            $idUrlParser,
            FallbackType::PLACE()
        );

        $this->copyJsonLanguages = new CopyJsonLanguages(
            new PlaceJsonDocumentLanguageAnalyzer()
        );

        $this->copyJsonAddress = new CopyJsonAddress($logger, true);
    }

    /**
     * @param \stdClass $from
     * @param \stdClass $to
     */
    public function copy(\stdClass $from, \stdClass $to)
    {
        $this->copyJsonOffer->copy($from, $to);
        $this->copyJsonLanguages->copy($from, $to);
        $this->copyJsonAddress->copy($from, $to);
    }
}
