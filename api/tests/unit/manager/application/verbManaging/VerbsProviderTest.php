<?php

declare(strict_types=1);

namespace norsk\api\manager\application\verbManaging;

use norsk\api\manager\application\verbManaging\useCases\GetAllVerbs;
use norsk\api\manager\infrastructure\persistence\VerbReader;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(VerbsProvider::class)]
class VerbsProviderTest extends TestCase
{
    public function testCanHandleGetAllVerbs(): void
    {
        $readerMock = $this->createMock(VerbReader::class);
        $readerMock->expects($this->once())
            ->method('getAllVerbs');

        $verbCreator = new VerbsProvider($readerMock);
        $verbCreator->handle(GetAllVerbs::create());
    }
}