<?php

declare(strict_types=1);

namespace norsk\api\manager\domain;

use ArrayIterator;
use IteratorAggregate;
use norsk\api\shared\application\Json;
use norsk\api\shared\domain\ManagingVocabulary;

class ManagedVocabularies implements IteratorAggregate
{
    /** @var ManagingVocabulary[] */
    private array $vocabularies;


    private function __construct(ManagingVocabulary ...$vocabulary)
    {
        $this->vocabularies = $vocabulary;
    }


    public static function create(): self
    {
        return new self();
    }


    public function add(ManagingVocabulary $vocabulary): void
    {
        $this->vocabularies[] = $vocabulary;
    }


    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->vocabularies);
    }


    public function asJson(): Json
    {
        $array = [];

        foreach ($this->vocabularies as $vocabulary) {
            $array[] = $vocabulary->asJson()->asDecodedJson();
        }

        return Json::encodeFromArray($array);
    }
}

