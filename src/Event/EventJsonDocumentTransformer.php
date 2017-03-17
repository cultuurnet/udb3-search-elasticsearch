<?php

namespace CultuurNet\UDB3\Search\ElasticSearch\Event;

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
        $body = $jsonDocument->getBody();
        $newBody = new \stdClass();

        $this->copyIdentifiers($body, $newBody, 'Event');

        $this->copyName($body, $newBody);
        $this->copyDescription($body, $newBody);

        $this->copyLabels($body, $newBody);
        $this->copyLabelsForFreeTextSearch($body, $newBody);
        $this->copyTerms($body, $newBody);

        $this->copyPerformer($body, $newBody);

        $this->copyAddressAndGeoInformation($body->location, $newBody);

        $this->copyLocation($body, $newBody);
        $this->copyOrganizer($body, $newBody);

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
        $this->copyLabels($from->location, $to->location);
    }
}
