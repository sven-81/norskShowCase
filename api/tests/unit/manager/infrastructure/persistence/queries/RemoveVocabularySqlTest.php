<?php

declare(strict_types=1);

namespace norsk\api\manager\infrastructure\persistence\queries;

use norsk\api\shared\domain\VocabularyType;
use norsk\api\tests\provider\VocabularyTypeProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(RemoveVocabularySql::class)]
class RemoveVocabularySqlTest extends TestCase
{
    public static function getVocabularyType(): array
    {
        return VocabularyTypeProvider::getVocabularyType();
    }


    #[DataProvider('getVocabularyType')]
    public function testCanBeUsedAsString(VocabularyType $vocabularyType): void
    {
        $table = $vocabularyType->value . 's';
        self::assertSame(
            'UPDATE `' . $table . '` SET `active`=0 WHERE `id`=?;',
            RemoveVocabularySql::create($vocabularyType)->asString()
        );
    }
}
