<?php

declare(strict_types=1);

namespace norsk\api\user\infrastructure\identityAccessManagement\authentication;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(AuthenticationKey::class)]
class AuthenticationKeyTest extends TestCase
{
    public function testCanBeUsedAsString(): void
    {
        $this->assertSame('abx982', AuthenticationKey::by('abx982')->asString());
    }


    public function testCanBeUsedAsBase64String(): void
    {
        $this->assertSame('YWJ4OTgy', AuthenticationKey::by('abx982')->asBase64String());
    }
}
