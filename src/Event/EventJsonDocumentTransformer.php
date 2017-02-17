<?php

namespace CultuurNet\UDB3\Search\ElasticSearch\Event;

use CultuurNet\UDB3\ReadModel\JsonDocument;
use CultuurNet\UDB3\Search\JsonDocument\JsonDocumentTransformerInterface;

class EventJsonDocumentTransformer implements JsonDocumentTransformerInterface
{
    public function transform(JsonDocument $jsonDocument)
    {
        $body = $jsonDocument->getBody();
        $indexBody = new \stdClass();

        $indexBody->{"@id"} = $body->{"@id"};
        $indexBody->{"@type"} = 'Event';

        if (isset($body->location->geo)) {
            $indexBody->geo = new \stdClass();
            $indexBody->geo->type = 'Point';

            // Important! In GeoJSON, and therefore Elasticsearch, the correct coordinate order is longitude, latitude
            // (X, Y) within coordinate arrays. This differs from many Geospatial APIs (e.g., Google Maps) that
            // generally use the colloquial latitude, longitude (Y, X).
            // @see https://www.elastic.co/guide/en/elasticsearch/reference/current/geo-shape.html#input-structure
            $indexBody->geo->coordinates = [
                $body->location->geo->longitude,
                $body->location->geo->latitude,
            ];
        }

        return (new JsonDocument($jsonDocument->getId()))
            ->withBody($indexBody);
    }
}
