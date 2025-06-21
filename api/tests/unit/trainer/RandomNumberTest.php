<?php

declare(strict_types=1);

namespace norsk\api\trainer;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(RandomNumber::class)]
class RandomNumberTest extends TestCase
{
    public function testCanCreateRandomNumberBetweenZeroAndOneHundredAndUsedAsInt(): void
    {
        $randomNumber = RandomNumber::create();
        $result = false;

        $filterVar = filter_var(
            $randomNumber->asInt(),
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 0, 'max_range' => 100,],]
        );
        if ($filterVar) {
            $result = true;
        }

        self::assertTrue($result);
    }
}
