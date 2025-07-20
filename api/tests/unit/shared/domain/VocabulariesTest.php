<?php

declare(strict_types=1);

namespace norsk\api\shared\domain;

use LogicException;
use norsk\api\tests\provider\VerbProvider;
use norsk\api\tests\provider\WordProvider;
use norsk\api\tests\stubs\FakeVocabulary;
use norsk\api\trainer\domain\SuccessCounter;
use norsk\api\trainer\domain\words\TrainingWord;
use OutOfBoundsException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Vocabularies::class)]
class VocabulariesTest extends TestCase
{
    private Id $id3;

    private Id $id666;

    private SuccessCounter $successCounter;

    private Vocabularies $words;

    private TrainingWord $word;


    public function testCanIterate(): void
    {
        $this->words->add($this->word);

        self::assertCount(2, $this->words->getIterator());
    }


    public function testCanPickWordById(): void
    {
        $provider = new WordProvider();
        $word2 = $provider->setId($this->id666)
            ->setSuccessCounter($this->successCounter)
            ->buildTrainingWord();

        $this->words->add($word2);

        self::assertEquals($this->word, $this->words->pick($this->id3));
    }


    public function testCanPickVerbById(): void
    {
        $provider = new VerbProvider();
        $verb1 = $provider->setId($this->id666)
            ->setSuccessCounter($this->successCounter)
            ->buildTrainingVerb();

        $verb2 = $provider->setId(Id::by(999))
            ->setGerman(German::of('sehen'))
            ->setNorsk(Norsk::of('se'))
            ->setNorskPresent(Norsk::of('ser'))
            ->setNorskPast(Norsk::of('så'))
            ->setNorskPastPerfect(Norsk::of('har sett'))
            ->setSuccessCounter($this->successCounter)
            ->buildTrainingVerb();

        $verbs = Vocabularies::create();
        $verbs->add($verb1);
        $verbs->add($verb2);

        self::assertEquals($verb1, $verbs->pick($this->id666));
    }


    public function testThrowsExceptionIfNoIdCanBeMapped(): void
    {
        $this->expectExceptionObject(
            new OutOfBoundsException('No Word can be mapped with chosen id: 321')
        );
        $this->words->pick(Id::by(321));
    }


    public function testThrowsExceptionIfPickIsUsedOnManagedWords(): void
    {
        $this->expectExceptionObject(
            new LogicException('Vocabulary type has to be TrainingWord or TrainingVerb')
        );

        $managedWords = Vocabularies::create();
        $managedWords->add(FakeVocabulary::create());

        $managedWords->pick($this->id3);
    }


    public function testCanGetManagedWordAsJson(): void
    {
        $provider = new WordProvider();
        $word2 = $provider->setId($this->id666)
            ->setSuccessCounter($this->successCounter)
            ->buildTrainingWord();

        $this->words->add($word2);

        self::assertJsonStringEqualsJsonString(
            '[{"german":"Schärenküste","id":3,"norsk":"skjærgård"},'
            . '{"german":"Schärenküste","id":666,"norsk":"skjærgård"}]',
            $this->words->asJson()->asString()
        );
    }


    protected function setUp(): void
    {
        $this->id3 = Id::by(3);
        $this->id666 = Id::by(666);
        $this->successCounter = SuccessCounter::by(56);

        $this->word = WordProvider::trainingWordArchipelago();

        $this->words = Vocabularies::create();
        $this->words->add($this->word);
    }
}
