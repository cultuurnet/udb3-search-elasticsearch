<?php

namespace CultuurNet\UDB3\Search\ElasticSearch\Organizer;

use CultuurNet\UDB3\ReadModel\JsonDocument;
use CultuurNet\UDB3\Search\ElasticSearch\JsonDocument\CopyJsonInterface;
use CultuurNet\UDB3\Search\JsonDocument\JsonDocumentTransformerInterface;

class OrganizerJsonDocumentTransformer implements JsonDocumentTransformerInterface
{
    /**
     * @var CopyJsonInterface
     */
    private $jsonCopier;

    /**
     * OrganizerJsonDocumentTransformer constructor.
     * @param CopyJsonInterface $jsonCopier
     */
    public function __construct(CopyJsonInterface $jsonCopier)
    {
        $this->jsonCopier = $jsonCopier;
    }

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

        $this->jsonCopier->copy($body, $newBody);

        $newBody->url = $body->url;

        return $jsonDocument->withBody($newBody);
    }
}
