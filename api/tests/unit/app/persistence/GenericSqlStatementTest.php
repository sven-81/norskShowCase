<?php

declare(strict_types=1);

namespace norsk\api\app\persistence;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(GenericSqlStatement::class)]
class GenericSqlStatementTest extends TestCase
{
    public function testCanBeUsedAsString(): void
    {
        self::assertSame('SomeQuery', GenericSqlStatement::create('SomeQuery')->asString());
    }
}
