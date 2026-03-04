<?php

declare(strict_types=1);

namespace norsk\api\user\infrastructure\identityAccessManagement\authentication;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(AuthenticationAlgorithm::class)]
class AuthenticationAlgorithmTest extends TestCase
{
    public function testCanBeUsedAsString(): void
    {
        $this->assertSame('ES256K', AuthenticationAlgorithm::by('ES256K')->asString());
    }


    public function testThrowsExceptionIfAlgorithmIsUnknown(): void
    {
        $this->expectExceptionObject(new InvalidArgumentException('Algorithm has no valid format'));

        AuthenticationAlgorithm::by('abx-982');
    }
}
