<?php

declare(strict_types=1);

namespace norsk\api\app\identityAccessManagement;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(JsonWebToken::class)]
class JsonWebTokenTest extends TestCase
{
    private string $token;


    public function testCanBeUsedAsStringFromPlainTokenString(): void
    {
        $this->assertSame($this->token, JsonWebToken::fromString($this->token)->asString());
    }


    public function testThrowsExceptionIfTokenFormatIsNotJwt(): void
    {
        $this->expectExceptionObject(new InvalidArgumentException('Token has no valid format'));

        JsonWebToken::fromString('bla');
    }


    public function testCanBeUsedAsStringFromBearerString(): void
    {
        $token = 'Bearer xzJhb.eyJzdWIicCI6MTY1NTIxODAwOX0.ceU31GO4x6QscCNHS4_6GDVq4A';

        $this->assertSame($this->token, JsonWebToken::fromBearerString($token)->asString());
    }


    protected function setUp(): void
    {
        $this->token = 'xzJhb.eyJzdWIicCI6MTY1NTIxODAwOX0.ceU31GO4x6QscCNHS4_6GDVq4A';
    }
}
