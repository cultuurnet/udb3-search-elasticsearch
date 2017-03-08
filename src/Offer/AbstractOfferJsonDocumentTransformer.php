<?php

namespace CultuurNet\UDB3\Search\ElasticSearch\Offer;

use CultuurNet\UDB3\Language;
use CultuurNet\UDB3\Search\ElasticSearch\IdUrlParserInterface;
use CultuurNet\UDB3\Search\JsonDocument\JsonDocumentTransformerInterface;

abstract class AbstractOfferJsonDocumentTransformer implements JsonDocumentTransformerInterface
{
    /**
     * @var IdUrlParserInterface
     */
    protected $idUrlParser;

    /**
     * @var Language
     */
    protected $defaultLanguageCode;

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
     * @param \stdClass $from
     * @param \stdClass $to
     * @param $fallbackType
     */
    protected function copyIdentifiers(\stdClass $from, \stdClass $to, $fallbackType)
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
    protected function copyName(\stdClass $from, \stdClass $to, Language $language)
    {
        $lang = $language->getCode();
        $to->name = $from->name->{$lang};
    }

    /**
     * @param \stdClass $from
     * @param \stdClass $to
     * @param Language $language
     */
    protected function copyDescription(\stdClass $from, \stdClass $to, Language $language)
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
    protected function copyLabels(\stdClass $from, \stdClass $to)
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
    protected function getLabels(\stdClass $object)
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
    protected function copyTerms(\stdClass $from, \stdClass $to)
    {
        if (isset($from->terms)) {
            $to->terms = $from->terms;
        }
    }

    /**
     * @param \stdClass $from
     * @param \stdClass $to
     */
    protected function copyAddressAndGeoInformation(\stdClass $from, \stdClass $to)
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
     */
    protected function copyOrganizer(\stdClass $from, \stdClass $to)
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
