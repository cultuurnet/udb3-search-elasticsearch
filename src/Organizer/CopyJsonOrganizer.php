<?php

namespace CultuurNet\UDB3\Search\ElasticSearch\Organizer;

use CultuurNet\UDB3\Organizer\ReadModel\JSONLD\OrganizerJsonDocumentLanguageAnalyzer;
use CultuurNet\UDB3\Search\ElasticSearch\IdUrlParserInterface;
use CultuurNet\UDB3\Search\ElasticSearch\JsonDocument\CopyJson\Components\CopyJsonAddress;
use CultuurNet\UDB3\Search\ElasticSearch\JsonDocument\CopyJson\Components\CopyJsonCreator;
use CultuurNet\UDB3\Search\ElasticSearch\JsonDocument\CopyJson\Components\CopyJsonIdentifier;
use CultuurNet\UDB3\Search\ElasticSearch\JsonDocument\CopyJson\Components\CopyJsonLanguages;
use CultuurNet\UDB3\Search\ElasticSearch\JsonDocument\CopyJson\Components\CopyOriginalEncodedJsonLd;
use CultuurNet\UDB3\Search\ElasticSearch\JsonDocument\CopyJson\CopyJsonInterface;
use CultuurNet\UDB3\Search\ElasticSearch\JsonDocument\CopyJson\Components\CopyJsonName;
use CultuurNet\UDB3\Search\ElasticSearch\JsonDocument\CopyJson\Components\FallbackType;
use CultuurNet\UDB3\Search\ElasticSearch\JsonDocument\CopyJson\Logging\CopyJsonLoggerInterface;

class CopyJsonOrganizer implements CopyJsonInterface
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
     * @var CopyJsonLanguages
     */
    private $copyJsonLanguages;

    /**
     * @var CopyJsonAddress
     */
    private $copyJsonAddress;
    
    
    private $copyJsonCreator;

    /**
     * @var CopyOriginalEncodedJsonLd
     */
    private $copyOriginalEncodedJsonLd;

    /**
     * @param CopyJsonLoggerInterface $logger
     * @param IdUrlParserInterface $idUrlParser
     */
    public function __construct(
        CopyJsonLoggerInterface $logger,
        IdUrlParserInterface $idUrlParser
    ) {
        $this->copyJsonIdentifier = new CopyJsonIdentifier(
            $logger,
            $idUrlParser,
            FallbackType::ORGANIZER()
        );

        $this->copyJsonName = new CopyJsonName($logger);

        $this->copyJsonLanguages = new CopyJsonLanguages(
            new OrganizerJsonDocumentLanguageAnalyzer()
        );

        $this->copyJsonAddress = new CopyJsonAddress($logger, false);

        $this->copyJsonCreator = new CopyJsonCreator($logger);

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

        $this->copyJsonLanguages->copy($from, $to);

        $this->copyJsonAddress->copy($from, $to);

        $this->copyJsonCreator->copy($from, $to);

        $this->copyOriginalEncodedJsonLd->copy($from, $to);
    }
}
