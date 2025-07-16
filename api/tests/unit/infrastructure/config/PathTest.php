<?php

declare(strict_types=1);

namespace norsk\api\infrastructure\config;

use norsk\api\infrastructure\config\Path;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Path::class)]
class PathTest extends TestCase
{
    private string $path;


    public function testCanBeUsedAsString(): void
    {
        $this->assertSame($this->path, Path::fromString($this->path)->asString());
    }


    protected function setUp(): void
    {
        $this->path = '/directory';
    }
}
