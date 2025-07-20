<?php

declare(strict_types=1);

namespace norsk\api\user\domain\model;

use norsk\api\shared\application\Json;
use norsk\api\user\domain\valueObjects\UserName;
use norsk\api\user\infrastructure\identityAccessManagement\jwt\JsonWebToken;

readonly class LoggedInUser
{
    private const int TWO_HOURS_IN_SECONDS = 7200;


    private function __construct(private ValidatedUser $validatedUser, private JsonWebToken $jwToken)
    {
    }


    public static function by(ValidatedUser $validatedUser, JsonWebToken $jwToken): self
    {
        return new self($validatedUser, $jwToken);
    }


    public function asBodyJson(): Json
    {
        return Json::encodeFromArray(
            [
                'login' => true,
                'username' => $this->getUserName()->asString(),
                'firstName' => $this->validatedUser->getFirstName()->asString(),
                'lastName' => $this->validatedUser->getLastName()->asString(),
                'token' => $this->jwToken->asString(),
                'tokenType' => 'Bearer',
                'expiresIn' => self::TWO_HOURS_IN_SECONDS,
            ]
        );
    }


    public function getUserName(): UserName
    {
        return $this->validatedUser->getUsername();
    }
}
