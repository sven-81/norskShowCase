<?php

declare(strict_types=1);

namespace norsk\api\shared;

use ArrayIterator;
use IteratorAggregate;
use LogicException;
use norsk\api\manager\verbs\ManagedVerb;
use norsk\api\manager\words\ManagedWord;
use norsk\api\trainer\verbs\TrainingVerb;
use norsk\api\trainer\words\TrainingWord;
use OutOfBoundsException;

class Vocabularies implements IteratorAggregate
{
    private array $vocabularies;


    private function __construct(TrainingWord|ManagedWord|TrainingVerb|ManagedVerb ...$vocabulary)
    {
        $this->vocabularies = $vocabulary;
    }


    public static function create(): self
    {
        return new self();
    }


    public function add(Vocabulary $vocabulary): void
    {
        $this->vocabularies[] = $vocabulary;
    }


    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->vocabularies);
    }


    public function pick(Id $pickedVocabularyId): TrainingWord|TrainingVerb
    {
        $type = null;
        foreach ($this->vocabularies as $vocabulary) {
            $type = $this->getType($vocabulary);
            if ($vocabulary->getId()->asInt() === $pickedVocabularyId->asInt()) {
                return $vocabulary;
            }
        }

        throw new OutOfBoundsException(
            'No ' . $type . ' can be mapped with chosen id: ' . $pickedVocabularyId->asInt()
        );
    }


    private function getType(Vocabulary $vocabulary): string
    {
        if ($vocabulary instanceof TrainingWord) {
            return ucfirst(VocabularyType::word->value);
        }

        if ($vocabulary instanceof TrainingVerb) {
            return ucfirst(VocabularyType::verb->value);
        }

        throw new LogicException('Vocabulary type has to be TrainingWord or TrainingVerb');
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
