<?php

namespace CultuurNet\UDB3\Search\ElasticSearch\Event;

use CultuurNet\UDB3\Offer\OfferType;
use CultuurNet\UDB3\ReadModel\JsonDocument;
use CultuurNet\UDB3\Search\ElasticSearch\Offer\AbstractOfferJsonDocumentTransformer;

/**
 * Converts Event JSON-LD to a format more ideal for searching.
 * Should be used when indexing Events.
 */
class EventJsonDocumentTransformer extends AbstractOfferJsonDocumentTransformer
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

        $this->logger->debug("Transforming event {$id} for indexation.");

        $this->copyIdentifiers($body, $newBody, 'Event');

        $this->copyCalendarType($body, $newBody);
        $this->copyDateRange($body, $newBody);

        $this->copyWorkflowStatus($body, $newBody);
        $this->copyAvailableRange($body, $newBody);

        $this->copyName($body, $newBody);
        $this->copyDescription($body, $newBody);

        $this->copyLanguages($body, $newBody);

        $this->copyTerms($body, $newBody);
        $this->copyTermsForFreeTextSearch($body, $newBody);
        $this->copyTermsForAggregations($body, $newBody);
        $this->copyLabels($body, $newBody);
        $this->copyLabelsForFreeTextSearch($body, $newBody);

        $this->copyPerformer($body, $newBody);
        $this->copyTypicalAgeRange($body, $newBody);
        $this->copyPriceInfo($body, $newBody);
        $this->copyAudienceType($body, $newBody);

        $this->copyMediaObjectsCount($body, $newBody);

        if (isset($body->location)) {
            $this->copyAddressAndGeoInformation($body->location, $newBody);

            $regionIds = $this->getRegionIds(
                OfferType::EVENT(),
                $jsonDocument->withBody($newBody)
            );

            if (!empty($regionIds)) {
                $newBody->regions = $regionIds;
            }

            $this->copyLocation($body, $newBody);
        } else {
            $this->logMissingExpectedField('location');
        }

        $this->copyOrganizer($body, $newBody);

        $this->copyMetadataDates($body, $newBody);

        $this->logger->debug("Transformation of event {$id} finished.");

        return $jsonDocument->withBody($newBody);
    }

    /**
     * @param \stdClass $from
     * @param \stdClass $to
     */
    private function copyPerformer(\stdClass $from, \stdClass $to)
    {
        if (isset($from->performer) && is_array($from->performer)) {
            $to->performer_free_text = array_map(
                function ($performer) {
                    // Don't copy all properties, just those we're interested
                    // in.
                    $newPerformer = new \stdClass();
                    $newPerformer->name = $performer->name;
                    return $newPerformer;
                },
                $from->performer
            );
        }
    }

    /**
     * @param \stdClass $from
     * @param \stdClass $to
     */
    private function copyLocation(\stdClass $from, \stdClass $to)
    {
        if (!isset($to->location)) {
            $to->location = new \stdClass();
        }

        $this->copyIdentifiers($from->location, $to->location, 'Place');
        $this->copyName($from->location, $to->location);
        $this->copyTerms($from->location, $to->location);
        $this->copyLabels($from->location, $to->location);
    }
}
