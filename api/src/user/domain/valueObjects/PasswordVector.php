<?php

declare(strict_types=1);

namespace norsk\api\user\domain\valueObjects;

use SensitiveParameter;

class PasswordVector
{
    private function __construct(
        private readonly Salt $salt,
        private readonly Pepper $pepper
    ) {
    }


    public static function by(#[SensitiveParameter] Salt $salt, Pepper $pepper): self
    {
        return new self($salt, $pepper);
    }


    public function getSalt(): Salt
    {
        return $this->salt;
    }


    public function getPepper(): Pepper
    {
        return $this->pepper;
    }
}
