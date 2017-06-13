<?php

namespace CultuurNet\UDB3\Search\ElasticSearch\JsonDocument;

interface CopyJsonInterface
{
    /**
     * @param \stdClass $from
     * @param \stdClass $to
     */
    public function copy(\stdClass $from, \stdClass $to);
}
