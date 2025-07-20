<?php

declare(strict_types=1);

namespace norsk\api\tests\provider;

use norsk\api\manager\domain\verbs\ManagedVerb;
use norsk\api\shared\domain\German;
use norsk\api\shared\domain\Id;
use norsk\api\shared\domain\Norsk;
use norsk\api\trainer\domain\SuccessCounter;
use norsk\api\trainer\domain\verbs\TrainingVerb;

class VerbProvider
{
    private Id $id;

    private German $german;

    private Norsk $norsk;

    private Norsk $norskPresent;

    private Norsk $norskPast;

    private Norsk $norskPastPerfect;

    private SuccessCounter $successCounter;


    public function __construct()
    {
        $this->id = Id::by(1);
        $this->german = German::of('gehen');
        $this->norsk = Norsk::of('gå');
        $this->norskPresent = Norsk::of('går');
        $this->norskPast = Norsk::of('gikk');
        $this->norskPastPerfect = Norsk::of('har gått');
        $this->successCounter = SuccessCounter::by(1);
    }


    public static function managedVerbToGo(): ManagedVerb
    {
        return (new VerbProvider())
            ->buildManagedVerb();
    }


    public function buildManagedVerb(): ManagedVerb
    {
        return ManagedVerb::fromPersistence(
            $this->id,
            $this->german,
            $this->norsk,
            $this->norskPresent,
            $this->norskPast,
            $this->norskPastPerfect,
        );
    }


    public static function trainingVerbToGo(): TrainingVerb
    {
        return (new VerbProvider())
            ->buildTrainingVerb();
    }


    public function buildTrainingVerb(): TrainingVerb
    {
        return TrainingVerb::of(
            $this->id,
            $this->german,
            $this->norsk,
            $this->norskPresent,
            $this->norskPast,
            $this->norskPastPerfect,
            $this->successCounter
        );
    }


    public static function managedVerbToGoAsArray(): array
    {
        return [
            'id' => 1,
            'german' => 'gehen',
            'norsk' => 'gå',
            'norskPresent' => 'går',
            'norskPast' => 'gikk',
            'norskPastPerfect' => 'har gått',
        ];
    }


    public static function managedVerbToGoFromRecord(): array
    {
        return [
            'id' => 1,
            'german' => 'gehen',
            'norsk' => 'gå',
            'norsk_present' => 'går',
            'norsk_past' => 'gikk',
            'norsk_past_perfekt' => 'har gått',
        ];
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


    public function setNorskPresent(Norsk $norskPresent): self
    {
        $this->norskPresent = $norskPresent;

        return $this;
    }


    public function setNorskPast(Norsk $norskPast): self
    {
        $this->norskPast = $norskPast;

        return $this;
    }


    public function setNorskPastPerfect(Norsk $norskPastPerfect): self
    {
        $this->norskPastPerfect = $norskPastPerfect;

        return $this;
    }


    public function setSuccessCounter(SuccessCounter $successCounter): self
    {
        $this->successCounter = $successCounter;

        return $this;
    }
}
