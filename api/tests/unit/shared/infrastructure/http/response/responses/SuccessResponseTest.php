<?php

declare(strict_types=1);

namespace norsk\api\shared\infrastructure\http\response\responses;

use norsk\api\shared\application\Json;
use norsk\api\shared\infrastructure\http\response\ResponseCode;
use norsk\api\shared\infrastructure\http\response\Url;
use norsk\api\tests\provider\TestHeader;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(SuccessResponse::class)]
class SuccessResponseTest extends TestCase
{
    use TestHeader;

    private array $expectedHeader;

    private Url $url;


    protected function setUp(): void
    {
        $this->url = Url::by('http://url');
        $this->expectedHeader = $this->getTestHeaderAsHeaders($this->url);
    }


    #[DataProvider('getSuccessResponse')]
    public function testCanCreateSuccessResponses(string $methodName, ?string $body, ?JSON $json): void
    {
        $response = SuccessResponse::$methodName($this->url, $json);

        self::assertEquals(ResponseCode::success->value, $response->getStatusCode());
        self::assertEquals($this->expectedHeader, $response->getHeaders());
        self::assertEquals($body, $response->getBody()->getContents());
    }


    public static function getSuccessResponse(): array
    {
        $body = '{"message":"some ManageWord"}';
        $json = Json::fromString($body);

        return [
            'deletedRecord' => ['deletedRecord', $body, $json],
            'loggedIn' => ['loggedIn', $body, $json],
            'corsOptions' => ['corsOptions', null, null],
        ];
    }
}
