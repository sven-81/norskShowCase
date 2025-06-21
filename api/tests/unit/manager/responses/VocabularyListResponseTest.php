<?php

declare(strict_types=1);

namespace norsk\api\manager\responses;

use norsk\api\app\response\Url;
use norsk\api\shared\Json;
use norsk\api\tests\provider\TestHeader;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(VocabularyListResponse::class)]
class VocabularyListResponseTest extends TestCase
{
    private const int STATUS_CODE = 200;

    private array $expectedHeader;

    private Url $url;

    use TestHeader;


    protected function setUp(): void
    {
        $this->url = Url::by('http://url');
        $this->expectedHeader = $this->getTestHeaderAsHeaders($this->url);
    }


    public function testCanCreateVocabularyListResponse(): void
    {
        $body = '{"message":"some ManageWord"}';

        $response = VocabularyListResponse::create($this->url, Json::fromString($body));

        self::assertEquals(self::STATUS_CODE, $response->getStatusCode());
        self::assertEquals($this->expectedHeader, $response->getHeaders());
        self::assertEquals($body, $response->getBody()->getContents());
    }
}
