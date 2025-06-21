<?php

declare(strict_types=1);

namespace norsk\api\app\persistence;

class GenericSqlStatement implements SqlStatement
{
    private function __construct(private readonly string $sqlStatement)
    {
    }


    public static function create(string $sqlStatement): self
    {
        return new self($sqlStatement);
    }


    public function asString(): string
    {
        return $this->sqlStatement;
    }
}
