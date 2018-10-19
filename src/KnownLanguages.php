<?php

namespace CultuurNet\UDB3\Search\ElasticSearch;

class KnownLanguages
{
    public function fieldNames($field_pattern): array
    {
        // @todo: The list of known languages gets bigger.
        // @see https://jira.uitdatabank.be/browse/III-2161 (es and it)
        $knownLanguages = ['nl', 'fr', 'de', 'en'];

        return array_map(
            function ($languageCode) use ($field_pattern) {
                return str_replace('{{lang}}', $languageCode, $field_pattern);
            },
            $knownLanguages
        );
    }
}
