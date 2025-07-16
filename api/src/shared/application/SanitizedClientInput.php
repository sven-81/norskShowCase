<?php

declare(strict_types=1);

namespace norsk\api\shared\application;

class SanitizedClientInput
{
    private const string CHAR_SET = 'UTF-8';


    private function __construct(private readonly string $string)
    {
    }


    public static function of(string $string): self
    {
        $sanitized = htmlspecialchars($string, ENT_QUOTES, self::CHAR_SET);

        return new self($sanitized);
    }


    public function asString(): string
    {
        return $this->string;
    }
}
