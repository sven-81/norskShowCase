<?php

declare(strict_types=1);

namespace norsk\api\manager\application\verbManaging\useCases;

use norsk\api\shared\domain\German;
use norsk\api\shared\domain\Norsk;
use norsk\api\shared\infrastructure\http\request\Payload;

readonly class CreateVerb
{

    private function __construct(
        private German $german,
        private Norsk $norsk,
        private Norsk $norskPresent,
        private Norsk $norskPast,
        private Norsk $norskPastPerfect
    ) {
    }


    public static function createBy(Payload $payload): self
    {
        $payloadArray = $payload->asArray();

        return new self(
            german: German::of($payloadArray['german']),
            norsk: Norsk::of($payloadArray['norsk']),
            norskPresent: Norsk::of($payloadArray['norskPresent']),
            norskPast: Norsk::of($payloadArray['norskPast']),
            norskPastPerfect: Norsk::of($payloadArray['norskPastPerfect'])
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


    public function getNorskPresent(): Norsk
    {
        return $this->norskPresent;
    }


    public function getNorskPast(): Norsk
    {
        return $this->norskPast;
    }


    public function getNorskPastPerfect(): Norsk
    {
        return $this->norskPastPerfect;
    }
}
