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
        $lang = $this->defaultLanguageCode->getCode();

        $body = $jsonDocument->getBody();
        $newBody = new \stdClass();

        $this->copyIdentifiers($body, $newBody, 'Event');

        $newBody->name = $body->name->{$lang};

        if (isset($body->description->{$lang})) {
            $newBody->description = $body->description->{$lang};
        }

        $this->copyLabels($body, $newBody);

        if (isset($body->terms)) {
            $newBody->terms = $body->terms;
        }

        if (isset($body->performer)) {
            $newBody->performer = array_map(
                function ($performer) {
                    return $performer->name;
                },
                $body->performer
            );
        }

        $newBody->addressLocality = $body->location->address->addressLocality;
        $newBody->postalCode = $body->location->address->postalCode;
        $newBody->streetAddress = $body->location->address->streetAddress;

        if (isset($body->location->geo)) {
            $newBody->geo = new \stdClass();
            $newBody->geo->type = 'Point';

            // Important! In GeoJSON, and therefore Elasticsearch, the correct coordinate order is longitude, latitude
            // (X, Y) within coordinate arrays. This differs from many Geospatial APIs (e.g., Google Maps) that
            // generally use the colloquial latitude, longitude (Y, X).
            // @see https://www.elastic.co/guide/en/elasticsearch/reference/current/geo-shape.html#input-structure
            $newBody->geo->coordinates = [
                $body->location->geo->longitude,
                $body->location->geo->latitude,
            ];
        }

        $newBody->location = new \stdClass();
        $this->copyIdentifiers($body->location, $newBody->location, 'Place');
        $newBody->location->name = $body->location->name->{$lang};
        $this->copyLabels($body->location, $newBody->location);

        if (isset($body->organizer)) {
            $newBody->organizer = new \stdClass();
            $this->copyIdentifiers($body->organizer, $newBody->organizer, 'Organizer');
            $newBody->organizer->name = $body->organizer->name;
            $this->copyLabels($body->organizer, $newBody->organizer);
        }

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
}
