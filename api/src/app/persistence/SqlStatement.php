<?php

declare(strict_types=1);

namespace norsk\api\app\persistence;

interface SqlStatement
{
    public function asString(): string;
}
