<?php

declare(strict_types=1);

namespace norsk\api\infrastructure\persistence;

use InvalidArgumentException;
use norsk\api\infrastructure\persistence\Host;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Host::class)]
class HostTest extends TestCase
{
    public static function invalidHosts(): array
    {
        return [
            [' '],
            [''],
        ];
    }


    public function testCanBeUsedAsString(): void
    {
        self::assertEquals('localhost', Host::fromString('localhost')->asString());
    }


    #[DataProvider('invalidHosts')]
    public function testFailsIfHostIsNoValidString(string $invalidHosts): void
    {
        $this->expectExceptionObject(new InvalidArgumentException('Host is not set', 422));

        Host::fromString($invalidHosts);
    }
}
