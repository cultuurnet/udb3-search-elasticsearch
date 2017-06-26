<?php

namespace CultuurNet\UDB3\Search\ElasticSearch\Place;

use CultuurNet\UDB3\Place\ReadModel\JSONLD\PlaceJsonDocumentLanguageAnalyzer;
use CultuurNet\UDB3\Search\ElasticSearch\IdUrlParserInterface;
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
            FallbackType::PLACE()
        );

        $this->copyJsonLanguages = new CopyJsonLanguages(
            new PlaceJsonDocumentLanguageAnalyzer()
        );
    }

    /**
     * @param \stdClass $from
     * @param \stdClass $to
     */
    public function copy(\stdClass $from, \stdClass $to)
    {
        $this->copyJsonOffer->copy($from, $to);
        $this->copyJsonLanguages->copy($from, $to);
    }
}
