<?php

namespace CultuurNet\UDB3\Search\ElasticSearch\Offer;

use CultuurNet\UDB3\Search\ElasticSearch\IdUrlParserInterface;
use CultuurNet\UDB3\Search\ElasticSearch\JsonDocument\CopyJson\AbstractCopyJSONLD;
use CultuurNet\UDB3\Search\ElasticSearch\JsonDocument\CopyJson\CopyJsonRelatedOrganizer;
use CultuurNet\UDB3\Search\ElasticSearch\JsonDocument\CopyJson\CopyJsonTerms;
use CultuurNet\UDB3\Search\ElasticSearch\JsonDocument\CopyJson\FallbackType;
use Psr\Log\LoggerInterface;

abstract class AbstractCopyOffer extends AbstractCopyJSONLD
{
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
        parent::__construct($logger, $idUrlParser, $fallbackType);

        $this->copyJsonTerms = new CopyJsonTerms();

        $this->copyJsonRelatedOrganizer = new CopyJsonRelatedOrganizer(
            $this->getLogger(),
            $this->getIdUrlParser(),
            FallbackType::ORGANIZER()
        );
    }

    /**
     * @param \stdClass $from
     * @param \stdClass $to
     */
    public function copy(\stdClass $from, \stdClass $to)
    {
        parent::copy($from, $to);

        $this->copyJsonTerms->copy($from, $to);

        $this->copyJsonRelatedOrganizer->copy($from, $to);
    }
}
