<?php

declare(strict_types=1);

namespace norsk\api\infrastructure\persistence;

use InvalidArgumentException;
use norsk\api\infrastructure\persistence\Password;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Password::class)]
class PasswordTest extends TestCase
{
    public static function invalidPasswords(): array
    {
        return [
            [' '],
            [''],
        ];
    }


    public function testCanBeUsedAsString(): void
    {
        self::assertEquals('secret', Password::fromString('secret')->asString());
    }


    #[DataProvider('invalidPasswords')]
    public function testFailsIfPasswordIsNoValidString(string $invalidHosts): void
    {
        $this->expectExceptionObject(new InvalidArgumentException('Password is not set', 422));

        Password::fromString($invalidHosts);
    }
}
