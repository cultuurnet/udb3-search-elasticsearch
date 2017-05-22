<?php

namespace CultuurNet\UDB3\Search\ElasticSearch\Organizer;

use CultuurNet\UDB3\Language;
use CultuurNet\UDB3\Search\ElasticSearch\AbstractElasticSearchQueryBuilder;
use CultuurNet\UDB3\Search\Organizer\OrganizerQueryBuilderInterface;
use ValueObjects\StringLiteral\StringLiteral;
use ValueObjects\Web\Url;

class ElasticSearchOrganizerQueryBuilder extends AbstractElasticSearchQueryBuilder implements
    OrganizerQueryBuilderInterface
{
    /**
     * @inheritdoc
     */
    protected function getPredefinedQueryStringFields(Language ...$languages)
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function withAutoCompleteFilter(StringLiteral $input)
    {
        // Currently not translatable, just look in the Dutch version for now.
        return $this->withMatchPhraseQuery('name.nl.autocomplete', $input->toNative());
    }

    /**
     * @inheritdoc
     */
    public function withWebsiteFilter(Url $url)
    {
        return $this->withMatchQuery('url', (string) $url);
    }
}
