<?php

declare(strict_types=1);

namespace norsk\api\manager\application\verbManaging;

use norsk\api\manager\application\verbManaging\useCases\CreateVerb;
use norsk\api\manager\domain\verbs\ManagedVerb;
use norsk\api\manager\domain\VocabularyUniquenessPolicy;
use norsk\api\manager\infrastructure\persistence\ManagerWriter;
use norsk\api\tests\provider\VerbProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(VerbCreator::class)]
class VerbCreatorTest extends TestCase
{
    public function testCanHandleCreateVerb(): void
    {
        $id = null;

        $verb = VerbProvider::managedVerbToGo();
        $german = $verb->getGerman();
        $norsk = $verb->getNorsk();
        $norskPresent = $verb->getNorskPresent();
        $norskPast = $verb->getNorskPast();
        $norskPastPerfect = $verb->getNorskPastPerfect();

        $createVerbMock = $this->createMock(CreateVerb::class);
        $createVerbMock->expects($this->once())
            ->method('getGerman')
            ->willReturn($german);
        $createVerbMock->expects($this->once())
            ->method('getNorsk')
            ->willReturn($norsk);
        $createVerbMock->expects($this->once())
            ->method('getNorskPresent')
            ->willReturn($norskPresent);
        $createVerbMock->expects($this->once())
            ->method('getNorskPast')
            ->willReturn($norskPast);
        $createVerbMock->expects($this->once())
            ->method('getNorskPastPerfect')
            ->willReturn($norskPastPerfect);

        $writerMock = $this->createMock(ManagerWriter::class);
        $writerMock->expects($this->once())
            ->method('add')
            ->with(ManagedVerb::createNew($german, $norsk, $norskPresent, $norskPast, $norskPastPerfect));

        $policyMock = $this->createMock(VocabularyUniquenessPolicy::class);
        $policyMock->expects($this->once())
            ->method('ensureVocabularyIsNotAlreadyPersisted')
            ->with($id, $german, $norsk);

        $verbCreator = new VerbCreator($writerMock, $policyMock);
        $verbCreator->handle($createVerbMock);
    }
}
