<?php

declare(strict_types=1);

namespace norsk\api\trainer\domain\verbs;

use norsk\api\shared\application\Json;
use norsk\api\shared\domain\German;
use norsk\api\shared\domain\Id;
use norsk\api\shared\domain\Norsk;
use norsk\api\shared\domain\TrainingVocabulary;
use norsk\api\trainer\domain\SuccessCounter;

readonly class TrainingVerb implements TrainingVocabulary
{
    private function __construct(
        private Id $id,
        private German $german,
        private Norsk $norsk,
        private Norsk $norskPresent,
        private Norsk $norskPast,
        private Norsk $norskPastPerfect,
        private SuccessCounter $successCounter,
    ) {
    }


    public static function of(
        Id $id,
        German $german,
        Norsk $norsk,
        Norsk $norskPresent,
        Norsk $norskPast,
        Norsk $norskPastPerfect,
        SuccessCounter $successCounter
    ): self {
        return new self(
            $id,
            $german,
            $norsk,
            $norskPresent,
            $norskPast,
            $norskPastPerfect,
            $successCounter
        );
    }


    public function getId(): Id
    {
        return $this->id;
    }


    public function getSuccessCounter(): SuccessCounter
    {
        return $this->successCounter;
    }


    public function asJson(): Json
    {
        $jsonArray = [
            'id' => $this->id->asInt(),
            'german' => $this->german->asString(),
            'norsk' => $this->norsk->asString(),
            'norskPresent' => $this->norskPresent->asString(),
            'norskPast' => $this->norskPast->asString(),
            'norskPastPerfect' => $this->norskPastPerfect->asString(),
        ];

        return Json::encodeFromArray($jsonArray);
    }
}
