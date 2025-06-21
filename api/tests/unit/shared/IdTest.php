<?php

declare(strict_types=1);

namespace norsk\api\shared;

use InvalidArgumentException;
use norsk\api\app\response\ResponseCode;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Id::class)]
class IdTest extends TestCase
{
    public function testCanBeUsedAsInt(): void
    {
        $this->assertSame(98, Id::by(98)->asInt());
    }


    public function testCanBeUsedAsString(): void
    {
        $this->assertSame('98', Id::fromString('98')->asString());
    }


    public function testThrowsAnExceptionIfIdIsNotNumeric(): void
    {
        $this->expectExceptionObject(
            new InvalidArgumentException(
                'Id has to be numeric: abc',
                ResponseCode::badRequest->value
            )
        );
        Id::fromString('abc');
    }
}
