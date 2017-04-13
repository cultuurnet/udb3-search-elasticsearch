<?php

namespace CultuurNet\UDB3\Search\ElasticSearch\Aggregation;

/**
 * Aggregation result, from an ElasticSearch response.
 * NOT an aggregation query.
 */
class Aggregation
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var Bucket[]
     */
    private $buckets;

    /**
     * @param string $name
     * @param Bucket[] $buckets
     */
    public function __construct($name, Bucket ...$buckets)
    {
        if (!is_string($name)) {
            throw new \InvalidArgumentException('Aggregation name should be a string.');
        }

        $this->name = $name;

        foreach ($buckets as $bucket) {
            $this->buckets[$bucket->getKey()] = $bucket;
        }
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return Bucket[]
     */
    public function getBuckets()
    {
        return $this->buckets;
    }

    /**
     * @param string $name
     * @param array $aggregationData
     * @return Aggregation
     */
    public static function fromElasticSearchResponseAggregationData($name, array $aggregationData)
    {
        if (!isset($aggregationData['buckets'])) {
            throw new \InvalidArgumentException('Aggregation data does not contain any buckets.');
        }

        $buckets = array_map(
            function (array $bucket) {
                if (!isset($bucket['key'])) {
                    throw new \InvalidArgumentException('Bucket is missing a key.');
                }

                if (!isset($bucket['doc_count'])) {
                    throw new \InvalidArgumentException('Bucket is missing a doc_count.');
                }

                return new Bucket(
                    (string) $bucket['key'],
                    (int) $bucket['doc_count']
                );
            },
            $aggregationData['buckets']
        );

        return new Aggregation($name, ...$buckets);
    }
}
