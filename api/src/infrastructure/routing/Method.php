<?php

declare(strict_types=1);

namespace norsk\api\infrastructure\routing;

use InvalidArgumentException;

readonly class Method
{

    private function __construct(private string $method)
    {
    }


    public static function of(string $string): self
    {
        self::ensureIsNotEmpty($string);

        return new self($string);
    }


    private static function ensureIsNotEmpty(string $string): void
    {
        if (empty($string)) {
            throw new InvalidArgumentException(message: 'Method cannot be empty.');
        }
    }


    public function asString(): string
    {
        return $this->method;
    }
}
