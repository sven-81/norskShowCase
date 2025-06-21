<?php

declare(strict_types=1);

namespace norsk\api\trainer;

class RandomNumber
{
    private const int MIN = 0;
    private const int MAX = 100;


    private function __construct(private readonly int $randomInt)
    {
    }


    public static function create(): self
    {
        return new self(random_int(self::MIN, self::MAX));
    }


    public function asInt(): int
    {
        return $this->randomInt;
    }
}
