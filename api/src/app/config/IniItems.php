<?php

declare(strict_types=1);

namespace norsk\api\app\config;

use ArrayIterator;
use IteratorAggregate;

class IniItems implements IteratorAggregate
{

    private function __construct(private readonly array $items)
    {
    }


    public static function fromAssocArray(array $items): self
    {
        return new self($items);
    }


    public function asArray(): array
    {
        return $this->items;
    }


    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->items);
    }
}
