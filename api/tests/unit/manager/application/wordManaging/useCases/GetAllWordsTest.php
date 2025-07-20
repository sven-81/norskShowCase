<?php

declare(strict_types=1);

namespace norsk\api\manager\application\wordManaging\useCases;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(GetAllWords::class)]
class GetAllWordsTest extends TestCase
{
    public function testGetAllWordsCommandIsSingleton(): void
    {
        $command = GetAllWords::create();
        /** @phpstan-ignore-next-line */
        $this->assertInstanceOf(GetAllWords::class, $command);
    }
}
