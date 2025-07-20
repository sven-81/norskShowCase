<?php

declare(strict_types=1);

namespace norsk\api\infrastructure\response\exceptionMapper;

use Exception;
use norsk\api\shared\infrastructure\http\response\exceptionMapper\TrainerExceptionMapper;
use norsk\api\shared\infrastructure\http\response\ResponseCode;
use norsk\api\shared\infrastructure\http\response\Url;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Throwable;

#[CoversClass(TrainerExceptionMapper::class)]
class TrainerExceptionMapperTest extends TestCase
{
    private Url $url;


    protected function setUp(): void
    {
        $this->url = $this->createMock(Url::class);
    }


    #[DataProvider('exceptionProvider')]
    public function testMap(Throwable $throwable, int $expectedStatusCode): void
    {
        $response = TrainerExceptionMapper::map($throwable, $this->url);
        $this->assertEquals($expectedStatusCode, $response->getStatusCode());
    }


    public static function exceptionProvider(): array
    {
        return [
            'BadRequestException' => [
                new Exception('Bad request', ResponseCode::badRequest->value),
                ResponseCode::badRequest->value,
            ],
            'NotFoundException' => [
                new Exception('Not Found', ResponseCode::notFound->value),
                ResponseCode::notFound->value,
            ],
            'DefaultException' => [
                new Exception('Server error'),
                ResponseCode::serverError->value,
            ],
        ];
    }
}
