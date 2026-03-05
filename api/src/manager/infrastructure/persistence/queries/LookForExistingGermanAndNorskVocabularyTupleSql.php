<?php

declare(strict_types=1);

namespace norsk\api\manager\infrastructure\persistence\queries;

use norsk\api\infrastructure\persistence\SqlStatement;
use norsk\api\shared\domain\Id;
use norsk\api\shared\domain\VocabularyType;

readonly class LookForExistingGermanAndNorskVocabularyTupleSql implements SqlStatement
{
    private string $sql;


    private function __construct(VocabularyType $vocabularyType, ?Id $optionalId)
    {
        $table = $vocabularyType->value . 's';
        $addIdMatch = $this->addId($optionalId);
        $this->sql = 'SELECT COUNT("german") AS "match" FROM ' . $table
                     . ' WHERE BINARY german = ? AND norsk = ?'
                     . $addIdMatch . ';';
    }


    private function addId(?Id $optionalId): string
    {
        $addIdMatch = '';
        if ($optionalId instanceof Id) {
            $addIdMatch = ' AND id != ' . $optionalId->asInt();
        }

        return $addIdMatch;
    }


    public static function create(VocabularyType $vocabularyType, ?Id $optionalId): self
    {
        return new self($vocabularyType, $optionalId);
    }


    public function asString(): string
    {
        return $this->sql;
    }
}
