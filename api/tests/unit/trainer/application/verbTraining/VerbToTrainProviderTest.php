<?php

declare(strict_types=1);

namespace norsk\api\trainer\application\verbTraining;

use norsk\api\shared\domain\Vocabularies;
use norsk\api\tests\provider\VerbProvider;
use norsk\api\trainer\application\verbTraining\useCases\GetVerbToTrain;
use norsk\api\trainer\domain\RandomGenerator;
use norsk\api\trainer\domain\verbs\TrainingVerbReadingRepository;
use norsk\api\user\domain\valueObjects\UserName;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(VerbToTrainProvider::class)]
class VerbToTrainProviderTest extends TestCase
{

    public function testCanHandleCommand(): void
    {
        $userName = UserName::by('someUser');
        $commandMock = $this->createMock(GetVerbToTrain::class);
        $commandMock->expects($this->once())
            ->method('getUserName')
            ->willReturn($userName);

        $vocabularies = Vocabularies::create();
        $vocabularies->add(VerbProvider::trainingVerbToGo());

        $repositoryMock = $this->createMock(TrainingVerbReadingRepository::class);
        $repositoryMock->expects($this->once())
            ->method('getAllVerbsFor')
            ->with($userName)
            ->willReturn($vocabularies);

        $pickedVocabulary = null;
        foreach ($vocabularies as $vocabulary) {
            $pickedVocabulary = $vocabulary;
        }

        $randomGeneratorMock = $this->createMock(RandomGenerator::class);
        $randomGeneratorMock->expects($this->once())
            ->method('pickFrom')
            ->with($vocabularies)
            ->willReturn($pickedVocabulary);

        $handler = new VerbToTrainProvider($repositoryMock, $randomGeneratorMock);
        $trainingVerb = $handler->handle($commandMock);

        $this->assertEquals($pickedVocabulary, $trainingVerb);
    }
}
