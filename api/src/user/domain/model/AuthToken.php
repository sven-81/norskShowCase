<?php

declare(strict_types=1);

namespace norsk\api\user\domain\model;

interface AuthToken
{
    public function asString(): string;
}

