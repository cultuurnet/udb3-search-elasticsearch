<?php

namespace CultuurNet\UDB3\Search\ElasticSearch\Organizer;

use CultuurNet\UDB3\ReadModel\JsonDocument;
use CultuurNet\UDB3\Search\JsonDocument\JsonDocumentTransformerInterface;
use Psr\Log\LoggerInterface;

class OrganizerJsonDocumentTransformer implements JsonDocumentTransformerInterface
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param JsonDocument $jsonDocument
     * @return JsonDocument
     */
    public function transform(JsonDocument $jsonDocument)
    {
        $body = $jsonDocument->getBody();

        $newBody = new \stdClass();
        $newBody->{'@id'} = $body->{'@id'};
        $newBody->{'@type'} = 'Organizer';

        $this->copyName($body, $newBody);

        $newBody->url = $body->url;

        return $jsonDocument->withBody($newBody);
    }

    /**
     * @param \stdClass $from
     * @param \stdClass $to
     */
    protected function copyName(\stdClass $from, \stdClass $to)
    {
        $to->name = new \stdClass();

        // TODO: Use $jsonLd->mainLanguage to get the required name field.
        if (isset($from->name->nl)) {
            $to->name->nl = $from->name->nl;
        } else {
            $this->logMissingExpectedField('name.nl');
        }

        // TODO: The list of known languages gets bigger.
        // https://jira.uitdatabank.be/browse/III-2161 (es and it)
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
    protected function logMissingExpectedField($fieldName)
    {
        $this->logger->warning("Missing expected field '{$fieldName}'.");
    }
}
