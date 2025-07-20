<?php

declare(strict_types=1);

namespace norsk\api\shared\domain;

use InvalidArgumentException;
use norsk\api\shared\infrastructure\http\response\ResponseCode;

class Id
{
    private function __construct(private readonly int $id)
    {
    }


    public static function by(int $id): self
    {
        return new self($id);
    }


    public static function fromString(string $id): self
    {
        if (!is_numeric($id)) {
            throw new InvalidArgumentException(
                'Id has to be numeric: ' . $id,
                ResponseCode::badRequest->value
            );
        }

        return new self((int)$id);
    }


    public function asInt(): int
    {
        return $this->id;
    }


    public function asString(): string
    {
        return (string)$this->id;
    }
}
