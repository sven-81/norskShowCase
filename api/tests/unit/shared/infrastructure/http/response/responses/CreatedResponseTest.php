<?php

declare(strict_types=1);

namespace norsk\api\shared\infrastructure\http\response\responses;

use norsk\api\shared\infrastructure\http\response\ResponseCode;
use norsk\api\shared\infrastructure\http\response\Url;
use norsk\api\tests\provider\TestHeader;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(CreatedResponse::class)]
class CreatedResponseTest extends TestCase
{
    use TestHeader;

    private array $expectedHeader;

    private Url $url;


    protected function setUp(): void
    {
        $this->url = Url::by('http://url');
        $this->expectedHeader = $this->getTestHeaderAsHeaders($this->url);
    }


    public function testCanCreateCreatedResponseForSavedWord(): void
    {
        $body = '{}';

        $response = CreatedResponse::savedVocabulary($this->url);

        self::assertEquals(ResponseCode::created->value, $response->getStatusCode());
        self::assertEquals($this->expectedHeader, $response->getHeaders());
        self::assertEquals($body, $response->getBody()->getContents());
    }


    public function testCanCreateCreatedResponseForSavedNewUser(): void
    {
        $body = '{}';

        $response = CreatedResponse::savedNewUser($this->url);

        self::assertEquals(ResponseCode::created->value, $response->getStatusCode());
        self::assertEquals($this->expectedHeader, $response->getHeaders());
        self::assertEquals($body, $response->getBody()->getContents());
    }
}
