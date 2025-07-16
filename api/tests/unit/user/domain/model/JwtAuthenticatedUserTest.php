<?php

declare(strict_types=1);

namespace norsk\api\user\domain\model;

use GuzzleHttp\Psr7\ServerRequest;
use InvalidArgumentException;
use norsk\api\shared\infrastructure\http\request\Payload;
use norsk\api\user\domain\valueObjects\UserName;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use stdClass;

#[CoversClass(JwtAuthenticatedUser::class)]
class JwtAuthenticatedUserTest extends TestCase
{
    private Payload $payload;


    protected function setUp(): void
    {
        $class = new StdClass();
        $class->nickname = 'someUser';
        $class->scope = 'is:user';
        $this->payload = Payload::by($class);
    }


    public function testCanBeCreatedByClass(): void
    {
        $jwtAuthUser = JwtAuthenticatedUser::byPayload($this->payload);
        self::assertEquals(Role::USER, $jwtAuthUser->getRole());
    }


    public function testCanBeCreatedByRequest(): void
    {
        $request = new ServerRequest('GET', '/');
        $request = $request->withAttribute(
            attribute: 'authenticatedUser',
            value: JwtAuthenticatedUser::byPayload($this->payload)
        );

        $jwtAuthUser = JwtAuthenticatedUser::byRequest($request);
        self::assertEquals(Role::USER, $jwtAuthUser->getRole());
    }


    public function testGetRole(): void
    {
        $jwtAuthUser = JwtAuthenticatedUser::byPayload($this->payload);
        self::assertEquals(Role::USER, $jwtAuthUser->getRole());
    }


    public function testGetUserName(): void
    {
        $jwtAuthUser = JwtAuthenticatedUser::byPayload($this->payload);
        self::assertEquals(UserName::by('someUser'), $jwtAuthUser->getUserName());
    }


    public function testRoleEquals(): void
    {
        $jwtAuthUser = JwtAuthenticatedUser::byPayload($this->payload);
        self::assertTrue($jwtAuthUser->roleEquals(Role::USER));
    }


    public function testThrowsExceptionIfNicknameDoesNotExist(): void
    {
        $this->expectExceptionObject(
            new InvalidArgumentException('Nickname not found in JWT payload.')
        );

        $class = new StdClass();
        $class->username = 'someUser';
        $class->scope = 'is:user';
        $payload = Payload::by($class);

        JwtAuthenticatedUser::byPayload($payload);
    }


    public function testThrowsExceptionIfScopeDoesNotExist(): void
    {
        $this->expectExceptionObject(
            new InvalidArgumentException('Scope not found in JWT payload.')
        );

        $class = new StdClass();
        $class->nickname = 'someUser';
        $class->role = 'is:user';
        $payload = Payload::by($class);

        JwtAuthenticatedUser::byPayload($payload);
    }


    public function testThrowsExceptionIfScopeIsNotValid(): void
    {
        $this->expectExceptionObject(
            new InvalidArgumentException('Invalid scope in JWT payload: invalid')
        );

        $class = new StdClass();
        $class->nickname = 'someUser';
        $class->scope = 'invalid';
        $payload = Payload::by($class);

        JwtAuthenticatedUser::byPayload($payload);
    }


    public function testThrowsExceptionIfAuthenticatedUserDoesNotExist(): void
    {
        $this->expectExceptionObject(
            new InvalidArgumentException('Authenticated user not found in JWT payload.')
        );

        $request = new ServerRequest(method: 'GET', uri: '/');
        $request = $request->withAttribute(
            attribute: 'NoAuthenticatedUser',
            value: JwtAuthenticatedUser::byPayload($this->payload)
        );

        JwtAuthenticatedUser::byRequest($request);
    }

}
