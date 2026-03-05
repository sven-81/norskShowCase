<?php

declare(strict_types=1);

namespace norsk\api\manager\application\wordManaging;

use norsk\api\manager\application\wordManaging\useCases\UpdateWord;
use norsk\api\manager\domain\words\ManagedWord;
use norsk\api\manager\infrastructure\persistence\ManagerWriter;
use norsk\api\manager\infrastructure\persistence\SqlUniquenessPolicy;
use norsk\api\shared\domain\Id;
use norsk\api\tests\provider\WordProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(WordUpdater::class)]
class WordUpdaterTest extends TestCase
{
    public function testCanHandleCreateWord(): void
    {
        $id = Id::by(12);
        $word = WordProvider::managedWordArchipelago();
        $german = $word->getGerman();
        $norsk = $word->getNorsk();

        $updateWordMock = $this->createMock(UpdateWord::class);
        $updateWordMock->expects($this->once())
            ->method('getId')
            ->willReturn($id);
        $updateWordMock->expects($this->once())
            ->method('getGerman')
            ->willReturn($german);
        $updateWordMock->expects($this->once())
            ->method('getNorsk')
            ->willReturn($norsk);

        $policyMock = $this->createMock(SqlUniquenessPolicy::class);
        $policyMock->expects($this->once())
            ->method('ensureVocabularyIsNotAlreadyPersisted')
            ->with($id, $german, $norsk);

        $managedWord = ManagedWord::fromPersistence($id, $german, $norsk);

        $writerMock = $this->createMock(ManagerWriter::class);
        $writerMock->expects($this->once())
            ->method('update')
            ->with($managedWord);

        $wordCreator = new WordUpdater($writerMock, $policyMock);
        $wordCreator->handle($updateWordMock);
    }
}
