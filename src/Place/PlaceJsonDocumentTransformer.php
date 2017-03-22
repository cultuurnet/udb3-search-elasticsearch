<?php

namespace CultuurNet\UDB3\Search\ElasticSearch\Place;

use CultuurNet\UDB3\ReadModel\JsonDocument;
use CultuurNet\UDB3\Search\ElasticSearch\Offer\AbstractOfferJsonDocumentTransformer;

/**
 * Converts Place JSON-LD to a format more ideal for searching.
 * Should be used when indexing Places.
 */
class PlaceJsonDocumentTransformer extends AbstractOfferJsonDocumentTransformer
{
    /**
     * @param JsonDocument $jsonDocument
     * @return JsonDocument
     */
    public function transform(JsonDocument $jsonDocument)
    {
        $body = $jsonDocument->getBody();
        $newBody = new \stdClass();

        $this->copyIdentifiers($body, $newBody, 'Place');

        $this->copyName($body, $newBody);
        $this->copyDescription($body, $newBody);

        $this->copyLabels($body, $newBody);
        $this->copyLabelsForFreeTextSearch($body, $newBody);
        $this->copyTerms($body, $newBody);

        $this->copyTypicalAgeRange($body, $newBody);
        $this->copyPriceInfo($body, $newBody);

        $this->copyAddressAndGeoInformation($body, $newBody);

        $this->copyOrganizer($body, $newBody);

        return $jsonDocument->withBody($newBody);
    }
}
