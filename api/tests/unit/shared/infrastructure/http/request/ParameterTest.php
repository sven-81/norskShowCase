<?php

declare(strict_types=1);

namespace norsk\api\shared\infrastructure\http\request;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Parameter::class)]
class ParameterTest extends TestCase
{
    public static function invalidParameter(): array
    {
        return [
            [' '],
            [''],
        ];
    }


    public function testCanBeUsedAsString(): void
    {
        self::assertEquals('foo', Parameter::by('foo')->asString());
    }


    #[DataProvider('invalidParameter')]
    public function testFailsIfParameterIsNoValidString(string $invalidParameter): void
    {
        $this->expectExceptionObject(new InvalidArgumentException('Parameter cannot be empty.', 422));

        Parameter::by($invalidParameter);
    }
}
