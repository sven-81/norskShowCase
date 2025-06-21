<?php

declare(strict_types=1);

namespace norsk\api\manager\verbs;

use norsk\api\shared\German;
use norsk\api\shared\Id;
use norsk\api\shared\Json;
use norsk\api\shared\Norsk;
use norsk\api\shared\Vocabulary;

class ManagedVerb implements Vocabulary
{
    private function __construct(
        private readonly Id $id,
        private readonly German $german,
        private readonly Norsk $norsk,
        private readonly Norsk $norskPresent,
        private readonly Norsk $norskPast,
        private readonly Norsk $norskPastPerfect
    ) {
    }


    public static function of(
        Id $id,
        German $german,
        Norsk $norsk,
        Norsk $norskPresent,
        Norsk $norskPast,
        Norsk $norskPastPerfect
    ): self {
        return new self(
            $id,
            $german,
            $norsk,
            $norskPresent,
            $norskPast,
            $norskPastPerfect
        );
    }


    public function getId(): Id
    {
        return $this->id;
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
