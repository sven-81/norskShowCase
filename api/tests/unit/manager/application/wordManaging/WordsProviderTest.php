<?php

declare(strict_types=1);

namespace norsk\api\manager\application\wordManaging;

use norsk\api\manager\application\wordManaging\useCases\GetAllWords;
use norsk\api\manager\infrastructure\persistence\WordReader;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(WordsProvider::class)]
class WordsProviderTest extends TestCase
{
    public function testCanHandleGetAllWords(): void
    {
        $readerMock = $this->createMock(WordReader::class);
        $readerMock->expects($this->once())
            ->method('getAllWords');

        $wordCreator = new WordsProvider($readerMock);
        $wordCreator->handle(GetAllWords::create());
    }
}