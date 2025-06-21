<?php

declare(strict_types=1);

namespace norsk\api\user;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(FirstName::class)]
class FirstNameTest extends TestCase
{
    public static function provideValidNames(): array
    {
        return [
            'Jan-Elías Räy Jr.' => ['Jan-Elías Räy Jr.'],
            'José' => ['José'],
            'François' => ['François'],
            'Björn' => ['Björn'],
            'Jürgen' => ['Jürgen'],
            'Renée' => ['Renée'],
            'Zoë' => ['Zoë'],
            'André' => ['André'],
            'María' => ['María'],
            'Łukasz' => ['Łukasz'],
            'Sören' => ['Sören'],
            'Chloé' => ['Chloé'],
            'Émilie' => ['Émilie'],
            'Mário' => ['Mário'],
            'Sébastien' => ['Sébastien'],
            'Nikołaj' => ['Nikołaj'],
            'Aisling' => ['Aisling'],
            'Thérèse' => ['Thérèse'],
            'Jörg' => ['Jörg'],
            'São' => ['São'],
            'Cécile' => ['Cécile'],
            'Oğuzhan' => ['Oğuzhan'],
            'Kōhei' => ['Kōhei'],
            'Šárka' => ['Šárka'],
            'Hélène' => ['Hélène'],
            'Grégory' => ['Grégory'],
            'Yūma-Haruki' => ['Yūma-Haruki'],
            'Søren-Kai' => ['Søren-Kai'],
        ];
    }


    #[DataProvider('provideValidNames')]
    public function testCanBeUsedAsString(string $name): void
    {
        self::assertSame($name, FirstName::by($name)->asString());
    }


    public function testThrowsExceptionIfFirstNameIsEmpty(): void
    {
        $this->expectExceptionObject(new InvalidArgumentException('First name cannot be empty.'));
        FirstName::by('   ');
    }
}
