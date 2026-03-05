<?php

declare(strict_types=1);

namespace norsk\api\trainer\application\verbTraining;

use norsk\api\shared\domain\Id;
use norsk\api\trainer\application\verbTraining\useCases\SaveTrainedVerb;
use norsk\api\trainer\domain\verbs\VerbTrainingWritingRepository;
use norsk\api\user\domain\valueObjects\UserName;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(VerbProgressUpdater::class)]
class VerbProgressUpdaterTest extends TestCase
{
    public function testCanHandleCommand(): void
    {
        $userName = UserName::by('someUser');
        $id = Id::by(1);

        $commandMock = $this->createMock(SaveTrainedVerb::class);
        $commandMock->expects($this->once())
            ->method('getUserName')
            ->willReturn($userName);
        $commandMock->expects($this->once())
            ->method('getId')
            ->willReturn($id);

        $repositoryMock = $this->createVerbRepositoryMock();
        $repositoryMock->expects($this->once())
            ->method('saveAsTrainedVerb')
            ->with($userName, $id);

        /** @var VerbProgressUpdater $handler */
        $handler = new VerbProgressUpdater($repositoryMock);
        $handler->handle($commandMock);
    }


    private function createVerbRepositoryMock(): VerbTrainingWritingRepository&MockObject
    {
        return $this->createMock(VerbTrainingWritingRepository::class);
    }
}
