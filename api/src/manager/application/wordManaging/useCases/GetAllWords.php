<?php

declare(strict_types=1);

namespace norsk\api\manager\application\wordManaging\useCases;

readonly class GetAllWords
{
    private function __construct()
    {
    }


    public static function create(): self
    {
        return new self();
    }
}
