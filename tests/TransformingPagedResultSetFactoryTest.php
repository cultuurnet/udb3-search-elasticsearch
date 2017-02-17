<?php

namespace CultuurNet\UDB3\Search\ElasticSearch;

use CultuurNet\UDB3\ReadModel\JsonDocument;
use CultuurNet\UDB3\Search\JsonDocument\JsonDocumentTransformerInterface;
use CultuurNet\UDB3\Search\PagedResultSet;
use ValueObjects\Number\Natural;

class TransformingPagedResultSetFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var JsonDocumentTransformerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $transformer;

    /**
     * @var TransformingPagedResultSetFactory
     */
    private $factory;

    public function setUp()
    {
        $this->transformer = $this->createMock(JsonDocumentTransformerInterface::class);

        $this->factory = new TransformingPagedResultSetFactory(
            $this->transformer
        );
    }

    /**
     * @test
     */
    public function it_returns_a_paged_result_set_after_transforming_each_result()
    {
        $response = [
            'hits' => [
                'total' => 962,
                'hits' => [
                    [
                        '_index' => 'udb3-core',
                        '_type' => 'organizer',
                        '_id' => '351b85c1-66ea-463b-82a6-515b7de0d267',
                        '_source' => [
                            '@id' => 'http://foo.bar/organizers/351b85c1-66ea-463b-82a6-515b7de0d267',
                            'name' => 'Collectief Cursief',
                        ],
                    ],
                    [
                        '_index' => 'udb3-core',
                        '_type' => 'organizer',
                        '_id' => 'bdc0f4ce-a211-463e-a8d1-d8b699fb1159',
                        '_source' => [
                            '@id' => 'http://foo.bar/organizers/bdc0f4ce-a211-463e-a8d1-d8b699fb1159',
                            'name' => 'Anoniem Collectief',
                        ],
                    ],
                ]
            ],
        ];

        $perPage = new Natural(30);

        $this->transformer->expects($this->exactly(2))
            ->method('transform')
            ->willReturnCallback(
                function (JsonDocument $jsonDocument) {
                    return $jsonDocument;
                }
            );

        $expected = new PagedResultSet(
            new Natural(962),
            new Natural(30),
            [
                (new JsonDocument('351b85c1-66ea-463b-82a6-515b7de0d267'))
                    ->withBody(
                        (object) [
                            '@id' => 'http://foo.bar/organizers/351b85c1-66ea-463b-82a6-515b7de0d267',
                            'name' => 'Collectief Cursief',
                        ]
                    ),
                (new JsonDocument('bdc0f4ce-a211-463e-a8d1-d8b699fb1159'))
                    ->withBody(
                        (object) [
                            '@id' => 'http://foo.bar/organizers/bdc0f4ce-a211-463e-a8d1-d8b699fb1159',
                            'name' => 'Anoniem Collectief',
                        ]
                    ),
            ]
        );

        $actual = $this->factory->createPagedResultSet($perPage, $response);

        $this->assertEquals($expected, $actual);
    }
}
