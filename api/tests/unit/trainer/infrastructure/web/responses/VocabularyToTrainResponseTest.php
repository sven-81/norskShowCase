<?php

declare(strict_types=1);

namespace norsk\api\trainer\infrastructure\web\responses;

use norsk\api\shared\application\Json;
use norsk\api\shared\infrastructure\http\response\Url;
use norsk\api\tests\provider\TestHeader;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(VocabularyToTrainResponse::class)]
class VocabularyToTrainResponseTest extends TestCase
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


    public function testCanCreateCreatedResponseForSavedWord(): void
    {
        $body = '{}';

        $response = VocabularyToTrainResponse::create($this->url, Json::fromString($body));

        self::assertEquals(self::STATUS_CODE, $response->getStatusCode());
        self::assertEquals($this->expectedHeader, $response->getHeaders());
        self::assertEquals($body, $response->getBody()->getContents());
    }
}
