<?php

declare(strict_types=1);

namespace norsk\api\app\logging;

use InvalidArgumentException;
use norsk\api\app\config\File;
use norsk\api\app\config\Path;
use norsk\api\helperTools\DirectoryCleaner;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Logger::class)]
class LoggerTest extends TestCase
{
    private string $logFile;

    private Logger $logger;


    protected function setUp(): void
    {
        $path = __DIR__ . '/../../../logs/';
        $this->logger = Logger::create(Path::fromString($path));
        $this->logFile = $path . 'log_' . date('Y-m-d') . '.log';
    }


    public function testCanCreateLogFile(): void
    {
        $this->logger->info(LogMessage::fromString('some info'));

        self::assertFileExists($this->logFile);
    }


    public function testCanLogInfo(): void
    {
        $this->logger->info(LogMessage::fromString('some info'));
        self::assertMatchesRegularExpression(
            '#\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}.\d{6}]\t\[INFO]\tsome info#',
            file_get_contents($this->logFile)
        );
    }


    public function testCanLogError(): void
    {
        $throwable = new InvalidArgumentException('some error');
        $this->logger->error($throwable);

        self::assertMatchesRegularExpression(
            '#\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}.\d{6}]\t\[ERROR]\tError-Code: 0#',
            file_get_contents($this->logFile)
        );
    }


    protected function tearDown(): void
    {
        $cleaner = new DirectoryCleaner();
        $cleaner->deleteFileIfExists(
            File::fromPath(
                Path::fromString($this->logFile)
            )
        );
    }
}
