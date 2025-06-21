<?php

declare(strict_types=1);

namespace norsk\api\app\config;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(IniItems::class)]
class IniItemsTest extends TestCase
{
    public function testCanBeUsedAsArray(): void
    {
        $array = [
            1 => [
                'foo' => 'bar',
            ],
        ];
        $this->assertSame($array, IniItems::fromAssocArray($array)->asArray());
    }


    public function testCanGetIterated(): void
    {
        $array = [
            1 => [
                'foo' => 'bar',
            ],
        ];
        $this->assertSame(1, IniItems::fromAssocArray($array)->getIterator()->key());
    }
}
