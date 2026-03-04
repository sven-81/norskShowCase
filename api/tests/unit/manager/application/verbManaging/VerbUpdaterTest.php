<?php

declare(strict_types=1);

namespace norsk\api\manager\application\verbManaging;

use norsk\api\manager\application\verbManaging\useCases\UpdateVerb;
use norsk\api\manager\domain\verbs\ManagedVerb;
use norsk\api\manager\infrastructure\persistence\ManagerWriter;
use norsk\api\manager\infrastructure\persistence\SqlUniquenessPolicy;
use norsk\api\shared\domain\Id;
use norsk\api\tests\provider\VerbProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(VerbUpdater::class)]
class VerbUpdaterTest extends TestCase
{
    public function testCanHandleCreateVerb(): void
    {
        $id = Id::by(12);

        $verb = VerbProvider::managedVerbToGo();
        $german = $verb->getGerman();
        $norsk = $verb->getNorsk();
        $norskPresent = $verb->getNorskPresent();
        $norskPast = $verb->getNorskPast();
        $norskPastPerfect = $verb->getNorskPastPerfect();

        $updateVerbMock = $this->createMock(UpdateVerb::class);
        $updateVerbMock->expects($this->once())
            ->method('getId')
            ->willReturn($id);
        $updateVerbMock->expects($this->once())
            ->method('getGerman')
            ->willReturn($german);
        $updateVerbMock->expects($this->once())
            ->method('getNorsk')
            ->willReturn($norsk);
        $updateVerbMock->expects($this->once())
            ->method('getNorskPresent')
            ->willReturn($norskPresent);
        $updateVerbMock->expects($this->once())
            ->method('getNorskPast')
            ->willReturn($norskPast);
        $updateVerbMock->expects($this->once())
            ->method('getNorskPastPerfect')
            ->willReturn($norskPastPerfect);

        $policyMock = $this->createMock(SqlUniquenessPolicy::class);
        $policyMock->expects($this->once())
            ->method('ensureVocabularyIsNotAlreadyPersisted')
            ->with($id, $german, $norsk);

        $managedVerb = ManagedVerb::fromPersistence(
            $id,
            $german,
            $norsk,
            $norskPresent,
            $norskPast,
            $norskPastPerfect
        );

        $writerMock = $this->createMock(ManagerWriter::class);
        $writerMock->expects($this->once())
            ->method('update')
            ->with($managedVerb);

        $verbCreator = new VerbUpdater($writerMock, $policyMock);
        $verbCreator->handle($updateVerbMock);
    }
}
