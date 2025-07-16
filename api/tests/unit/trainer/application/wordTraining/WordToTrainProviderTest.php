<?php

declare(strict_types=1);

namespace norsk\api\trainer\application\wordTraining;

use norsk\api\shared\domain\Vocabularies;
use norsk\api\tests\provider\WordProvider;
use norsk\api\trainer\application\wordTraining\useCases\GetWordToTrain;
use norsk\api\trainer\domain\RandomGenerator;
use norsk\api\trainer\domain\words\TrainingWordReadingRepository;
use norsk\api\user\domain\valueObjects\UserName;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(WordToTrainProvider::class)]
class WordToTrainProviderTest extends TestCase
{

    public function testCanHandleCommand(): void
    {
        $userName = UserName::by('someUser');
        $commandMock = $this->createMock(GetWordToTrain::class);
        $commandMock->expects($this->once())
            ->method('getUserName')
            ->willReturn($userName);

        $vocabularies = Vocabularies::create();
        $vocabularies->add(WordProvider::trainingWordArchipelago());

        $repositoryMock = $this->createMock(TrainingWordReadingRepository::class);
        $repositoryMock->expects($this->once())
            ->method('getAllWordsFor')
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

        $handler = new WordToTrainProvider($repositoryMock, $randomGeneratorMock);
        $trainingWord = $handler->handle($commandMock);

        $this->assertEquals($pickedVocabulary, $trainingWord);
    }
}
