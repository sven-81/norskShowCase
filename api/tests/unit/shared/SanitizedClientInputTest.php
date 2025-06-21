<?php

declare(strict_types=1);

namespace norsk\api\shared;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(SanitizedClientInput::class)]
class SanitizedClientInputTest extends TestCase
{
    public static function provideInput(): array
    {
        return [
            'XSS' => ['<script>alert("XSS")</script>', '&lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;'],
            'empty' => ['', ''],
            'space' => ['  ', '  '],
            'apostroph' => ['O\'Reilly & Co.', 'O&#039;Reilly &amp; Co.'],
            'slash' => ['some/thing', 'some/thing'],
            'backslash' => ['some\thing', 'some\thing'],
            'germanSpecialChars' => ['öäüßÖÄÜ', 'öäüßÖÄÜ'],
            'norskSpecialChars' => ['ÆæØøÅå', 'ÆæØøÅå'],
        ];
    }


    #[DataProvider('provideInput')]
    public function testCanBeUsedAsString(string $input, string $expected): void
    {
        self::assertEquals($expected, SanitizedClientInput::of($input)->asString());
    }
}
