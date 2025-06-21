<?php

declare(strict_types=1);

namespace norsk\api\app\response;

use norsk\api\tests\provider\TestHeader;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(UnauthorizedResponse::class)]
class UnauthorizedResponseTest extends TestCase
{
    use TestHeader;

    private const int ERROR_CODE = 401;

    private array $expectedHeader;

    private Url $url;


    protected function setUp(): void
    {
        $this->url = Url::by('http://url');
        $this->expectedHeader = $this->getTestHeaderAsHeaders($this->url);
    }


    public function testCanCreateAsNoHeaderResponse(): void
    {
        $expectedBody = '{"message":"No Authorization header sent"}';

        $response = UnauthorizedResponse::noHeader($this->url);
        self::assertEquals(self::ERROR_CODE, $response->getStatusCode());
        self::assertEquals($this->expectedHeader, $response->getHeaders());
        self::assertEquals($expectedBody, $response->getBody()->getContents());
    }


    public function testCanCreateAsNoRightsResponse(): void
    {
        $expectedBody = '{"message":"Unauthorized: No rights for managing words or verbs"}';

        $response = UnauthorizedResponse::noRights($this->url);
        self::assertEquals(self::ERROR_CODE, $response->getStatusCode());
        self::assertEquals($this->expectedHeader, $response->getHeaders());
        self::assertEquals($expectedBody, $response->getBody()->getContents());
    }
}
