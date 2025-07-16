<?php

declare(strict_types=1);

namespace norsk\api\trainer\application\wordTraining;

use norsk\api\shared\domain\Id;
use norsk\api\trainer\application\wordTraining\useCases\SaveTrainedWord;
use norsk\api\trainer\domain\WritingRepository;
use norsk\api\user\domain\valueObjects\UserName;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(WordProgressUpdater::class)]
class WordProgressUpdaterTest extends TestCase
{

    public function testCanHandleCommand(): void
    {
        $userName = UserName::by('someUser');
        $id = Id::by(1);

        $commandMock = $this->createMock(SaveTrainedWord::class);
        $commandMock->expects($this->once())
            ->method('getUserName')
            ->willReturn($userName);
        $commandMock->expects($this->once())
            ->method('getId')
            ->willReturn($id);

        $repositoryMock = $this->createMock(WritingRepository::class);
        $repositoryMock->expects($this->once())
            ->method('saveAsTrainedWord')
            ->with($userName, $id);

        $handler = new WordProgressUpdater($repositoryMock);
        $handler->handle($commandMock);
    }
}
