<?php

declare(strict_types=1);

namespace norsk\api\shared;

use InvalidArgumentException;
use norsk\api\app\response\ResponseCode;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Norsk::class)]
class NorskTest extends TestCase
{
    public static function provideValidNorsk(): array
    {
        return [
            'Norsk' => ['   Norsk   '],
            'Apfel' => ['   Æble   '],
            'Ehre' => ['ære'],
            'Bier' => ['Øl'],
            'Insel' => ['øy'],
            'Jahr' => ['År'],
            'öffnen' => ['åpne'],
            'Sprichwort' => ['Å være på bærtur.'],
        ];
    }


    public static function provideInvalidNorsk(): array
    {
        return [
            'space' => ['     '],
            'tab' => [' '],
            'return' => ["\r"],
        ];
    }


    #[DataProvider('provideValidNorsk')]
    public function testCanBeUsedAsString(string $valid): void
    {
        $this->assertSame(trim($valid), Norsk::of($valid)->asString());
    }


    #[DataProvider('provideInvalidNorsk')]
    public function testThrowsExceptionIfNorskIsEmpty(string $invalid): void
    {
        $this->expectExceptionObject(
            new InvalidArgumentException('Norsk cannot be empty.', ResponseCode::unprocessable->value)
        );
        Norsk::of($invalid);
    }
}
