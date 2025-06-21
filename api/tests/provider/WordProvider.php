<?php

declare(strict_types=1);

namespace norsk\api\tests\provider;

use norsk\api\manager\words\ManagedWord;
use norsk\api\shared\German;
use norsk\api\shared\Id;
use norsk\api\shared\Norsk;
use norsk\api\trainer\SuccessCounter;
use norsk\api\trainer\words\TrainingWord;

class WordProvider
{
    private Id $id;

    private German $german;

    private Norsk $norsk;

    private SuccessCounter $successCounter;


    public function __construct()
    {
        $this->id = Id::by(3);
        $this->german = German::of('Schärenküste');
        $this->norsk = Norsk::of('skjærgård');
        $this->successCounter = SuccessCounter::by(56);
    }


    public static function managedWordArchipelago(): ManagedWord
    {
        return (new WordProvider())
            ->buildManagedWord();
    }


    public function buildManagedWord(): ManagedWord
    {
        return ManagedWord::of(
            $this->id,
            $this->german,
            $this->norsk,
        );
    }


    public static function trainingWordArchipelago(): TrainingWord
    {
        return (new WordProvider())
            ->buildTrainingWord();
    }


    public function buildTrainingWord(): TrainingWord
    {
        return TrainingWord::of(
            $this->id,
            $this->german,
            $this->norsk,
            $this->successCounter
        );
    }


    public static function managedWordArchipelagoAsArray(): array
    {
        return [
            'id' => 3,
            'german' => 'Schärenküste',
            'norsk' => 'skjærgård',
        ];
    }


    public static function managedWordArchipelagoAsJsonString(): string
    {
        return '{"id":3,"german":"Sch\u00e4renk\u00fcste","norsk":"skj\u00e6rg\u00e5rd"}';
    }


    public function setId(Id $id): self
    {
        $this->id = $id;

        return $this;
    }


    public function setGerman(German $german): self
    {
        $this->german = $german;

        return $this;
    }


    public function setNorsk(Norsk $norsk): self
    {
        $this->norsk = $norsk;

        return $this;
    }


    public function setSuccessCounter(SuccessCounter $successCounter): self
    {
        $this->successCounter = $successCounter;

        return $this;
    }
}
