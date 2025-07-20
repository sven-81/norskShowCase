<?php

declare(strict_types=1);

namespace norsk\api\infrastructure\logging;

use norsk\api\infrastructure\logging\LogMessage;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(LogMessage::class)]
class LogMessageTest extends TestCase
{
    public function testCanBeUsedAsString(): void
    {
        $this->assertSame('some Text', LogMessage::fromString('some Text')->asString());
    }
}
