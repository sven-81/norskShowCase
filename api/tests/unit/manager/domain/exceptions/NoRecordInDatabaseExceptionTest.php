<?php

declare(strict_types=1);

namespace norsk\api\manager\domain\exceptions;

use norsk\api\shared\domain\DomainExceptionCode;
use norsk\api\shared\domain\Id;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(NoRecordInDatabaseException::class)]
class NoRecordInDatabaseExceptionTest extends TestCase
{
    private NoRecordInDatabaseException $exception;


    protected function setUp(): void
    {
        $idStub = $this->createStub(Id::class);
        $idStub->method('asString')
            ->willReturn('1');

        $this->exception = new NoRecordInDatabaseException($idStub);
    }


    public function testExceptionMessage(): void
    {
        self::assertSame('No record found in database for id: 1', $this->exception->getMessage());
    }


    public function testExceptionCode(): void
    {
        self::assertSame(DomainExceptionCode::notFound->value, $this->exception->getCode());
    }
}
