<?php

declare(strict_types=1);

namespace norsk\api\app\persistence;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Parameters::class)]
class ParametersTest extends TestCase
{
    public function testCanGetAddedValue(): void
    {
        $expected = [
            0 => 'zero',
            1 => 'one',
            2 => 2,
            3 => true,
        ];

        $params = Parameters::init();
        $params->addString('zero');
        $params->addString('one');
        $params->addInt(2);
        $params->addBool(true);

        $this->assertSame($expected, $params->asArray());
    }
}
