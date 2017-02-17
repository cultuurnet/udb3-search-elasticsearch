<?php

namespace CultuurNet\UDB3\Search\ElasticSearch\JsonDocument;

use CultuurNet\UDB3\ReadModel\JsonDocument;
use CultuurNet\UDB3\Search\JsonDocument\JsonDocumentTransformerInterface;
use GuzzleHttp\ClientInterface;

class EmbeddedJsonDocumentTransformer implements JsonDocumentTransformerInterface
{
    /**
     * @var ClientInterface
     */
    private $guzzleClient;

    /**
     * @param ClientInterface $guzzleClient
     */
    public function __construct(
        ClientInterface $guzzleClient
    ) {
        $this->guzzleClient = $guzzleClient;
    }

    /**
     * @param JsonDocument $jsonDocument
     * @return JsonDocument
     */
    public function transform(JsonDocument $jsonDocument)
    {
        $body = $jsonDocument->getBody();
        $iri = $body->{'@id'};
        $type = $body->{'@type'};

        $response = $this->guzzleClient->request('GET', $iri);

        if ($response->getStatusCode() !== 200) {
            return $jsonDocument;
        }

        $newBody = json_decode($response->getBody());
        $newBody->{'@type'} = $type;

        return $jsonDocument->withBody($newBody);
    }
}
