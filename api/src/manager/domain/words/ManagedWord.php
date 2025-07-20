<?php

declare(strict_types=1);

namespace norsk\api\manager\domain\words;

use LogicException;
use norsk\api\infrastructure\persistence\AffectedRows;
use norsk\api\shared\application\Json;
use norsk\api\shared\domain\German;
use norsk\api\shared\domain\Id;
use norsk\api\shared\domain\ManagingVocabulary;
use norsk\api\shared\domain\Norsk;
use norsk\api\shared\domain\VocabularyPersistencePort;

class ManagedWord implements ManagingVocabulary
{
    private function __construct(
        private readonly ?Id $id,
        private readonly German $german,
        private readonly Norsk $norsk
    ) {
    }


    public static function createNew(German $german, Norsk $norsk): self
    {
        return new self(
            id: null,
            german: $german,
            norsk: $norsk
        );
    }


    public static function fromPersistence(Id $id, German $german, Norsk $norsk): self
    {
        return new self($id, $german, $norsk);
    }


    public function getId(): Id
    {
        if ($this->id === null) {
            throw new LogicException('Cannot access Id of a non-persisted ManagedWord.');
        }

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


    public function persistWith(VocabularyPersistencePort $writer): void
    {
        $writer->saveNewWord($this);
    }


    public function updateWith(VocabularyPersistencePort $writer): AffectedRows
    {
        return $writer->saveEditedWord($this);
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
