<?php

declare(strict_types=1);

namespace norsk\api\infrastructure\logging;

use norsk\api\infrastructure\config\Path;
use norsk\api\infrastructure\logging\AppLoggerConfig;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function PHPUnit\Framework\assertFalse;
use function PHPUnit\Framework\assertSame;
use function PHPUnit\Framework\assertTrue;

#[CoversClass(AppLoggerConfig::class)]
class AppLoggerConfigTest extends TestCase
{
    private Path $path;

    private AppLoggerConfig $loggingConfig;

    private AppLoggerConfig $noLoggingConfig;


    public function testCanGetPath(): void
    {
        assertSame($this->path, $this->loggingConfig->getPath());
    }


    public function testReturnsTrueForDisplayErrorDetails(): void
    {
        assertTrue($this->loggingConfig->isDisplayErrorDetails());
    }


    public function testReturnsTrueForLogErrors(): void
    {
        assertTrue($this->loggingConfig->isLogErrors());
    }


    public function testReturnsTrueForLogErrorDetails(): void
    {
        assertTrue($this->loggingConfig->isDisplayErrorDetails());
    }


    public function testReturnsFalseForDisplayErrorDetails(): void
    {
        assertFalse($this->noLoggingConfig->isDisplayErrorDetails());
    }


    public function testReturnsFalseForLogErrors(): void
    {
        assertFalse($this->noLoggingConfig->isLogErrors());
    }


    public function testReturnsFalseForLogErrorDetails(): void
    {
        assertFalse($this->noLoggingConfig->isLogErrorDetails());
    }


    protected function setUp(): void
    {
        $this->path = Path::fromString('/foo');

        $this->loggingConfig = AppLoggerConfig::by($this->path, true, true, true);
        $this->noLoggingConfig = AppLoggerConfig::by($this->path, false, false, false);
    }
}
