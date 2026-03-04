<?php

declare(strict_types=1);

namespace norsk\api\manager\application\wordManaging;

use norsk\api\manager\application\wordManaging\useCases\CreateWord;
use norsk\api\manager\domain\words\ManagedWord;
use norsk\api\manager\infrastructure\persistence\ManagerWriter;
use norsk\api\manager\infrastructure\persistence\SqlUniquenessPolicy;
use norsk\api\tests\provider\WordProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(WordCreator::class)]
class WordCreatorTest extends TestCase
{
    public function testCanHandleCreateWord(): void
    {
        $word = WordProvider::managedWordArchipelago();
        $createWordMock = $this->createMock(CreateWord::class);
        $createWordMock->expects($this->once())
            ->method('getGerman')
            ->willReturn($word->getGerman());
        $createWordMock->expects($this->once())
            ->method('getNorsk')
            ->willReturn($word->getNorsk());

        $policyMock = $this->createMock(SqlUniquenessPolicy::class);
        $policyMock->expects($this->once())
            ->method('ensureVocabularyIsNotAlreadyPersisted')
            ->with(null, $word->getGerman(), $word->getNorsk());

        $managedWord = ManagedWord::createNew($word->getGerman(), $word->getNorsk());

        $writerMock = $this->createMock(ManagerWriter::class);
        $writerMock->expects($this->once())
            ->method('add')
            ->with($managedWord);

        $wordCreator = new WordCreator($writerMock, $policyMock);
        $wordCreator->handle($createWordMock);
    }
}
