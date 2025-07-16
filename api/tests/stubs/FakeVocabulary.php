<?php

declare(strict_types=1);

namespace norsk\api\tests\stubs;

use norsk\api\infrastructure\persistence\AffectedRows;
use norsk\api\shared\application\Json;
use norsk\api\shared\domain\Id;
use norsk\api\shared\domain\ManagingVocabulary;
use norsk\api\shared\domain\TrainingVocabulary;
use norsk\api\shared\domain\VocabularyPersistencePort;
use norsk\api\trainer\domain\SuccessCounter;

class FakeVocabulary implements TrainingVocabulary, ManagingVocabulary
{
    private SuccessCounter $successCounter;


    private function __construct(
        private readonly Id $id,
    ) {
        $this->successCounter = SuccessCounter::by(1);
    }


    public static function create(): self
    {
        return new self(Id::by(id: 666));
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
        return Json::encodeFromArray([]);
    }


    public function persistWith(VocabularyPersistencePort $writer): void
    {
    }


    public function updateWith(VocabularyPersistencePort $writer): AffectedRows
    {
        return AffectedRows::fromInt(rows: 1);
    }
}
