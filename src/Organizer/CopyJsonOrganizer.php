<?php

namespace CultuurNet\UDB3\Search\ElasticSearch\Organizer;

use CultuurNet\UDB3\Organizer\ReadModel\JSONLD\OrganizerJsonDocumentLanguageAnalyzer;
use CultuurNet\UDB3\Search\ElasticSearch\IdUrlParserInterface;
use CultuurNet\UDB3\Search\ElasticSearch\JsonDocument\CopyJson\Components\CopyJsonIdentifier;
use CultuurNet\UDB3\Search\ElasticSearch\JsonDocument\CopyJson\Components\CopyJsonLanguages;
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

        $this->copyJsonLanguages = new CopyJsonLanguages(
            new OrganizerJsonDocumentLanguageAnalyzer()
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

        $this->copyJsonLanguages->copy($from, $to);
    }
}
