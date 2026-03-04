<?php

declare(strict_types=1);

namespace norsk\api\manager\application\verbManaging\useCases;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(GetAllVerbs::class)]
class GetAllVerbsTest extends TestCase
{
    public function testGetAllVerbsCommandIsSingleton(): void
    {
        $command = GetAllVerbs::create();

        self::assertSame(GetAllVerbs::class, get_class($command));
    }
}
