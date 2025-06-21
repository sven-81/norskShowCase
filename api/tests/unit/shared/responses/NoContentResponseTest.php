<?php

declare(strict_types=1);

namespace norsk\api\shared\responses;

use norsk\api\app\response\Url;
use norsk\api\tests\provider\TestHeader;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(NoContentResponse::class)]
class NoContentResponseTest extends TestCase
{
    use TestHeader;

    private const int STATUS_CODE = 204;

    private array $expectedHeader;

    private Url $url;


    protected function setUp(): void
    {
        $this->url = Url::by('http://url');
        $this->expectedHeader = $this->getTestHeaderAsHeaders($this->url);
    }


    public function testCanCreateWordListResponseForUpdatedWordSuccessfully(): void
    {
        $body = '';

        $response = NoContentResponse::updatedVocabularySuccessfully($this->url);

        self::assertEquals(self::STATUS_CODE, $response->getStatusCode());
        self::assertEquals($this->expectedHeader, $response->getHeaders());
        self::assertEquals($body, $response->getBody()->getContents());
    }


    public function testCanCreateWordListResponseForWordTrainedSuccessfully(): void
    {
        $body = '';

        $response = NoContentResponse::vocabularyTrainedSuccessfully($this->url);

        self::assertEquals(self::STATUS_CODE, $response->getStatusCode());
        self::assertEquals($this->expectedHeader, $response->getHeaders());
        self::assertEquals($body, $response->getBody()->getContents());
    }
}
