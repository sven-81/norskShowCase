<?php

declare(strict_types=1);

namespace norsk\api\infrastructure\response\exceptionMapper;

use Exception;
use norsk\api\shared\infrastructure\http\request\Parameter;
use norsk\api\shared\infrastructure\http\response\exceptionMapper\LoginExceptionMapper;
use norsk\api\shared\infrastructure\http\response\ResponseCode;
use norsk\api\shared\infrastructure\http\response\Url;
use norsk\api\user\domain\exceptions\ParameterMissingException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Throwable;

#[CoversClass(LoginExceptionMapper::class)]
class LoginExceptionMapperTest extends TestCase
{
    private Url $url;


    protected function setUp(): void
    {
        $this->url = $this->createMock(Url::class);
    }


    #[DataProvider('exceptionProvider')]
    public function testMap(Throwable $throwable, int $expectedStatusCode): void
    {
        $response = LoginExceptionMapper::map($throwable, $this->url);

        $this->assertEquals($expectedStatusCode, $response->getStatusCode());
    }


    public static function exceptionProvider(): array
    {
        return [
            'ParameterMissingException' => [
                new ParameterMissingException(Parameter::by('missingParam')),
                ResponseCode::badRequest->value,
            ],
            'BadRequestException' => [
                new Exception('Bad request', ResponseCode::badRequest->value),
                ResponseCode::badRequest->value,
            ],
            'UnauthorizedException' => [
                new Exception('Unauthorized', ResponseCode::unauthorized->value),
                ResponseCode::unauthorized->value,
            ],
            'ForbiddenException' => [
                new Exception('Forbidden', ResponseCode::forbidden->value),
                ResponseCode::forbidden->value,
            ],
            'UnprocessableException' => [
                new Exception('Unprocessable', ResponseCode::unprocessable->value),
                ResponseCode::unprocessable->value,
            ],
            'DefaultException' => [
                new Exception('Server error'),
                ResponseCode::serverError->value,
            ],
        ];
    }
}
