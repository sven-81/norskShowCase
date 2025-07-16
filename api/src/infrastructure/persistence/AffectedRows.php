<?php

declare(strict_types=1);

namespace norsk\api\infrastructure\persistence;

use RuntimeException;

class AffectedRows
{
    private function __construct(private readonly int $row)
    {
    }


    public static function fromInt(int $rows): self
    {
        return new self($rows);
    }


    public function notAtLeastOne(): bool
    {
        if ($this->row < 0) {
            throw new RuntimeException('An error has occurred during executing database query');
        }
        if ($this->row === 0) {
            return true;
        }

        return false;
    }
}
