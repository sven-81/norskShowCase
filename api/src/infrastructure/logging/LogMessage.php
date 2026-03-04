<?php

declare(strict_types=1);

namespace norsk\api\infrastructure\logging;

readonly class LogMessage
{
    private function __construct(private string $message)
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
