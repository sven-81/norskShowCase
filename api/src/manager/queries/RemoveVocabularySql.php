<?php

declare(strict_types=1);

namespace norsk\api\manager\queries;

use norsk\api\app\persistence\SqlStatement;
use norsk\api\shared\VocabularyType;

class RemoveVocabularySql implements SqlStatement
{
    private readonly string $sql;


    private function __construct(VocabularyType $vocabularyType)
    {
        $table = $vocabularyType->value . 's';
        $this->sql = 'UPDATE `' . $table . '` SET `active`=0 WHERE `id`=?;';
    }


    public static function create(VocabularyType $vocabularyType): self
    {
        return new self($vocabularyType);
    }


    public function asString(): string
    {
        return $this->sql;
    }
}
