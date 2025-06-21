<?php

declare(strict_types=1);

namespace norsk\api\app\persistence;

use InvalidArgumentException;
use norsk\api\app\response\ResponseCode;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Port::class)]
class PortTest extends TestCase
{
    public function testCanBeUsedAsIntFromString(): void
    {
        self::assertSame(3306, Port::fromInt(3306)->asInt());
    }


    public function testThrowsExceptionOnInvalidValue(): void
    {
        $this->expectExceptionObject(
            new InvalidArgumentException('Port is not set', ResponseCode::unprocessable->value)
        );
        Port::fromInt(0);
    }
}
