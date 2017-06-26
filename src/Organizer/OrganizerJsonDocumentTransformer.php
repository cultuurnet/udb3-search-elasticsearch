<?php

namespace CultuurNet\UDB3\Search\ElasticSearch\Organizer;

use CultuurNet\UDB3\Language;
use CultuurNet\UDB3\Organizer\ReadModel\JSONLD\OrganizerJsonDocumentLanguageAnalyzer;
use CultuurNet\UDB3\ReadModel\JsonDocument;
use CultuurNet\UDB3\Search\ElasticSearch\IdUrlParserInterface;
use CultuurNet\UDB3\Search\ElasticSearch\JsonDocument\CopyJson\CopyJsonInterface;
use CultuurNet\UDB3\Search\ElasticSearch\JsonDocument\CopyJson\Components\FallbackType;
use CultuurNet\UDB3\Search\ElasticSearch\JsonDocument\CopyJson\Logging\CopyJsonPsrLogger;
use CultuurNet\UDB3\Search\JsonDocument\JsonDocumentTransformerInterface;
use Psr\Log\LoggerInterface;
use Rhumsaa\Uuid\Uuid;

class OrganizerJsonDocumentTransformer implements JsonDocumentTransformerInterface
{
    /**
     * @var CopyJsonInterface
     */
    private $jsonCopier;

    /**
     * @var OrganizerJsonDocumentLanguageAnalyzer
     */
    private $languageAnalyzer;

    public function __construct(
        IdUrlParserInterface $idUrlParser,
        LoggerInterface $logger
    ) {
        $this->jsonCopier = new CopyJsonOrganizer(
            new CopyJsonPsrLogger($logger),
            $idUrlParser,
            FallbackType::ORGANIZER()
        );

        $this->languageAnalyzer = new OrganizerJsonDocumentLanguageAnalyzer();
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

        if (isset($body->languages)) {
            $languages = $body->languages;
        } else {
            // @todo Change this else condition to log missing field when full
            // replay is done.
            // @replay_i18n
            // @see https://jira.uitdatabank.be/browse/III-2201
            // Use NIL uuid as it doesn't really matter here. The JsonDocument is
            // just a wrapper to pass the $newBody JSON to the language analyzer.
            $languages = $this->languageAnalyzer->determineAvailableLanguages($jsonDocument);
        }

        if (isset($body->completedLanguages)) {
            $completedLanguages = $body->completedLanguages;
        } else {
            // @todo Change this else condition to log missing field when full
            // replay is done.
            // @replay_i18n
            // @see https://jira.uitdatabank.be/browse/III-2201
            // Use NIL uuid as it doesn't really matter here. The JsonDocument is
            // just a wrapper to pass the $newBody JSON to the language analyzer.
            $completedLanguages = $this->languageAnalyzer->determineCompletedLanguages($jsonDocument);
        }

        $languageToString = function (Language $language) {
            return $language->getCode();
        };

        if (!empty($languages)) {
            $newBody->languages = array_map($languageToString, $languages);
        }

        if (!empty($completedLanguages)) {
            $newBody->completedLanguages = array_map($languageToString, $completedLanguages);
        }

        return $jsonDocument->withBody($newBody);
    }
}
