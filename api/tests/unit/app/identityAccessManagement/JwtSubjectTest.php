<?php

declare(strict_types=1);

namespace norsk\api\app\identityAccessManagement;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(JwtSubject::class)]
class JwtSubjectTest extends TestCase
{
    public function testCanBeUsedAsString(): void
    {
        self::assertEquals('foo', JwtSubject::by('foo')->asString());
    }


    public function testThrowsExceptionIfFirstNameIsEmpty(): void
    {
        $this->expectExceptionObject(new InvalidArgumentException('Subject in JWT cannot be empty.'));
        JwtSubject::by('   ');
    }
}
