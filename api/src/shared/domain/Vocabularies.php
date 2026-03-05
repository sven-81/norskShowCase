<?php

declare(strict_types=1);

namespace norsk\api\shared\domain;

use ArrayIterator;
use IteratorAggregate;
use norsk\api\shared\application\Json;
use OutOfBoundsException;

class Vocabularies implements IteratorAggregate
{
    /** @var TrainingVocabulary[] */
    private array $vocabularies;


    private function __construct(TrainingVocabulary ...$vocabulary)
    {
        $this->vocabularies = $vocabulary;
    }


    public static function create(): self
    {
        return new self();
    }


    public function add(TrainingVocabulary $vocabulary): void
    {
        $this->vocabularies[] = $vocabulary;
    }


    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->vocabularies);
    }


    public function pick(Id $pickedVocabularyId): TrainingVocabulary
    {
        foreach ($this->vocabularies as $vocabulary) {
            if ($vocabulary->getId()->asInt() === $pickedVocabularyId->asInt()) {
                return $vocabulary;
            }
        }

        throw new OutOfBoundsException(
            'No vocabulary found for id: ' . $pickedVocabularyId->asInt()
        );
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
