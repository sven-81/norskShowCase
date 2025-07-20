<?php

declare(strict_types=1);

namespace norsk\api\manager\domain\verbs;

use LogicException;
use norsk\api\infrastructure\persistence\AffectedRows;
use norsk\api\shared\application\Json;
use norsk\api\shared\domain\German;
use norsk\api\shared\domain\Id;
use norsk\api\shared\domain\ManagingVocabulary;
use norsk\api\shared\domain\Norsk;
use norsk\api\shared\domain\VocabularyPersistencePort;

class ManagedVerb implements ManagingVocabulary
{
    private function __construct(
        private readonly ?Id $id,
        private readonly German $german,
        private readonly Norsk $norsk,
        private readonly Norsk $norskPresent,
        private readonly Norsk $norskPast,
        private readonly Norsk $norskPastPerfect
    ) {
    }


    public static function createNew(
        German $german,
        Norsk $norsk,
        Norsk $norskPresent,
        Norsk $norskPast,
        Norsk $norskPastPerfect,
    ): self {
        return new self(
            id: null,
            german: $german,
            norsk: $norsk,
            norskPresent: $norskPresent,
            norskPast: $norskPast,
            norskPastPerfect: $norskPastPerfect
        );
    }


    public static function fromPersistence(
        Id $id,
        German $german,
        Norsk $norsk,
        Norsk $norskPresent,
        Norsk $norskPast,
        Norsk $norskPastPerfect
    ): self {
        return new self(
            $id,
            $german,
            $norsk,
            $norskPresent,
            $norskPast,
            $norskPastPerfect
        );
    }


    public function getId(): Id
    {
        if ($this->id === null) {
            throw new LogicException('Cannot access Id of a non-persisted ManagedVerb.');
        }

        return $this->id;
    }


    public function asJson(): Json
    {
        $jsonArray = [
            'id' => $this->id->asInt(),
            'german' => $this->german->asString(),
            'norsk' => $this->norsk->asString(),
            'norskPresent' => $this->norskPresent->asString(),
            'norskPast' => $this->norskPast->asString(),
            'norskPastPerfect' => $this->norskPastPerfect->asString(),
        ];

        return Json::encodeFromArray($jsonArray);
    }


    public function persistWith(VocabularyPersistencePort $writer): void
    {
        $writer->saveNewVerb($this);
    }


    public function updateWith(VocabularyPersistencePort $writer): AffectedRows
    {
        return $writer->saveEditedVerb($this);
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
