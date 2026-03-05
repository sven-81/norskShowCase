<?php

declare(strict_types=1);

namespace norsk\api\trainer\domain\exceptions;

use norsk\api\shared\domain\DomainExceptionCode;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(NoRecordInDatabaseException::class)]
class NoRecordInDatabaseExceptionTest extends TestCase
{
    private NoRecordInDatabaseException $exception;


    protected function setUp(): void
    {
        $this->exception = new NoRecordInDatabaseException('No records found in database for: verbs');
    }


    public function testExceptionMessage(): void
    {
        self::assertSame('No records found in database for: verbs', $this->exception->getMessage());
    }


    public function testExceptionCode(): void
    {
        self::assertSame(DomainExceptionCode::notFound->value, $this->exception->getCode());
    }
}

