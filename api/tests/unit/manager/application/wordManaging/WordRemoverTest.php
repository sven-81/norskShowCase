<?php

declare(strict_types=1);

namespace norsk\api\manager\application\wordManaging;

use norsk\api\manager\application\wordManaging\useCases\DeleteWord;
use norsk\api\manager\infrastructure\persistence\ManagerWriter;
use norsk\api\shared\domain\Id;
use norsk\api\shared\domain\VocabularyType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(WordRemover::class)]
class WordRemoverTest extends TestCase
{
    public function testCanHandleDeleteWord(): void
    {
        $id = Id::by(123);
        $deleteWordMock = $this->createMock(DeleteWord::class);
        $deleteWordMock->expects($this->once())
            ->method('getId')
            ->willReturn($id);

        $writerMock = $this->createMock(ManagerWriter::class);
        $writerMock->expects($this->once())
            ->method('remove')
            ->with($id, VocabularyType::word);

        $wordCreator = new WordRemover($writerMock);
        $wordCreator->handle($deleteWordMock);
    }
}
