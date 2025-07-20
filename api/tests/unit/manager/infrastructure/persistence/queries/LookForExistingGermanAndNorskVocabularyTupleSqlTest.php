<?php

declare(strict_types=1);

namespace norsk\api\manager\infrastructure\persistence\queries;

use norsk\api\shared\domain\Id;
use norsk\api\shared\domain\VocabularyType;
use norsk\api\tests\provider\VocabularyTypeProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(LookForExistingGermanAndNorskVocabularyTupleSql::class)]
class LookForExistingGermanAndNorskVocabularyTupleSqlTest extends TestCase
{
    public static function getVocabularyType(): array
    {
        return VocabularyTypeProvider::getVocabularyType();
    }


    #[DataProvider('getVocabularyType')]
    public function testCanBeUsedAsStringWithoutId(VocabularyType $vocabularyType): void
    {
        $lookForExistingGermanAndNorskWordTupleSql = LookForExistingGermanAndNorskVocabularyTupleSql::create(
            $vocabularyType,
            null
        );

        self::assertSame(
            'SELECT COUNT("german") AS "match" FROM ' . $vocabularyType->value . 's'
            . ' WHERE BINARY german = ? AND norsk = ?;',
            $lookForExistingGermanAndNorskWordTupleSql->asString()
        );
    }


    #[DataProvider('getVocabularyType')]
    public function testCanBeUsedAsStringWithId(VocabularyType $vocabularyType): void
    {
        $lookForExistingGermanWordSql = LookForExistingGermanAndNorskVocabularyTupleSql::create(
            $vocabularyType,
            Id::by(3)
        );

        self::assertSame(
            'SELECT COUNT("german") AS "match" FROM '
            . $vocabularyType->value . 's'
            . ' WHERE BINARY german = ? AND norsk = ? AND id != 3;',
            $lookForExistingGermanWordSql->asString()
        );
    }
}
