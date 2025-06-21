<?php

declare(strict_types=1);

namespace norsk\api\shared\responses;

use norsk\api\app\response\Url;
use norsk\api\shared\Json;
use norsk\api\tests\provider\TestHeader;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SuccessResponse::class)]
class SuccessResponseTest extends TestCase
{
    use TestHeader;

    private const int STATUS_CODE = 200;

    private array $expectedHeader;

    private Url $url;


    protected function setUp(): void
    {
        $this->url = Url::by('http://url');
        $this->expectedHeader = $this->getTestHeaderAsHeaders($this->url);
    }


    public function testCanCreateSuccessResponseForDeletedRecord(): void
    {
        $body = '{"message":"some ManageWord"}';

        $response = SuccessResponse::deletedRecord($this->url, Json::fromString($body));

        self::assertEquals(self::STATUS_CODE, $response->getStatusCode());
        self::assertEquals($this->expectedHeader, $response->getHeaders());
        self::assertEquals($body, $response->getBody()->getContents());
    }


    public function testCanCreateSuccessResponseForLoggedIn(): void
    {
        $body = '{"message":"some ManageWord"}';

        $response = SuccessResponse::loggedIn($this->url, Json::fromString($body));

        self::assertEquals(self::STATUS_CODE, $response->getStatusCode());
        self::assertEquals($this->expectedHeader, $response->getHeaders());
        self::assertEquals($body, $response->getBody()->getContents());
    }
}
