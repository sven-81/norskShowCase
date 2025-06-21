<?php

declare(strict_types=1);

namespace norsk\api\trainer\words;

use norsk\api\shared\German;
use norsk\api\shared\Id;
use norsk\api\shared\Json;
use norsk\api\shared\Norsk;
use norsk\api\shared\Vocabulary;
use norsk\api\trainer\SuccessCounter;

class TrainingWord implements Vocabulary
{
    private function __construct(
        private readonly Id $id,
        private readonly German $german,
        private readonly Norsk $norsk,
        private readonly SuccessCounter $successCounter,
    ) {
    }


    public static function of(Id $id, German $german, Norsk $norsk, SuccessCounter $successCounter): self
    {
        return new self($id, $german, $norsk, $successCounter);
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
        ];

        return Json::encodeFromArray($jsonArray);
    }
}
