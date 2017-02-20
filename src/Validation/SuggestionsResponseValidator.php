<?php

namespace CultuurNet\UDB3\Search\ElasticSearch\Validation;

class SuggestionsResponseValidator implements ElasticSearchResponseValidatorInterface
{
    /**
     * @var array
     */
    private $suggestionNames;

    /**
     * @param array $suggestionNames
     */
    public function __construct(array $suggestionNames)
    {
        $this->suggestionNames = $suggestionNames;
    }

    /**
     * @param array $responseData
     * @throws InvalidElasticSearchResponseException
     */
    public function validate(array $responseData)
    {
        if (!isset($responseData['suggest'])) {
            throw new InvalidElasticSearchResponseException(
                "ElasticSearch response does not contain a 'suggest' property!"
            );
        }

        foreach ($this->suggestionNames as $name) {
            if (!isset($responseData['suggest'][$name])) {
                throw new InvalidElasticSearchResponseException(
                    "ElasticSearch response does not contain a 'suggest.{$name}' property!"
                );
            }

            foreach ($responseData['suggest'][$name] as $key => $suggestion) {
                if (!isset($responseData['suggest'][$name][$key]['options'])) {
                    throw new InvalidElasticSearchResponseException(
                        "ElasticSearch response does not contain a 'suggest.{$name}[{$key}].options' property!"
                    );
                }

                foreach ($responseData['suggest'][$name][$key]['options'] as $optionKey => $option) {
                    if (!isset($option['text'])) {
                        throw new InvalidElasticSearchResponseException(
                            "ElasticSearch response does not contain a "
                                . "'suggest.{$name}[{$key}].options[{$optionKey}].text' property!"
                        );
                    }
                }
            }
        }
    }
}
