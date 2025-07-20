<?php

declare(strict_types=1);

namespace norsk\api\infrastructure\persistence;

interface SqlStatement
{
    public function asString(): string;
}
