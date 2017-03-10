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
     * @param IdUrlParserInterface $idUrlParser
     */
    public function __construct(
        IdUrlParserInterface $idUrlParser
    ) {
        $this->idUrlParser = $idUrlParser;
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
     */
    protected function copyName(\stdClass $from, \stdClass $to)
    {
        $to->name = new \stdClass();
        $to->name->nl = $from->name->nl;

        // Only copy over the languages that we know how to analyze.
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
     * @param \stdClass $from
     * @param \stdClass $to
     */
    protected function copyDescription(\stdClass $from, \stdClass $to)
    {
        // Only copy over the languages that we know how to analyze.
        if (isset($from->description)) {
            $to->description = new \stdClass();
        }

        if (isset($from->description->nl)) {
            $to->description->nl = $from->description->nl;
        }

        if (isset($from->description->fr)) {
            $to->description->fr = $from->description->fr;
        }

        if (isset($from->description->en)) {
            $to->description->en = $from->description->en;
        }

        if (isset($from->description->de)) {
            $to->description->de = $from->description->de;
        }
    }

    /**
     * @param \stdClass $from
     * @param \stdClass $to
     */
    protected function copyLabelsForFreeTextSearch(\stdClass $from, \stdClass $to)
    {
        $labels = $this->getLabels($from);

        if (!empty($labels)) {
            $to->labels_free_text = $labels;
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
            $to->terms_free_text = array_map(
                function (\stdClass $term) {
                    // Don't copy all properties, just those we're interested
                    // in.
                    $copy = new \stdClass();
                    $copy->id = $term->id;
                    $copy->label = $term->label;
                    return $copy;
                },
                $from->terms
            );
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

        $to->organizer->name = new \stdClass();
        $to->organizer->name->nl = $from->organizer->name;
    }
}
