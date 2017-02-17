<?php

namespace CultuurNet\UDB3\Search\ElasticSearch\Place;

use CultuurNet\UDB3\ReadModel\JsonDocument;
use CultuurNet\UDB3\Search\JsonDocument\JsonDocumentTransformerInterface;

class PlaceJsonDocumentTransformer implements JsonDocumentTransformerInterface
{
    public function transform(JsonDocument $jsonDocument)
    {
        $body = $jsonDocument->getBody();
        $indexBody = new \stdClass();

        $indexBody->{"@id"} = $body->{"@id"};
        $indexBody->{"@type"} = 'Place';

        if (isset($body->geo)) {
            $indexBody->geo = new \stdClass();
            $indexBody->geo->type = 'Point';

            // Important! In GeoJSON, and therefore Elasticsearch, the correct coordinate order is longitude, latitude
            // (X, Y) within coordinate arrays. This differs from many Geospatial APIs (e.g., Google Maps) that
            // generally use the colloquial latitude, longitude (Y, X).
            // @see https://www.elastic.co/guide/en/elasticsearch/reference/current/geo-shape.html#input-structure
            $indexBody->geo->coordinates = [
                $body->geo->longitude,
                $body->geo->latitude,
            ];
        }

        return (new JsonDocument($jsonDocument->getId()))
            ->withBody($indexBody);
    }
}
