<?php

namespace CultuurNet\UDB3\Search\ElasticSearch\Organizer;

use CultuurNet\UDB3\Address\PostalCode;
use CultuurNet\UDB3\Label\ValueObjects\LabelName;
use CultuurNet\UDB3\Language;
use CultuurNet\UDB3\Search\Creator;
use CultuurNet\UDB3\Search\ElasticSearch\AbstractElasticSearchQueryBuilder;
use CultuurNet\UDB3\Search\ElasticSearch\KnownLanguages;
use CultuurNet\UDB3\Search\Organizer\OrganizerQueryBuilderInterface;
use Stringy\Stringy;
use ValueObjects\Geography\Country;
use ValueObjects\StringLiteral\StringLiteral;
use ValueObjects\Web\Domain;
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

    /**
     * @inheritdoc
     */
    public function withDomainFilter(Domain $domain)
    {
        $domain = Stringy::create((string) $domain);
        $domain = $domain->removeLeft('www.');

        return $this->withTermQuery('domain', (string) $domain);
    }

    /**
     * @inheritdoc
     */
    public function withPostalCodeFilter(PostalCode $postalCode)
    {
        return $this->withMultiFieldMatchQuery(
            (new KnownLanguages())->fieldNames(
                'address.{{lang}}.postalCode'
            ),
            $postalCode->toNative()
        );
    }

    /**
     * @inheritdoc
     */
    public function withAddressCountryFilter(Country $country)
    {
        return $this->withMultiFieldMatchQuery(
            (new KnownLanguages())->fieldNames(
                'address.{{lang}}.addressCountry'
            ),
            $country->getCode()->toNative()
        );
    }

    /**
     * @inheritdoc
     */
    public function withCreatorFilter(Creator $creator)
    {
        return $this->withMatchQuery('creator', $creator->toNative());
    }

    /**
     * @inheritdoc
     */
    public function withLabelFilter(LabelName $label)
    {
        return $this->withMatchQuery('labels', $label->toNative());
    }
}
