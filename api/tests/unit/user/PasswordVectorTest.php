<?php

declare(strict_types=1);

namespace norsk\api\user;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(PasswordVector::class)]
class PasswordVectorTest extends TestCase
{
    private Pepper $pepper;

    private Salt $salt;

    private PasswordVector $passwordVector;


    public function testCanGetSalt(): void
    {
        self::assertSame($this->salt, $this->passwordVector->getSalt());
    }


    public function testCanGetPepper(): void
    {
        self::assertSame($this->pepper, $this->passwordVector->getPepper());
    }


    protected function setUp(): void
    {
        $this->salt = Salt::by('some_salt_value_that_is_36_char_long');
        $this->pepper = Pepper::by('some_pepper_value_that_is_36_char_long');

        $this->passwordVector = PasswordVector::by($this->salt, $this->pepper);
    }
}
