<?php

namespace CultuurNet\UDB3\Search\ElasticSearch\JsonDocument\CopyJson;

use Psr\Log\LoggerInterface;

class CopyJsonName implements CopyJsonInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * CopyJsonName constructor.
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function copy(\stdClass $from, \stdClass $to)
    {
        $to->name = new \stdClass();

        // @see https://jira.uitdatabank.be/browse/III-2201
        // @replay_i18n Use $jsonLd->mainLanguage to get the required name field.
        if (isset($from->name->nl)) {
            $to->name->nl = $from->name->nl;
        } else if (isset($from->name) && is_string($from->name)) {
            // @replay_i18n For old projections the name is untranslated and just a string.
            // When a full replay is done this code becomes obsolete.
            $to->name->nl = $from->name;
            // No other languages possible, so already return.
            return;
        } else {
            $this->logMissingExpectedField('name.nl');
        }

        // @todo: The list of known languages gets bigger.
        // @see https://jira.uitdatabank.be/browse/III-2161 (es and it)
        if (isset($from->name->fr)) {
            $to->name->fr = $from->name->fr;
        }

        if (isset($from->name->en)) {
            $to->name->en = $from->name->en;
        }

        if (isset($from->name->de)) {
            $to->name->de = $from->name->de;
        }
    }

    /**
     * @param $fieldName
     */
    private function logMissingExpectedField($fieldName)
    {
        $this->logger->warning("Missing expected field '{$fieldName}'.");
    }
}
