<?php

namespace CultuurNet\UDB3\Search\ElasticSearch\Place;

use CultuurNet\UDB3\Offer\OfferType;
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
        $id = $jsonDocument->getId();
        $body = $jsonDocument->getBody();
        $newBody = new \stdClass();

        $this->logger->debug("Transforming place {$id} for indexation.");

        $this->copyIdentifiers($body, $newBody, 'Place');

        $this->copyCalendarType($body, $newBody);
        $this->copyDateRange($body, $newBody);

        $this->copyWorkflowStatus($body, $newBody);
        $this->copyAvailableRange($body, $newBody);

        $this->copyName($body, $newBody);
        $this->copyDescription($body, $newBody);

        $this->copyLanguages($body, $newBody);

        $this->copyLabels($body, $newBody);
        $this->copyLabelsForFreeTextSearch($body, $newBody);
        $this->copyTerms($body, $newBody);
        $this->copyTermsForFreeTextSearch($body, $newBody);
        $this->copyTermsForAggregations($body, $newBody);

        $this->copyTypicalAgeRange($body, $newBody);
        $this->copyPriceInfo($body, $newBody);
        $this->copyAudienceType($body, $newBody);

        $this->copyMediaObjectsCount($body, $newBody);

        $this->copyAddressAndGeoInformation($body, $newBody);

        $regionIds = $this->getRegionIds(
            OfferType::PLACE(),
            $jsonDocument->withBody($newBody)
        );

        if (!empty($regionIds)) {
            $newBody->regions = $regionIds;
        }

        $this->copyOrganizer($body, $newBody);

        $this->copyMetadataDates($body, $newBody);

        $this->logger->debug("Transformation of place {$id} finished.");

        return $jsonDocument->withBody($newBody);
    }
}
