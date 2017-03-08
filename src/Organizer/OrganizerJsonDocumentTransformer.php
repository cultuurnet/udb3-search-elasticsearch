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

        // @todo Replace name_deprecated with same name property as offers, in III-1956.
        // @see https://jira.uitdatabank.be/browse/III-1956
        $newBody = new \stdClass();
        $newBody->{'@id'} = $body->{'@id'};
        $newBody->{'@type'} = 'Organizer';
        $newBody->name_deprecated = $body->name;
        $newBody->url = $body->url;

        return $jsonDocument->withBody($newBody);
    }
}
