<?php

declare(strict_types=1);

namespace norsk\api\manager\domain\exceptions;

use norsk\api\shared\domain\Id;
use norsk\api\shared\infrastructure\http\response\ResponseCode;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(NoRecordInDatabaseException::class)]
class NoRecordInDatabaseExceptionTest extends TestCase
{
    private NoRecordInDatabaseException $exception;


    public function testExceptionMessage(): void
    {
        $this->assertSame('No record found in database for id: 1', $this->exception->getMessage());
    }


    public function testExceptionCode(): void
    {
        $this->assertSame(ResponseCode::notFound->value, $this->exception->getCode());
    }


    protected function setUp(): void
    {
        $idMock = $this->createMock(Id::class);
        $idMock->method('asString')
            ->willReturn('1');

        $this->exception = new NoRecordInDatabaseException($idMock);
    }
}
