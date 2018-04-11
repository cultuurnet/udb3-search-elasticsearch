<?php

namespace CultuurNet\UDB3\Search\ElasticSearch\Organizer;

use CultuurNet\UDB3\ReadModel\JsonDocument;
use CultuurNet\UDB3\Search\ElasticSearch\IdUrlParserInterface;
use CultuurNet\UDB3\Search\ElasticSearch\JsonDocument\CopyJson\CopyJsonInterface;
use CultuurNet\UDB3\Search\ElasticSearch\JsonDocument\CopyJson\Logging\CopyJsonPsrLogger;
use CultuurNet\UDB3\Search\JsonDocument\JsonDocumentTransformerInterface;
use Psr\Log\LoggerInterface;

class OrganizerJsonDocumentTransformer implements JsonDocumentTransformerInterface
{
    /**
     * @var CopyJsonInterface
     */
    private $jsonCopier;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        IdUrlParserInterface $idUrlParser,
        LoggerInterface $logger
    ) {
        $this->logger = $logger;

        $this->jsonCopier = new CopyJsonOrganizer(
            new CopyJsonPsrLogger($this->logger),
            $idUrlParser
        );
    }

    /**
     * @param JsonDocument $jsonDocument
     * @return JsonDocument
     */
    public function transform(JsonDocument $jsonDocument)
    {
        $body = $jsonDocument->getBody();

        $newBody = new \stdClass();

        $this->jsonCopier->copy($body, $newBody);

        $newBody->url = $body->url;

        $this->copyAddress($body, $newBody);

        return $jsonDocument->withBody($newBody);
    }

    /**
     * @param \stdClass $from
     * @param \stdClass $to
     */
    protected function copyAddress(\stdClass $from, \stdClass $to)
    {
        // Address is not a required field for an organizer.
        if (isset($from->address)) {
            $mainLanguage = isset($from->mainLanguage) ? $from->mainLanguage : 'nl';

            if (isset($from->address->streetAddress)) {
                // Old JSON-LD does not have a multilingual address.
                $from->address = (object)[$mainLanguage => $from->address];
            }

            $addressLanguages = array_keys(get_object_vars($from->address));
            $fields = ['addressCountry', 'addressLocality', 'postalCode', 'streetAddress'];
            $copiedAddresses = [];

            foreach ($addressLanguages as $addressLanguage) {
                $address = $from->address->{$addressLanguage};
                $copiedAddress = [];

                foreach ($fields as $field) {
                    if (!isset($address->{$field})) {
                        $this->logMissingExpectedField("address.{$addressLanguage}.{$field}");
                        continue;
                    }

                    $copiedAddress[$field] = $address->{$field};
                }

                if (!empty($copiedAddress)) {
                    $copiedAddresses[$addressLanguage] = (object)$copiedAddress;
                }
            }

            if (!empty($copiedAddresses)) {
                $to->address = (object)$copiedAddresses;
            }
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
