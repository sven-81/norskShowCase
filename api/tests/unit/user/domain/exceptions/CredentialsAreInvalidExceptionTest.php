<?php

declare(strict_types=1);

namespace norsk\api\user\exceptions;

use norsk\api\shared\infrastructure\http\response\ResponseCode;
use norsk\api\user\domain\exceptions\CredentialsAreInvalidException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(CredentialsAreInvalidException::class)]
class CredentialsAreInvalidExceptionTest extends TestCase
{
    private CredentialsAreInvalidException $exception;


    public function testExceptionMessage(): void
    {
        $this->assertSame('Unauthorized: Cannot verify credentials', $this->exception->getMessage());
    }


    public function testExceptionCode(): void
    {
        $this->assertSame(ResponseCode::unauthorized->value, $this->exception->getCode());
    }


    protected function setUp(): void
    {
        $this->exception = new CredentialsAreInvalidException();
    }
}
