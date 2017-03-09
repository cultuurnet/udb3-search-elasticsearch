<?php

namespace CultuurNet\UDB3\Search\ElasticSearch\JsonDocument;

use CultuurNet\UDB3\ReadModel\JsonDocument;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Response;

class EmbeddedJsonDocumentTransformerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ClientInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $client;

    /**
     * @var EmbeddedJsonDocumentTransformer
     */
    private $transformer;

    public function setUp()
    {
        $this->client = $this->createMock(ClientInterface::class);
        $this->transformer = new EmbeddedJsonDocumentTransformer($this->client);
    }

    /**
     * @test
     */
    public function it_returns_the_original_document_if_no_remote_document_could_be_found()
    {
        $originalDocument = new JsonDocument(
            '5782f617-f91d-49bd-8da5-892dce98c68f',
            json_encode(
                [
                    '@id' => 'http://foo.io/events/5782f617-f91d-49bd-8da5-892dce98c68f',
                    '@type' => 'Event',
                ]
            )
        );

        $this->client->expects($this->once())
            ->method('request')
            ->with('GET', 'http://foo.io/events/5782f617-f91d-49bd-8da5-892dce98c68f')
            ->willReturn(new Response(404));

        $expectedDocument = $originalDocument;
        $actualDocument = $this->transformer->transform($originalDocument);

        $this->assertEquals($expectedDocument, $actualDocument);
    }

    /**
     * @test
     */
    public function it_returns_the_remote_document_if_found()
    {
        $originalDocument = new JsonDocument(
            '5782f617-f91d-49bd-8da5-892dce98c68f',
            json_encode(
                [
                    '@id' => 'http://foo.io/events/5782f617-f91d-49bd-8da5-892dce98c68f',
                    '@type' => 'Event',
                ]
            )
        );

        $remoteJson = json_encode(
            [
                '@id' => 'http://foo.io/events/5782f617-f91d-49bd-8da5-892dce98c68f',
                '@type' => 'Event',
                'name' => 'Punkfest',
            ]
        );

        $this->client->expects($this->once())
            ->method('request')
            ->with('GET', 'http://foo.io/events/5782f617-f91d-49bd-8da5-892dce98c68f')
            ->willReturn(new Response(200, [], $remoteJson));

        $expectedDocument = new JsonDocument(
            '5782f617-f91d-49bd-8da5-892dce98c68f',
            $remoteJson
        );

        $actualDocument = $this->transformer->transform($originalDocument);

        $this->assertEquals($expectedDocument, $actualDocument);
    }
}
