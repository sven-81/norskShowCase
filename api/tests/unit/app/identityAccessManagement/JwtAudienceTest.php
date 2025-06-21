<?php

declare(strict_types=1);

namespace norsk\api\app\identityAccessManagement;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(JwtAudience::class)]
class JwtAudienceTest extends TestCase
{
    public function testCanBeUsedAsString(): void
    {
        self::assertEquals('foo', JwtAudience::by('foo')->asString());
    }


    public function testThrowsExceptionIfFirstNameIsEmpty(): void
    {
        $this->expectExceptionObject(new InvalidArgumentException('Audience in JWT cannot be empty.'));
        JwtAudience::by('   ');
    }
}
