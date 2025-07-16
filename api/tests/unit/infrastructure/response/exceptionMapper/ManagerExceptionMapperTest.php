<?php

declare(strict_types=1);

namespace norsk\api\infrastructure\response\exceptionMapper;

use Exception;
use norsk\api\shared\infrastructure\http\response\exceptionMapper\ManagerExceptionMapper;
use norsk\api\shared\infrastructure\http\response\ResponseCode;
use norsk\api\shared\infrastructure\http\response\Url;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Throwable;

#[CoversClass(ManagerExceptionMapper::class)]
class ManagerExceptionMapperTest extends TestCase
{
    private Url $url;


    protected function setUp(): void
    {
        $this->url = $this->createMock(Url::class);
    }


    #[DataProvider('exceptionProviderForCreate')]
    public function testMapForCreate(Throwable $throwable, int $expectedStatusCode): void
    {
        $response = ManagerExceptionMapper::mapForCreate($throwable, $this->url);
        $this->assertEquals($expectedStatusCode, $response->getStatusCode());
    }


    #[DataProvider('exceptionProviderForUpdate')]
    public function testMapForUpdate(Throwable $throwable, int $expectedStatusCode): void
    {
        $response = ManagerExceptionMapper::mapForUpdate($throwable, $this->url);
        $this->assertEquals($expectedStatusCode, $response->getStatusCode());
    }


    #[DataProvider('exceptionProviderForDelete')]
    public function testMapForDelete(Throwable $throwable, int $expectedStatusCode): void
    {
        $response = ManagerExceptionMapper::mapForDelete($throwable, $this->url);
        $this->assertEquals($expectedStatusCode, $response->getStatusCode());
    }


    public static function exceptionProviderForCreate(): array
    {
        return [
            'ConflictException' => [
                new Exception('Conflict', ResponseCode::conflict->value),
                ResponseCode::conflict->value,
            ],
            'DefaultException' => [
                new Exception('Server error'),
                ResponseCode::serverError->value,
            ],
        ];
    }


    public static function exceptionProviderForUpdate(): array
    {
        return [
            'ConflictException' => [
                new Exception('Conflict', ResponseCode::conflict->value),
                ResponseCode::conflict->value,
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


    public static function exceptionProviderForDelete(): array
    {
        return [
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
