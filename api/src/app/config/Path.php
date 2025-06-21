<?php

declare(strict_types=1);

namespace norsk\api\app\config;

class Path
{
    private function __construct(private readonly string $path)
    {
    }


    public static function fromString(string $path): self
    {
        return new self($path);
    }


    public function asString(): string
    {
        return $this->path;
    }
}
