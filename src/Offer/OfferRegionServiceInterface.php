<?php

namespace CultuurNet\UDB3\Search\ElasticSearch\Offer;

use CultuurNet\UDB3\Offer\OfferType;
use CultuurNet\UDB3\ReadModel\JsonDocument;

interface OfferRegionServiceInterface
{
    /**
     * @param OfferType $offerType
     * @param JsonDocument $jsonDocument
     * @return \string[]
     */
    public function getRegionIds(OfferType $offerType, JsonDocument $jsonDocument);
}
