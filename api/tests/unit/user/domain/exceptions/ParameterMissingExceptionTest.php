<?php

declare(strict_types=1);

namespace norsk\api\user\exceptions;

use norsk\api\shared\infrastructure\http\request\Parameter;
use norsk\api\shared\infrastructure\http\response\ResponseCode;
use norsk\api\user\domain\exceptions\ParameterMissingException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ParameterMissingException::class)]
class ParameterMissingExceptionTest extends TestCase
{
    private ParameterMissingException $exception;


    public function testExceptionMessage(): void
    {
        $this->assertSame('Missing required parameter: testParameter', $this->exception->getMessage());
    }


    public function testExceptionCode(): void
    {
        $this->assertSame(ResponseCode::badRequest->value, $this->exception->getCode());
    }


    protected function setUp(): void
    {
        $parameterMock = $this->createMock(Parameter::class);
        $parameterMock->method('asString')
            ->willReturn('testParameter');

        $this->exception = new ParameterMissingException($parameterMock);
    }
}
