<?php

declare(strict_types=1);

namespace norsk\api\app\identityAccessManagement;

class AuthenticationKey
{
    private function __construct(private readonly string $key)
    {
    }


    public static function by(string $key): self
    {
        return new self($key);
    }


    public function asString(): string
    {
        return $this->key;
    }


    public function asBase64String(): string
    {
        return base64_encode($this->key);
    }
}
