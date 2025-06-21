<?php

declare(strict_types=1);

namespace norsk\api\app\request;

use InvalidArgumentException;
use norsk\api\app\response\ResponseCode;

readonly class Parameter
{
    private function __construct(private string $parameter)
    {
    }


    public static function by(string $parameter): self
    {
        $trimmed = trim($parameter);
        self::ensureIsNotEmpty($trimmed);

        return new self($trimmed);
    }


    private static function ensureIsNotEmpty(string $trimmed): void
    {
        if ($trimmed === '') {
            throw new InvalidArgumentException(
                'Parameter cannot be empty.',
                ResponseCode::unprocessable->value
            );
        }
    }


    public function asString(): string
    {
        return $this->parameter;
    }
}
