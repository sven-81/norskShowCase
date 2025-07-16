<?php

declare(strict_types=1);

namespace norsk\api\infrastructure\routing;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Method::class)]
class MethodTest extends TestCase
{

    public function testCanUseMethodAsString(): void
    {
        $string = 'doSomething';
        $method = Method::of($string);

        $this->assertSame($string, $method->asString());
    }


    public function testThrowsExceptionIfStringIsEmpty(): void
    {
        $this->expectExceptionObject(new InvalidArgumentException(message: 'Method cannot be empty.'));
        Method::of('');
    }

}
