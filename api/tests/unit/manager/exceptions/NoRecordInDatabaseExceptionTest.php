<?php

declare(strict_types=1);

namespace norsk\api\manager\exceptions;

use norsk\api\app\response\ResponseCode;
use norsk\api\shared\Id;
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
