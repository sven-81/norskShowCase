<?php

declare(strict_types=1);

namespace norsk\api\infrastructure\persistence;

readonly class GenericSqlStatement implements SqlStatement
{
    private function __construct(private string $sqlStatement)
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
