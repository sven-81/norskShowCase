<?php

declare(strict_types=1);

namespace norsk\api\user;

use norsk\api\app\identityAccessManagement\JsonWebToken;
use norsk\api\shared\Json;

readonly class LoggedInUser
{
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
            ]
        );
    }


    public function getUserName(): UserName
    {
        return $this->validatedUser->getUsername();
    }
}
