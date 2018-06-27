<?php

namespace CultuurNet\UDB3\Search\ElasticSearch\JsonDocument\CopyJson;

use CultuurNet\UDB3\Search\ElasticSearch\IdUrlParserInterface;
use CultuurNet\UDB3\Search\ElasticSearch\JsonDocument\CopyJson\Components\CopyJsonIdentifier;
use CultuurNet\UDB3\Search\ElasticSearch\JsonDocument\CopyJson\Components\CopyJsonName;
use CultuurNet\UDB3\Search\ElasticSearch\JsonDocument\CopyJson\Components\CopyJsonTerms;
use CultuurNet\UDB3\Search\ElasticSearch\JsonDocument\CopyJson\Components\CopyJsonTypicalAgeRange;
use CultuurNet\UDB3\Search\ElasticSearch\JsonDocument\CopyJson\Components\CopyOriginalEncodedJsonLd;
use CultuurNet\UDB3\Search\ElasticSearch\JsonDocument\CopyJson\Components\FallbackType;
use CultuurNet\UDB3\Search\ElasticSearch\JsonDocument\CopyJson\Logging\CopyJsonLoggerInterface;

class CopyJsonOffer implements CopyJsonInterface
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
     * @var CopyJsonTypicalAgeRange
     */
    private $copyJsonTypicalAgeRange;

    /**
     * @var CopyJsonRelatedOrganizer
     */
    private $copyJsonRelatedOrganizer;

    private $copyOriginalEncodedJsonLd;

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
        $this->copyJsonIdentifier = new CopyJsonIdentifier(
            $logger,
            $idUrlParser,
            $fallbackType
        );

        $this->copyJsonName = new CopyJsonName($logger);

        $this->copyJsonTerms = new CopyJsonTerms();

        $this->copyJsonTypicalAgeRange = new CopyJsonTypicalAgeRange();

        $this->copyJsonRelatedOrganizer = new CopyJsonRelatedOrganizer(
            $logger,
            $idUrlParser,
            FallbackType::ORGANIZER()
        );

        $this->copyOriginalEncodedJsonLd = new CopyOriginalEncodedJsonLd();
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

        $this->copyJsonTypicalAgeRange->copy($from, $to);

        $this->copyJsonRelatedOrganizer->copy($from, $to);

        $this->copyOriginalEncodedJsonLd->copy($from, $to);
    }
}
