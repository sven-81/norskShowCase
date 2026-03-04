<?php

declare(strict_types=1);

namespace norsk\api\manager\application\wordManaging\useCases;

use norsk\api\shared\domain\German;
use norsk\api\shared\domain\Id;
use norsk\api\shared\domain\Norsk;
use norsk\api\shared\infrastructure\http\request\Payload;

readonly class UpdateWord
{
    private function __construct(
        private Id $id,
        private German $german,
        private Norsk $norsk
    ) {
    }


    public static function createBy(Id $id, Payload $payload): self
    {
        $payloadArray = $payload->asArray();

        return new self(
            id: $id,
            german: German::of($payloadArray['german']),
            norsk: Norsk::of($payloadArray['norsk'])
        );
    }


    public function getId(): Id
    {
        return $this->id;
    }


    public function getGerman(): German
    {
        return $this->german;
    }


    public function getNorsk(): Norsk
    {
        return $this->norsk;
    }
}
