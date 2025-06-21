<?php

declare(strict_types=1);

namespace norsk\api\shared;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(VocabularyType::class)]
class VocabularyTypeTest extends TestCase
{
    public function testReturnsTrueIfTypeIsWord(): void
    {
        self::assertTrue(VocabularyType::word->isWord(VocabularyType::word));
    }


    public function testReturnsFalseIfTypeIsVerb(): void
    {
        self::assertFalse(VocabularyType::word->isWord(VocabularyType::verb));
    }
}
