<?php

declare(strict_types=1);

namespace norsk\api\app\persistence;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(DatabaseName::class)]
class DbNameTest extends TestCase
{
    /**
     * @return array<int, array<int, string>>
     */
    public static function invalidNames(): array
    {
        return [
            [' '],
            [''],
        ];
    }


    public function testCanBeUsedAsString(): void
    {
        self::assertEquals('mats', DatabaseName::fromString('mats')->asString());
    }


    #[DataProvider('invalidNames')]
    public function testFailsIfDbNameIsNoValidString(string $invalidNames): void
    {
        $this->expectExceptionObject(new InvalidArgumentException('Name is not set', 422));

        DatabaseName::fromString($invalidNames);
    }
}
