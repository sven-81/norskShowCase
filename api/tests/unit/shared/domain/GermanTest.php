<?php

declare(strict_types=1);

namespace norsk\api\shared\domain;

use InvalidArgumentException;
use norsk\api\shared\infrastructure\http\response\ResponseCode;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(German::class)]
class GermanTest extends TestCase
{
    public static function provideInvalidGerman(): array
    {
        return [
            'space' => ['  a   '],
            'tab' => [' '],
            'tabsWithChar' => [' a   '],
            'return' => ["\r"],
            'returnWithChar' => ["\ra"],
        ];
    }


    public static function provideValidGerman(): array
    {
        return [
            'Äpfel' => ['  Äpfel    '],
            'Überprüfung' => [' Überprüfung   '],
            'Straße' => ['Straße'],
            'Käse' => ['Käse'],
            'Füße' => ['Füße'],
            'Öl' => ['Öl'],
            'Größe' => ['Größe'],
            'Mädchen' => ['Mädchen'],
            'Schlüssel' => ['Schlüssel'],
            'Bücher' => ['Bücher'],
            'äffen' => ['äffen'],
            'überqueren' => ['überqueren'],
            'schließen' => ['schließen'],
            'genießen' => ['genießen'],
            'fließen' => ['fließen'],
            'grüßen' => ['grüßen'],
            'zählen' => ['zählen'],
            'lösen' => ['lösen'],
            'biegen' => ['biegen'],
            'drücken' => ['drücken'],
            'Sprichwort' => ['Der frühe Vogel fängt den Wurm.'],
        ];
    }


    #[DataProvider('provideValidGerman')]
    public function testCanBeUsedAsString(string $valid): void
    {
        $this->assertSame(trim($valid), German::of($valid)->asString());
    }


    #[DataProvider('provideInvalidGerman')]
    public function testThrowsExceptionIfGermanIsEmpty(string $invalid): void
    {
        $this->expectExceptionObject(
            new InvalidArgumentException(
                'German has at least two chars.',
                ResponseCode::unprocessable->value
            )
        );
        German::of($invalid);
    }
}
