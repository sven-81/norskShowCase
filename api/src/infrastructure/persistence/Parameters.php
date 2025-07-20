<?php

declare(strict_types=1);

namespace norsk\api\infrastructure\persistence;

class Parameters
{
    /**
     * @param string[] $value
     */
    private function __construct(private array $value)
    {
    }


    public static function init(): self
    {
        return new self([]);
    }


    public function addString(string $value): void
    {
        $this->value[] = $value;
    }


    public function addInt(int $value): void
    {
        $this->value[] = $value;
    }


    public function addBool(bool $value): void
    {
        $this->value[] = $value;
    }


    /**
     * @return string[]
     */
    public function asArray(): array
    {
        return $this->value;
    }
}
