<?php

declare(strict_types=1);

namespace norsk\api\infrastructure\persistence;

use ArrayIterator;
use Closure;
use IteratorAggregate;

class SqlResult implements IteratorAggregate
{
    /**
     * @param array<array|string|int|bool|null> $result
     */
    private function __construct(private array $result)
    {
    }


    /**
     * @param array<array|string|int|bool|null> $result
     * @return self
     */
    public static function resultFromArray(array $result): self
    {
        return new self($result);
    }


    /**
     * @return array[]
     */
    public function asArray(): array
    {
        return $this->result;
    }


    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->result);
    }


    public function count(): int
    {
        return count($this->result);
    }


    public function hasEntries(): bool
    {
        if ($this->hasCountedResultsByQuery()) {
            return true;
        }

        return $this->hasAnyResults();
    }


    private function hasCountedResultsByQuery(): bool
    {
        $anyKeysParent = array_keys($this->result);

        foreach ($anyKeysParent as $parentKey) {
            if (is_array($this->result[$parentKey])) {
                $anyKeysChild = array_filter($this->result[$parentKey], $this->hasChildValues());

                return $this->childHasValues($anyKeysChild);
            }
        }

        return false;
    }


    private function hasChildValues(): Closure
    {
        return static fn($value) => $value > 0;
    }


    private function childHasValues(array $anyKeysChild): bool
    {
        if ($this->childIsNotEmpty($anyKeysChild)) {
            return true;
        }

        return false;
    }


    private function childIsNotEmpty(array $anyKeysChild): bool
    {
        return !empty($anyKeysChild);
    }


    private function hasAnyResults(): bool
    {
        if ($this->wasCountedByQuery()) {
            return false;
        }
        if (count($this->result) > 0) {
            return true;
        }

        return false;
    }


    private function wasCountedByQuery(): bool
    {
        $anyKeysParent = array_keys($this->result);

        foreach ($anyKeysParent as $parentKey) {
            if (is_array($this->result[$parentKey]) && count($this->result[$parentKey]) > 0) {
                return true;
            }
        }

        return false;
    }
}
