<?php

declare(strict_types=1);

namespace norsk\api\user\responses;

use GuzzleHttp\Psr7\Response;
use norsk\api\app\response\ResponseHeaders;
use norsk\api\app\response\Url;
use norsk\api\shared\responses\ConflictResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ConflictResponse::class)]
class ConflictResponseTest extends TestCase
{
    private Response $expected;


    protected function setUp(): void
    {
        $url = Url::by('http://url');
        $this->expected = new Response(
            409,
            ResponseHeaders::create($url)->asArray(),
            '{}'
        );

        $this->response = ConflictResponse::create($url);
    }


    private Response $response;


    public function testCanCreateResponseWithCorrectStatusCode(): void
    {
        self::assertEquals(
            $this->expected->getStatusCode(),
            $this->response->getStatusCode()
        );
    }


    public function testCanCreateResponseWithCorrectHeaders(): void
    {
        self::assertEquals(
            $this->expected->getHeaders(),
            $this->response->getHeaders()
        );
    }


    public function testCanCreateResponseWithCorrectBody(): void
    {
        self::assertEquals(
            $this->expected->getBody()->getContents(),
            $this->response->getBody()->getContents()
        );
    }
}
