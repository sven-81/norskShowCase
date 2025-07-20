<?php

declare(strict_types=1);

namespace norsk\api\manager\application\verbManaging;

use norsk\api\manager\application\verbManaging\useCases\DeleteVerb;
use norsk\api\manager\infrastructure\persistence\ManagerWriter;
use norsk\api\shared\domain\Id;
use norsk\api\shared\domain\VocabularyType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(VerbRemover::class)]
class VerbRemoverTest extends TestCase
{
    public function testCanHandleDeleteVerb(): void
    {
        $id = Id::by(123);
        $deleteVerbMock = $this->createMock(DeleteVerb::class);
        $deleteVerbMock->expects($this->once())
            ->method('getId')
            ->willReturn($id);

        $writerMock = $this->createMock(ManagerWriter::class);
        $writerMock->expects($this->once())
            ->method('remove')
            ->with($id, VocabularyType::verb);

        $verbCreator = new VerbRemover($writerMock);
        $verbCreator->handle($deleteVerbMock);
    }
}
