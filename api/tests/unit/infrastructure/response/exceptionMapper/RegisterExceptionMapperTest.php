<?php

declare(strict_types=1);

namespace norsk\api\infrastructure\response\exceptionMapper;

use Exception;
use norsk\api\shared\infrastructure\http\response\exceptionMapper\RegisterExceptionMapper;
use norsk\api\shared\infrastructure\http\response\ResponseCode;
use norsk\api\shared\infrastructure\http\response\Url;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Throwable;

#[CoversClass(RegisterExceptionMapper::class)]
class RegisterExceptionMapperTest extends TestCase
{
    private Url $url;

    private const int DUPLICATE_KEY = 1062;


    protected function setUp(): void
    {
        $this->url = $this->createMock(Url::class);
    }


    #[DataProvider('exceptionProvider')]
    public function testMap(Throwable $throwable, int $expectedStatusCode): void
    {
        $response = RegisterExceptionMapper::map($throwable, $this->url);
        $this->assertEquals($expectedStatusCode, $response->getStatusCode());
    }


    public static function exceptionProvider(): array
    {
        return [
            'BadRequestException' => [
                new Exception('Bad request', ResponseCode::badRequest->value),
                ResponseCode::badRequest->value,
            ],
            'UnprocessableException' => [
                new Exception('Unprocessable', ResponseCode::unprocessable->value),
                ResponseCode::unprocessable->value,
            ],
            'DuplicateKeyException' => [
                new Exception('Duplicate key', self::DUPLICATE_KEY),
                ResponseCode::conflict->value,
            ],
            'DefaultException' => [
                new Exception('Server error'),
                ResponseCode::serverError->value,
            ],
        ];
    }
}
