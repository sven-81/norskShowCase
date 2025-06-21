<?php

declare(strict_types=1);

namespace norsk\api\user;

use InvalidArgumentException;
use norsk\api\app\response\ResponseCode;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(UserName::class)]
class UserNameTest extends TestCase
{
    public static function provideUserNames(): array
    {
        return [
            '1234' => ['1234'],
            'som3UserName1973' => ['som3UserName1973'],
            'StarG@zer99' => ['StarG@zer99'],
            'C0smic_Dr@gon' => ['C0smic_Dr@gon'],
            'Mystic_Fl0wer7!' => ['Mystic_Fl0wer7!'],
            'Quantum#Leap42' => ['Quantum#Leap42'],
            'echo$Wave88' => ['echo$Wave88'],
            'Neon_Light23*' => ['Neon_Light23*'],
            'Shadow_Stalker56@' => ['Shadow_Stalker56@'],
            'Pixel_Pirate9#' => ['Pixel_Pirate9#'],
            'ThunderBolt!21' => ['ThunderBolt!21'],
            'Dreamer^Sky77' => ['Dreamer^Sky77'],
            'Cyber_Warrior3$' => ['Cyber_Warrior3$'],
            'Frosty*Flake88' => ['Frosty*Flake88'],
            'WildCard#007' => ['WildCard#007'],
            'Solar^Flare19!' => ['Solar^Flare19!'],
            'Ocean_Breeze5%' => ['Ocean_Breeze5%'],
            'Firefly!Dänce33' => ['Firefly!Dänce33'],
            'Lunar^Eclipse8*' => ['Lunar^Eclipse8*'],
            'Retro_Vibes24#' => ['Retro_Vibes24#'],
            'Cosmic*Explorer11@' => ['Cosmic*Explorer11@'],
            'Urban_Jungłe99$' => ['Urban_Jungłe99$'],
            'Mystic^Wanderer7!' => ['Mystic^Wanderer7!'],
            'Galactic#Nomad45' => ['Galactic#Nomad45'],
            'Infinity*Loøp88#' => ['Infinity*Loøp88#'],
        ];
    }


    public static function provideInvalidNames(): array
    {
        return [
            'space' => ['    '],
            'tooShort' => ['123'],
            'tooLong' => ['C0smic_Dr@gonInfinity*Loøp8789'],
        ];
    }


    public static function provideInvalidChars(): array
    {
        return [
            'Fir\'efly!Dänce33' => ['Fir\'efly!Dänce33'],
            'Velvet_Šky9&' => ['Velvet_Šky9&'],
        ];
    }


    #[DataProvider('provideUserNames')]
    public function testCanBeUsedAsString(string $name): void
    {
        self::assertSame($name, UserName::by($name)->asString());
    }


    #[DataProvider('provideInvalidNames')]
    public function testThrowsExceptionIfUserNameIsTooShortOrTooLong(string $invalid): void
    {
        $this->expectExceptionObject(
            new InvalidArgumentException(
                'The username must be between 4 and 30 characters long.',
                ResponseCode::unprocessable->value
            )
        );
        UserName::by($invalid);
    }


    #[DataProvider('provideInvalidChars')]
    public function testThrowsExceptionIfUserNameHasInvalidChars(string $invalid): void
    {
        $this->expectExceptionObject(
            new InvalidArgumentException(
                'User name contains invalid characters: \' or &.',
                ResponseCode::unprocessable->value
            )
        );
        UserName::by($invalid);
    }
}
