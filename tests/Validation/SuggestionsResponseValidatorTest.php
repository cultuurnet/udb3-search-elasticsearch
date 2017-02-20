<?php

namespace CultuurNet\UDB3\Search\ElasticSearch\Validation;

class SuggestionsResponseValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SuggestionsResponseValidator
     */
    private $validator;

    public function setUp()
    {
        $this->validator = new SuggestionsResponseValidator(
            [
                'suggest1',
                'suggest2',
            ]
        );
    }

    /**
     * @test
     */
    public function it_does_not_throw_an_exception_when_a_valid_response_is_given()
    {
        $response = [
            'suggest' => [
                'suggest1' => [
                    0 => [
                        'options' => [
                            ['text' => 'test1'],
                            ['text' => 'test2'],
                        ]
                    ],
                ],
                'suggest2' => [
                    0 => [
                        'options' => [
                            ['text' => 'foo1'],
                            ['text' => 'foo2'],
                        ]
                    ],
                ],
            ],
        ];

        $this->validator->validate($response);
    }

    /**
     * @test
     * @dataProvider invalidResponseDataProvider
     *
     * @param string $expectedExceptionMessage
     * @param array $responseData
     */
    public function it_throws_an_exception_when_a_required_property_is_missing(
        $expectedExceptionMessage,
        array $responseData
    ) {
        $this->expectException(InvalidElasticSearchResponseException::class);
        $this->expectExceptionMessage($expectedExceptionMessage);

        $this->validator->validate($responseData);
    }

    /**
     * @return array
     */
    public function invalidResponseDataProvider()
    {
        return [
            'missing_suggest' => [
                "ElasticSearch response does not contain a 'suggest' property!",
                [
                    'error' => 'Oops!',
                ],
            ],
            'missing_suggest_name' => [
                "ElasticSearch response does not contain a 'suggest.suggest2' property!",
                [
                    'suggest' => [
                        'suggest1' => [
                            0 => [
                                'options' => [
                                    ['text' => 'test1'],
                                    ['text' => 'test2'],
                                ]
                            ],
                        ],
                    ],
                ],
            ],
            'missing_option' => [
                "ElasticSearch response does not contain a 'suggest.suggest2[0].options' property!",
                [
                    'suggest' => [
                        'suggest1' => [
                            0 => [
                                'options' => [
                                    ['text' => 'test1'],
                                    ['text' => 'test2'],
                                ]
                            ],
                        ],
                        'suggest2' => [
                            0 => [],
                        ],
                    ],
                ],
            ],
            'missing_option_text' => [
                "ElasticSearch response does not contain a 'suggest.suggest2[0].options[1].text' property!",
                [
                    'suggest' => [
                        'suggest1' => [
                            0 => [
                                'options' => [
                                    ['text' => 'test1'],
                                    ['text' => 'test2'],
                                ]
                            ],
                        ],
                        'suggest2' => [
                            0 => [
                                'options' => [
                                    ['text' => 'foo1'],
                                    [],
                                ]
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
