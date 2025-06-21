<?php

declare(strict_types=1);

namespace norsk\api\tests\provider;

use norsk\api\shared\VocabularyType;

class VocabularyTypeProvider
{
    public static function getVocabularyType(): array
    {
        return [
            'word' => [VocabularyType::word],
            'verb' => [VocabularyType::verb],
        ];
    }
}
