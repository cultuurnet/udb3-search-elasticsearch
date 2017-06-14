<?php

namespace CultuurNet\UDB3\Search\ElasticSearch\JsonDocument\CopyJson;

use CultuurNet\UDB3\Search\ElasticSearch\IdUrlParserInterface;
use Psr\Log\LoggerInterface;

class CopyJsonRelatedOrganizer extends AbstractCopyJSONLD
{
    /**
     * @var CopyJsonLabels
     */
    private $copyJsonLabels;

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

        $this->copyJsonLabels = new CopyJsonLabels();
    }

    /**
     * @param \stdClass $from
     * @param \stdClass $to
     */
    public function copy(\stdClass $from, \stdClass $to)
    {
        if (!isset($from->organizer)) {
            return;
        }

        if (!isset($to->organizer)) {
            $to->organizer = new \stdClass();
        }

        parent::copy($from->organizer, $to->organizer);

        $this->copyJsonLabels->copy($from->organizer, $to->organizer);
    }
}
