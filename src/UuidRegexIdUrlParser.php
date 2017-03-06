<?php

namespace CultuurNet\UDB3\Search\ElasticSearch;

class UuidRegexIdUrlParser implements IdUrlParserInterface
{
    const UUID_REGEX = '/([0-9A-Fa-f]{8}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{12})/';

    /**
     * @param string $url
     * @return string
     */
    public function getIdFromUrl($url)
    {
        $matches = [];
        preg_match(self::UUID_REGEX, $url, $matches);

        if (!empty($matches[0])) {
            return (string) $matches[0];
        } else {
            throw new \RuntimeException('No uuid found in the given url.');
        }
    }
}
