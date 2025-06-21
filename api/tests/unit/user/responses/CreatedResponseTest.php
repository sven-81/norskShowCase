<?php

declare(strict_types=1);

namespace norsk\api\user\responses;

use GuzzleHttp\Psr7\Response;
use norsk\api\app\response\ResponseCode;
use norsk\api\app\response\ResponseHeaders;
use norsk\api\app\response\Url;
use norsk\api\shared\responses\CreatedResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(CreatedResponse::class)]
class CreatedResponseTest extends TestCase
{
    private Response $expected;

    private Response $createdResponse;

    private Url $url;


    protected function setUp(): void
    {
        $this->url = Url::by('http://url');
        $this->expected = new Response(
            ResponseCode::created->value,
            ResponseHeaders::create(Url::by('http://url'))->asArray(),
            '{}'
        );

        $this->createdResponse = CreatedResponse::savedNewUser($this->url);
    }


    public function testCanCreateResponseWithCorrectStatusCode(): void
    {
        self::assertEquals(
            $this->expected->getStatusCode(),
            $this->createdResponse->getStatusCode()
        );
    }


    public function testCanCreateResponseWithCorrectHeaders(): void
    {
        self::assertEquals(
            $this->expected->getHeaders(),
            $this->createdResponse->getHeaders()
        );
    }


    public function testCanCreateResponseWithCorrectBody(): void
    {
        self::assertEquals(
            $this->expected->getBody()->getContents(),
            $this->createdResponse->getBody()->getContents()
        );
    }


    public function testCanBeCreatedBySavedWord(): void
    {
        $created = CreatedResponse::savedVocabulary($this->url);

        self::assertInstanceOf(Response::class, $created);
    }
}
