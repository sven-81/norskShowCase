<?php

declare(strict_types=1);

namespace norsk\api\user;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(LastName::class)]
class LastNameTest extends TestCase
{
    public static function provideLastNames(): array
    {
        return [
            'Otto vor dem-Gäntßschenfelde' => ['Otto vor dem-Gäntßschenfelde', 'Otto vor dem-Gäntßschenfelde'],
            "O'Connor" => ["O'Connor", "O&#039;Connor"],
            'García-López' => ['García-López', 'García-López'],
            'Müller-Schmidt' => ['Müller-Schmidt', 'Müller-Schmidt'],
            'van der Meer' => ['van der Meer', 'van der Meer'],
            "D'Angelo" => ["D'Angelo", "D&#039;Angelo"],
            'DiCaprio' => ['DiCaprio', 'DiCaprio'],
            'Fernández-González' => ['Fernández-González', 'Fernández-González'],
            'von Hohenberg' => ['von Hohenberg', 'von Hohenberg'],
            'López-Morales' => ['López-Morales', 'López-Morales'],
            'MacLeod' => ['MacLeod', 'MacLeod'],
            'de la Cruz' => ['de la Cruz', 'de la Cruz'],
            'Nascimënto-Šilva' => ['Nascimënto-Šilva', 'Nascimënto-Šilva'],
            'Kwiatkowski' => ['Kwiatkowski', 'Kwiatkowski'],
            'Sánchez-Carrillo' => ['Sánchez-Carrillo', 'Sánchez-Carrillo'],
            'Al-Mansōūri' => ['Al-Mansōūri', 'Al-Mansōūri'],
            'Björkman' => ['Björkman', 'Björkman'],
            'Zhang-Wang' => ['Zhang-Wang', 'Zhang-Wang'],
            "O'Reilly" => ["O'Reilly", "O&#039;Reilly"],
            'de Jongh' => ['de Jongh', 'de Jongh'],
            'Pachecø-Ramirez' => ['Pachecø-Ramirez', 'Pachecø-Ramirez'],
            'Schmitt-Engelhardt' => ['Schmitt-Engelhardt', 'Schmitt-Engelhardt'],
            'Lecłercq-Dupont' => ['Lecłercq-Dupont', 'Lecłercq-Dupont'],
            'Tzeng-Huang' => ['Tzeng-Huang', 'Tzeng-Huang'],
            'Córdova-Benitez' => ['Córdova-Benitez', 'Córdova-Benitez'],
            'van der Linden' => ['van der Linden', 'van der Linden'],
        ];
    }


    #[DataProvider('provideLastNames')]
    public function testCanBeUsedAsString(string $name, string $expected): void
    {
        self::assertSame($expected, LastName::by($name)->asString());
    }


    public function testThrowsExceptionIfLastNameIsEmpty(): void
    {
        $this->expectExceptionObject(new InvalidArgumentException('Last name cannot be empty.'));
        LastName::by('   ');
    }
}
