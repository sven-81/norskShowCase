<?php

declare(strict_types=1);

namespace norsk\api\shared\responses;

use InvalidArgumentException;
use norsk\api\app\response\Url;
use norsk\api\tests\provider\TestHeader;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ErrorResponse::class)]
class ErrorResponseTest extends TestCase
{
    use TestHeader;

    private array $expectedHeader;

    private Url $url;


    protected function setUp(): void
    {
        $this->url = Url::by('http://url');
        $this->expectedHeader = $this->getTestHeaderAsHeaders($this->url);
    }


    public function testCanCreateErrorResponseForServerError(): void
    {
        $body = '{"message":"mööp"}';

        $response = ErrorResponse::serverError($this->url, new InvalidArgumentException('mööp'));

        self::assertEquals(500, $response->getStatusCode());
        self::assertEquals($this->expectedHeader, $response->getHeaders());
        self::assertEquals($body, $response->getBody()->getContents());
    }


    public function testCanCreateErrorResponseForUnprocessable(): void
    {
        $body = '{"message":"mööp"}';

        $response = ErrorResponse::unprocessable($this->url, new InvalidArgumentException('mööp'));

        self::assertEquals(422, $response->getStatusCode());
        self::assertEquals($this->expectedHeader, $response->getHeaders());
        self::assertEquals($body, $response->getBody()->getContents());
    }


    public function testCanCreateErrorResponseForUnauthorized(): void
    {
        $body = '{"message":"mööp"}';

        $response = ErrorResponse::unauthorized($this->url, new InvalidArgumentException('mööp'));

        self::assertEquals(401, $response->getStatusCode());
        self::assertEquals($this->expectedHeader, $response->getHeaders());
        self::assertEquals($body, $response->getBody()->getContents());
    }


    public function testCanCreateErrorResponseForConflict(): void
    {
        $body = '{"message":"mööp"}';

        $response = ErrorResponse::conflict($this->url, new InvalidArgumentException('mööp'));

        self::assertEquals(409, $response->getStatusCode());
        self::assertEquals($this->expectedHeader, $response->getHeaders());
        self::assertEquals($body, $response->getBody()->getContents());
    }


    public function testCanCreateErrorResponseForNotFound(): void
    {
        $body = '{"message":"mööp"}';

        $response = ErrorResponse::notFound($this->url, new InvalidArgumentException('mööp'));

        self::assertEquals(404, $response->getStatusCode());
        self::assertEquals($this->expectedHeader, $response->getHeaders());
        self::assertEquals($body, $response->getBody()->getContents());
    }


    public function testCanCreateErrorResponseForBadRequest(): void
    {
        $body = '{"message":"mööp"}';

        $response = ErrorResponse::badRequest($this->url, new InvalidArgumentException('mööp'));

        self::assertEquals(400, $response->getStatusCode());
        self::assertEquals($this->expectedHeader, $response->getHeaders());
        self::assertEquals($body, $response->getBody()->getContents());
    }


    public function testCanCreateErrorResponseForForbidden(): void
    {
        $body = '{"message":"mööp"}';

        $response = ErrorResponse::forbidden($this->url, new InvalidArgumentException('mööp'));

        self::assertEquals(403, $response->getStatusCode());
        self::assertEquals($this->expectedHeader, $response->getHeaders());
        self::assertEquals($body, $response->getBody()->getContents());
    }
}
