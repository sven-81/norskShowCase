<?php

declare(strict_types=1);

namespace norsk\api\manager\words;

use norsk\api\shared\German;
use norsk\api\shared\Id;
use norsk\api\shared\Json;
use norsk\api\shared\Norsk;
use norsk\api\shared\Vocabulary;

class ManagedWord implements Vocabulary
{
    private function __construct(
        private readonly Id $id,
        private readonly German $german,
        private readonly Norsk $norsk
    ) {
    }


    public static function of(Id $id, German $german, Norsk $norsk): self
    {
        return new self($id, $german, $norsk);
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
        ];

        return Json::encodeFromArray($jsonArray);
    }
}
