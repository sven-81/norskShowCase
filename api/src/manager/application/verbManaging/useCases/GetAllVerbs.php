<?php

declare(strict_types=1);

namespace norsk\api\manager\application\verbManaging\useCases;

readonly class GetAllVerbs
{
    private function __construct()
    {
    }


    public static function create(): self
    {
        return new self();
    }
}
