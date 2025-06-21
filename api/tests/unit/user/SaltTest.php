<?php

declare(strict_types=1);

namespace norsk\api\user;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use RuntimeException;

#[CoversClass(Salt::class)]
class SaltTest extends TestCase
{
    public function testCanCreateSaltWithValue(): void
    {
        $salt = Salt::init();
        $salt->generate();
        $setSalt = $salt->asString();

        self::assertSame(64, strlen($setSalt));
    }


    public function testCanCreateSaltFromString(): void
    {
        $salt = Salt::by('1234567890abcdefghijklmnopqrstuvwxyz1234567890');

        self::assertSame('1234567890abcdefghijklmnopqrstuvwxyz1234567890', $salt->asString());
    }


    public function testThrowsExceptionIfSaltIsEmpty(): void
    {
        $this->expectExceptionObject(
            new InvalidArgumentException('Salt is empty')
        );

        Salt::by('');
    }


    public function testThrowsExceptionIfSaltIsTooShort(): void
    {
        $this->expectExceptionObject(
            new InvalidArgumentException('Salt does not have at least 32 characters')
        );

        Salt::by('123');
    }


    public function testVerifiesSaltIs64CharsLong(): void
    {
        $salt = Salt::init();
        $salt->generate();
        $setSalt = $salt->asString();

        self::assertSame(64, strlen($setSalt));
    }


    public function testThrowsExceptionIfSaltIsUsedEmpty(): void
    {
        $this->expectExceptionObject(new RuntimeException('Salt not set'));

        Salt::init()->asString();
    }
}
