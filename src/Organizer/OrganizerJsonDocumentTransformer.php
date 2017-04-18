<?php

namespace CultuurNet\UDB3\Search\ElasticSearch\Organizer;

use CultuurNet\UDB3\ReadModel\JsonDocument;
use CultuurNet\UDB3\Search\JsonDocument\JsonDocumentTransformerInterface;

class OrganizerJsonDocumentTransformer implements JsonDocumentTransformerInterface
{
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

        // Organizer names can not be translated at the moment, but we index
        // them as if they are multilingual to maintain compatibility with
        // events and places.
        $newBody->name = new \stdClass();
        $newBody->name->nl = $body->name;

        $newBody->url = $body->url;

        return $jsonDocument->withBody($newBody);
    }
}
