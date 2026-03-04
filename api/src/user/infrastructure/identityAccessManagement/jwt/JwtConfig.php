<?php

declare(strict_types=1);

namespace norsk\api\user\infrastructure\identityAccessManagement\jwt;

use norsk\api\user\infrastructure\identityAccessManagement\authentication\AuthenticationAlgorithm;
use norsk\api\user\infrastructure\identityAccessManagement\authentication\AuthenticationKey;

readonly class JwtConfig
{
    private function __construct(
        private AuthenticationKey $authKey,
        private AuthenticationAlgorithm $algorithm,
        private JwtSubject $subject,
        private JwtAudience $audience
    ) {
    }


    public static function fromCredentials(
        AuthenticationKey $authKey,
        AuthenticationAlgorithm $algorithm,
        JwtSubject $subject,
        JwtAudience $audience
    ): self {
        return new self($authKey, $algorithm, $subject, $audience);
    }


    public function getAuthKey(): AuthenticationKey
    {
        return $this->authKey;
    }


    public function getAlgorithm(): AuthenticationAlgorithm
    {
        return $this->algorithm;
    }


    public function getSubject(): JwtSubject
    {
        return $this->subject;
    }


    public function getAudience(): JwtAudience
    {
        return $this->audience;
    }
}
