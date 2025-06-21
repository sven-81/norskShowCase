<?php

declare(strict_types=1);

namespace norsk\api\app\persistence;

use InvalidArgumentException;
use norsk\application\Host;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(User::class)]
class UserTest extends TestCase
{
    public static function invalidUser(): array
    {
        return [
            [' '],
            [''],
        ];
    }


    public function testCanBeUsedAsString(): void
    {
        self::assertEquals('someUser', User::fromString('someUser')->asString());
    }


    #[DataProvider('invalidUser')]
    public function testFailsIfUserIsNoValidString(string $invalidUser): void
    {
        $this->expectExceptionObject(new InvalidArgumentException('User is not set', 422));

        User::fromString($invalidUser);
    }
}
