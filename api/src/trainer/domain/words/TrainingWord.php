<?php

declare(strict_types=1);

namespace norsk\api\trainer\domain\words;

use norsk\api\shared\application\Json;
use norsk\api\shared\domain\German;
use norsk\api\shared\domain\Id;
use norsk\api\shared\domain\Norsk;
use norsk\api\shared\domain\TrainingVocabulary;
use norsk\api\trainer\domain\SuccessCounter;

class TrainingWord implements TrainingVocabulary
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
