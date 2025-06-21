<?php

declare(strict_types=1);

namespace norsk\api\shared;

enum VocabularyType: string
{
    case word = 'word';
    case verb = 'verb';


    public function isWord(VocabularyType $vocabularyType): bool
    {
        return self::word->value === $vocabularyType->value;
    }
}
