<?php

declare(strict_types=1);

namespace norsk\api\app\config;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use RuntimeException;

#[CoversClass(File::class)]
class FileTest extends TestCase
{
    public function testCanGetPath(): void
    {
        $path = Path::fromString('blupp.abc');

        self::assertEquals($path, File::fromPath($path)->getPath());
    }


    public function testCanParseIniFile(): void
    {
        $file = File::fromPath(Path::fromString(__DIR__ . '/../resources/mySqlConfig.test.ini'));
        $expected = parse_ini_file($file->getPath()->asString(), true);

        self::assertEquals($expected, $file->parseIniFile()->asArray());
    }


    public function testThrowsExceptionIfFileIsNotReadable(): void
    {
        $this->expectExceptionObject(new RuntimeException('Cannot read file:'));

        $path = __DIR__ . '/resources/noFile.ini';
        $file = File::fromPath(Path::fromString($path));
        $file->parseIniFile();
    }
}
