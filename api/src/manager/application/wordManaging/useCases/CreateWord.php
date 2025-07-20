<?php

declare(strict_types=1);

namespace norsk\api\manager\application\wordManaging\useCases;

use norsk\api\shared\domain\German;
use norsk\api\shared\domain\Norsk;
use norsk\api\shared\infrastructure\http\request\Payload;

readonly class CreateWord
{

    private function __construct(
        private German $german,
        private Norsk $norsk
    ) {
    }


    public static function createBy(Payload $payload): self
    {
        $payloadArray = $payload->asArray();

        return new self(
            german: German::of($payloadArray['german']),
            norsk: Norsk::of($payloadArray['norsk']),
        );
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
