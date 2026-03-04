<?php

declare(strict_types=1);

namespace norsk\api\user\infrastructure\identityAccessManagement\jwt;

use norsk\api\user\infrastructure\identityAccessManagement\authentication\AuthenticationAlgorithm;
use norsk\api\user\infrastructure\identityAccessManagement\authentication\AuthenticationKey;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(JwtConfig::class)]
class JwtConfigTest extends TestCase
{
    private AuthenticationKey $authKey;

    private AuthenticationAlgorithm $algorithm;

    private JwtSubject $subject;

    private JwtAudience $audience;

    private JwtConfig $jwtConfig;


    public function testCanGetAlgorithm(): void
    {
        self::assertSame(
            $this->algorithm,
            $this->jwtConfig->getAlgorithm()
        );
    }


    public function testCanGetAuthKey(): void
    {
        self::assertSame(
            $this->authKey,
            $this->jwtConfig->getAuthKey()
        );
    }


    public function testCanGetSubject(): void
    {
        self::assertSame(
            $this->subject,
            $this->jwtConfig->getSubject()
        );
    }


    public function testCanGetAudience(): void
    {
        self::assertSame(
            $this->audience,
            $this->jwtConfig->getAudience()
        );
    }


    protected function setUp(): void
    {
        $this->authKey = AuthenticationKey::by('foo4.bar5.abc987');
        $this->algorithm = AuthenticationAlgorithm::by('ES256K');
        $this->subject = JwtSubject::by('foo');
        $this->audience = JwtAudience::by('bar');

        $this->jwtConfig = JwtConfig::fromCredentials(
            $this->authKey,
            $this->algorithm,
            $this->subject,
            $this->audience
        );
    }
}
