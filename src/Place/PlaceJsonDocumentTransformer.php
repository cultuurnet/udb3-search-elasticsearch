<?php

namespace CultuurNet\UDB3\Search\ElasticSearch\Place;

use CultuurNet\UDB3\Offer\OfferType;
use CultuurNet\UDB3\ReadModel\JsonDocument;
use CultuurNet\UDB3\Search\ElasticSearch\IdUrlParserInterface;
use CultuurNet\UDB3\Search\ElasticSearch\JsonDocument\CopyJson\Components\FallbackType;
use CultuurNet\UDB3\Search\ElasticSearch\Offer\AbstractOfferJsonDocumentTransformer;
use CultuurNet\UDB3\Search\ElasticSearch\Offer\OfferRegionServiceInterface;
use Psr\Log\LoggerInterface;

/**
 * Converts Place JSON-LD to a format more ideal for searching.
 * Should be used when indexing Places.
 */
class PlaceJsonDocumentTransformer extends AbstractOfferJsonDocumentTransformer
{
    /**
     * @var CopyJsonPlace
     */
    private $copyJsonPlace;

    /**
     * PlaceJsonDocumentTransformer constructor.
     * @param IdUrlParserInterface $idUrlParser
     * @param OfferRegionServiceInterface $offerRegionService
     * @param LoggerInterface $logger
     */
    public function __construct(
        IdUrlParserInterface $idUrlParser,
        OfferRegionServiceInterface $offerRegionService,
        LoggerInterface $logger
    ) {
        parent::__construct($idUrlParser, $offerRegionService, $logger);

        $this->copyJsonPlace = new CopyJsonPlace(
            $this->logger,
            $this->idUrlParser,
            FallbackType::PLACE()
        );
    }

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

        $this->copyJsonPlace->copy($body, $newBody);

        $this->copyCalendarType($body, $newBody);
        $this->copyDateRange($body, $newBody);

        $this->copyWorkflowStatus($body, $newBody);
        $this->copyAvailableRange($body, $newBody);

        $this->copyDescription($body, $newBody);

        $this->copyMainLanguage($body, $newBody);
        $this->copyLanguages($body, $newBody);

        $this->copyLabels($body, $newBody);
        $this->copyLabelsForFreeTextSearch($body, $newBody);
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

        $this->copyCreated($body, $newBody);
        $this->copyModified($body, $newBody);
        $this->copyCreator($body, $newBody);

        $this->logger->debug("Transformation of place {$id} finished.");

        return $jsonDocument->withBody($newBody);
    }
}
