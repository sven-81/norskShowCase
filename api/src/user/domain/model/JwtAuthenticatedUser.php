<?php

declare(strict_types=1);

namespace norsk\api\user\domain\model;

use InvalidArgumentException;
use norsk\api\shared\infrastructure\http\request\Payload;
use norsk\api\user\application\AuthenticatedUserInterface;
use norsk\api\user\domain\valueObjects\UserName;
use Psr\Http\Message\ServerRequestInterface;

class JwtAuthenticatedUser implements AuthenticatedUserInterface
{
    private const string AUTHENTICATED_USER = 'authenticatedUser';


    private function __construct(
        private readonly UserName $userName,
        private readonly Role $role,
    ) {
    }


    public static function byPayload(Payload $payload): self
    {
        $decodedPayload = $payload->asArray();

        self::ensureNicknameExists($decodedPayload);
        $role = self::getScope($decodedPayload);

        return new self(
            UserName::by($decodedPayload['nickname']),
            $role
        );
    }


    private static function ensureNicknameExists(array $decodedPayload): void
    {
        if (!array_key_exists('nickname', $decodedPayload)) {
            throw new InvalidArgumentException('Nickname not found in JWT payload.');
        }
    }


    private static function getScope(array $decodedPayload): Role
    {
        self::ensureScopeExists($decodedPayload);

        $scopeValue = str_replace(search: 'is:', replace: '', subject: $decodedPayload['scope']);
        $role = Role::tryFrom($scopeValue);

        self::ensureScopeIsValid($role, $scopeValue);

        return $role;
    }


    private static function ensureScopeExists(array $decodedPayload): void
    {
        if (!array_key_exists('scope', $decodedPayload)) {
            throw new InvalidArgumentException('Scope not found in JWT payload.');
        }
    }


    private static function ensureScopeIsValid(?Role $role, string $scopeValue): void
    {
        if ($role === null) {
            throw new InvalidArgumentException('Invalid scope in JWT payload: ' . $scopeValue);
        }
    }


    public static function byRequest(ServerRequestInterface $request): self
    {
        $user = $request->getAttribute(self::AUTHENTICATED_USER);
        self::ensureAuthenticatedUserExists($user);

        return new self($user->getUserName(), $user->getRole());
    }


    public function getUserName(): UserName
    {
        return $this->userName;
    }


    public function getRole(): Role
    {
        return $this->role;
    }


    private static function ensureAuthenticatedUserExists(?JwtAuthenticatedUser $user): void
    {
        if ($user === null) {
            throw new InvalidArgumentException('Authenticated user not found in JWT payload.');
        }
    }


    public function roleEquals(Role $role): bool
    {
        return $this->getRole() === $role;
    }
}
