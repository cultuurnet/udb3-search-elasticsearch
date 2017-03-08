<?php

namespace CultuurNet\UDB3\Search\ElasticSearch\Event;

use CultuurNet\UDB3\Language;
use CultuurNet\UDB3\ReadModel\JsonDocument;
use CultuurNet\UDB3\Search\ElasticSearch\IdUrlParserInterface;
use CultuurNet\UDB3\Search\JsonDocument\JsonDocumentTransformerInterface;

/**
 * Converts Event JSON-LD to a format more ideal for searching.
 * Should be used when indexing Events.
 */
class EventJsonDocumentTransformer implements JsonDocumentTransformerInterface
{
    /**
     * @var IdUrlParserInterface
     */
    private $idUrlParser;

    /**
     * @var Language
     */
    private $defaultLanguageCode;

    /**
     * @param IdUrlParserInterface $idUrlParser
     * @param Language $defaultLanguageCode
     */
    public function __construct(
        IdUrlParserInterface $idUrlParser,
        Language $defaultLanguageCode
    ) {
        $this->idUrlParser = $idUrlParser;
        $this->defaultLanguageCode = $defaultLanguageCode;
    }

    /**
     * @param JsonDocument $jsonDocument
     * @return JsonDocument
     */
    public function transform(JsonDocument $jsonDocument)
    {
        $body = $jsonDocument->getBody();
        $newBody = new \stdClass();

        $this->copyIdentifiers($body, $newBody, 'Event');

        $this->copyName($body, $newBody, $this->defaultLanguageCode);
        $this->copyDescription($body, $newBody, $this->defaultLanguageCode);

        $this->copyLabels($body, $newBody);
        $this->copyTerms($body, $newBody);

        $this->copyPerformer($body, $newBody);

        $this->copyAddressAndGeoInformation($body->location, $newBody);

        $this->copyLocation($body, $newBody, $this->defaultLanguageCode);
        $this->copyOrganizer($body, $newBody);

        return $jsonDocument->withBody($newBody);
    }

    /**
     * @param \stdClass $from
     * @param \stdClass $to
     * @param $fallbackType
     */
    private function copyIdentifiers(\stdClass $from, \stdClass $to, $fallbackType)
    {
        $to->{"@id"} = $from->{"@id"};
        $to->{"@type"} = isset($from->{"@type"}) ? $from->{"@type"} : $fallbackType;
        $to->id = $this->idUrlParser->getIdFromUrl($from->{"@id"});
    }

    /**
     * @param \stdClass $from
     * @param \stdClass $to
     * @param Language $language
     */
    private function copyName(\stdClass $from, \stdClass $to, Language $language)
    {
        $lang = $language->getCode();
        $to->name = $from->name->{$lang};
    }

    /**
     * @param \stdClass $from
     * @param \stdClass $to
     * @param Language $language
     */
    private function copyDescription(\stdClass $from, \stdClass $to, Language $language)
    {
        $lang = $language->getCode();

        if (isset($from->description->{$lang})) {
            $to->description = $from->description->{$lang};
        }
    }

    /**
     * @param \stdClass $from
     * @param \stdClass $to
     */
    private function copyLabels(\stdClass $from, \stdClass $to)
    {
        $labels = $this->getLabels($from);

        if (!empty($labels)) {
            $to->labels = $labels;
        }
    }

    /**
     * @param \stdClass $object
     * @return array
     */
    private function getLabels(\stdClass $object)
    {
        $labels = [];

        if (isset($object->labels)) {
            $labels = array_merge($labels, $object->labels);
        }

        if (isset($object->hiddenLabels)) {
            $labels = array_merge($labels, $object->hiddenLabels);
        }

        return $labels;
    }

    /**
     * @param \stdClass $from
     * @param \stdClass $to
     */
    private function copyTerms(\stdClass $from, \stdClass $to)
    {
        if (isset($from->terms)) {
            $to->terms = $from->terms;
        }
    }

    /**
     * @param \stdClass $from
     * @param \stdClass $to
     */
    private function copyPerformer(\stdClass $from, \stdClass $to)
    {
        if (isset($from->performer) && is_array($from->performer)) {
            $to->performer = array_map(
                function ($performer) {
                    return $performer->name;
                },
                $from->performer
            );
        }
    }

    /**
     * @param \stdClass $from
     * @param \stdClass $to
     */
    private function copyAddressAndGeoInformation(\stdClass $from, \stdClass $to)
    {
        $to->addressLocality = $from->address->addressLocality;
        $to->postalCode = $from->address->postalCode;
        $to->streetAddress = $from->address->streetAddress;

        if (isset($from->geo)) {
            $to->geo = new \stdClass();
            $to->geo->type = 'Point';

            // Important! In GeoJSON, and therefore Elasticsearch, the correct coordinate order is longitude, latitude
            // (X, Y) within coordinate arrays. This differs from many Geospatial APIs (e.g., Google Maps) that
            // generally use the colloquial latitude, longitude (Y, X).
            // @see https://www.elastic.co/guide/en/elasticsearch/reference/current/geo-shape.html#input-structure
            $to->geo->coordinates = [
                $from->geo->longitude,
                $from->geo->latitude,
            ];
        }
    }

    /**
     * @param \stdClass $from
     * @param \stdClass $to
     * @param Language $language
     */
    private function copyLocation(\stdClass $from, \stdClass $to, Language $language)
    {
        if (!isset($to->location)) {
            $to->location = new \stdClass();
        }

        $this->copyIdentifiers($from->location, $to->location, 'Place');
        $this->copyName($from->location, $to->location, $language);
        $this->copyLabels($from->location, $to->location);
    }

    /**
     * @param \stdClass $from
     * @param \stdClass $to
     */
    private function copyOrganizer(\stdClass $from, \stdClass $to)
    {
        if (!isset($from->organizer)) {
            return;
        }

        if (!isset($to->organizer)) {
            $to->organizer = new \stdClass();
        }

        $this->copyIdentifiers($from->organizer, $to->organizer, 'Organizer');
        $to->organizer->name = $from->organizer->name;
        $this->copyLabels($from->organizer, $to->organizer);
    }
}
