<?php

declare(strict_types=1);

namespace norsk\api\user;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use RuntimeException;

#[CoversClass(Pepper::class)]
class PepperTest extends TestCase
{
    private const int CHAR_COUNT = 32;


    public function testCanCreatePepperWithValidString(): void
    {
        $validPepper = str_repeat('a', self::CHAR_COUNT);

        $pepper = Pepper::by($validPepper);

        $this->assertSame($validPepper, $pepper->asString());
    }


    public function testCannotCreatePepperWithEmptyString(): void
    {
        $this->expectExceptionObject(new RuntimeException('Pepper not set'));

        Pepper::by('');
    }


    public function testCannotCreatePepperWithShortString(): void
    {
        $this->expectExceptionObject(new RuntimeException('Pepper is too short'));

        Pepper::by('short');
    }
}
