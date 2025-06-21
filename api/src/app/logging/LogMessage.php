<?php

declare(strict_types=1);

namespace norsk\api\app\logging;

class LogMessage
{
    private function __construct(private readonly string $message)
    {
    }


    public static function fromString(string $string): self
    {
        return new self($string);
    }


    public function asString(): string
    {
        return $this->message;
    }
}
