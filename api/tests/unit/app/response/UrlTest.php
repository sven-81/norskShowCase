<?php

declare(strict_types=1);

namespace norsk\api\app\response;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Url::class)]
class UrlTest extends TestCase
{

    public static function getValidUrl(): array
    {
        return [
            'http' => ['http://someUrl'],
            'https' => ['https://someUrl'],
        ];
    }


    #[DataProvider('getValidUrl')]
    public function testUseUrlAsString(string $url): void
    {
        $this->assertSame($url, Url::by($url)->asString());
    }


    public function testThrowsExceptionIfUrlIsInvalid(): void
    {
        $this->expectExceptionObject(new InvalidArgumentException('The given URL is not a valid URL: false'));
        Url::by('false');
    }
}
