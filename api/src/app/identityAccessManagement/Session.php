<?php

declare(strict_types=1);

namespace norsk\api\app\identityAccessManagement;

use InvalidArgumentException;
use norsk\api\app\request\Payload;
use norsk\api\app\response\ResponseCode;
use norsk\api\user\Role;
use norsk\api\user\UserName;
use norsk\api\user\UsersReader;

class Session
{
    private const string NORSK_CLIENT = 'norsk client';


    private function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }


    public static function create(): self
    {
        return new self();
    }


    public static function getUserName(): UserName
    {
        $userName = $_SESSION['user'];
        self::ensureIsNotNull($userName);

        return UserName::by($userName);
    }


    private static function ensureIsNotNull(?string $userName): void
    {
        if ($userName === null) {
            throw new InvalidArgumentException('Session user is not set.', ResponseCode::serverError->value);
        }
    }


    public static function isValidManager(): bool
    {
        return isset($_SESSION['user'], $_SESSION['scope']) && self::isScopeManager();
    }


    private static function isScopeManager(): bool
    {
        return $_SESSION['scope'] === 'is:' . Role::MANAGER->value;
    }


    public function addJwtContent(Payload $payload, UsersReader $usersReader): void
    {
        $payloadArray = $payload->asArray();

        $this->addScope($payloadArray);
        $this->addUserName($payloadArray, $usersReader);
        $this->addExpiration($payloadArray);
    }


    private function addScope(array $payload): void
    {
        if (
            array_key_exists('scope', $payload)
            && ($payload['scope'] === 'is:user'
                || $payload['scope'] === 'is:manager')
        ) {
            $_SESSION['scope'] = $payload['scope'];
        }
    }


    private function addUserName(array $payload, UsersReader $usersReader): void
    {
        if ($payload['nickname'] !== null) {
            $this->ifTokenIsClientDefaultSetDefaultUserName($payload);
            $this->ifIsNotClientCheckIfUsernameExists($payload, $usersReader);
            $_SESSION['user'] = UserName::by($payload['nickname'])->asString();
        }
    }


    private function ifTokenIsClientDefaultSetDefaultUserName(array $payload): void
    {
        if ($payload['nickname'] === self::NORSK_CLIENT) {
            $_SESSION['user'] = $payload['nickname'];
        }
    }


    private function ifIsNotClientCheckIfUsernameExists(array $payload, UsersReader $usersReader): void
    {
        if ($payload['nickname'] !== self::NORSK_CLIENT) {
            $usersReader->checkIfUserExists(UserName::by($payload['nickname']));
        }
    }


    private function addExpiration(array $payload): void
    {
        if ($payload['exp'] !== null) {
            $_SESSION['expire'] = $payload['exp'];
        }
    }


    public function destroy(): void
    {
        session_unset();
        session_destroy();
    }
}
