<?php

declare(strict_types=1);

namespace norsk\api\trainer\domain;

use LogicException;
use norsk\api\shared\domain\German;
use norsk\api\shared\domain\Id;
use norsk\api\shared\domain\Norsk;
use norsk\api\shared\domain\Vocabularies;
use norsk\api\tests\provider\VerbProvider;
use norsk\api\tests\provider\WordProvider;
use norsk\api\tests\stubs\FakeVocabulary;
use OutOfBoundsException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(RandomGenerator::class)]
class RandomGeneratorTest extends TestCase
{
    public function testCanPickAWordFormAllWordsForUser(): void
    {
        $provider = new WordProvider();

        $word1 = $provider
            ->setSuccessCounter(SuccessCounter::by(null))
            ->buildTrainingWord();

        $word2 = $provider
            ->setId(Id::by(666))
            ->setGerman(German::of('Wellen'))
            ->setNorsk(Norsk::of('bølger'))
            ->setSuccessCounter(SuccessCounter::by(1))
            ->buildTrainingWord();

        $randomNumberMock = $this->createMock(RandomNumber::class);
        $randomNumberMock->expects($this->once())
            ->method('asInt')
            ->willReturn(51);

        $randomGenerator = new RandomGenerator($randomNumberMock);
        $allWordsForUser = Vocabularies::create();
        $allWordsForUser->add($word2);
        $allWordsForUser->add($word1);
        $allWordsForUser->add($word2);

        self::assertEquals($word1, $randomGenerator->pickFrom($allWordsForUser));
    }


    public function testCanPickAVerbFormAllVerbsForUser(): void
    {
        $provider = new VerbProvider();
        $verb1 = $provider->setId(Id::by(123))
            ->setSuccessCounter(SuccessCounter::by(null))
            ->buildTrainingVerb();

        $verb2 = $provider->setId(Id::by(666))
            ->setGerman(German::of('sehen'))
            ->setNorsk(Norsk::of('se'))
            ->setNorskPresent(Norsk::of('ser'))
            ->setNorskPast(Norsk::of('så'))
            ->setNorskPastPerfect(Norsk::of('har sett'))
            ->buildTrainingVerb();

        $randomNumberMock = $this->createMock(RandomNumber::class);
        $randomNumberMock->expects($this->once())
            ->method('asInt')
            ->willReturn(51);

        $randomGenerator = new RandomGenerator($randomNumberMock);
        $allVerbsForUser = Vocabularies::create();
        $allVerbsForUser->add($verb2);
        $allVerbsForUser->add($verb1);
        $allVerbsForUser->add($verb2);

        self::assertEquals($verb1, $randomGenerator->pickFrom($allVerbsForUser));
    }


    public function testThrowsExceptionIfRandomCannotBeChosenBecauseThereAreNone(): void
    {
        $this->expectExceptionObject(
            new OutOfBoundsException(
                'No Vocabulary can be chosen randomly for RandomNumber: '
            )
        );
        $randomNumberMock = $this->createMock(RandomNumber::class);
        $randomGenerator = new RandomGenerator($randomNumberMock);
        $allWordsForUser = Vocabularies::create();

        $randomGenerator->pickFrom($allWordsForUser);
    }


    public function testThrowsExceptionIfManagingWordIsGiven(): void
    {
        $this->expectExceptionObject(
            new LogicException(
                'Vocabulary type has to be TrainingWord or TrainingVerb'
            )
        );
        $randomNumberMock = $this->createMock(RandomNumber::class);
        $randomGenerator = new RandomGenerator($randomNumberMock);
        $managingWordsForUser = Vocabularies::create();
        $managingWordsForUser->add(FakeVocabulary::create());

        $randomGenerator->pickFrom($managingWordsForUser);
    }


    public function testThrowsExceptionIfManagingVerbIsGiven(): void
    {
        $this->expectExceptionObject(
            new LogicException(
                'Vocabulary type has to be TrainingWord or TrainingVerb'
            )
        );
        $randomNumberMock = $this->createMock(RandomNumber::class);
        $randomGenerator = new RandomGenerator($randomNumberMock);
        $managingVerbsForUser = Vocabularies::create();
        $managingVerbsForUser->add(FakeVocabulary::create());

        $randomGenerator->pickFrom($managingVerbsForUser);
    }
}
