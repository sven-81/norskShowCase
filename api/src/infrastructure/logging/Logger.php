<?php

declare(strict_types=1);

namespace norsk\api\infrastructure\logging;

use Katzgrau\KLogger\Logger as KLogger;
use norsk\api\infrastructure\config\Path;
use Psr\Log\LogLevel;
use Throwable;

class Logger
{
    private readonly KLogger $logger;


    private function __construct(Path $logPath)
    {
        $logFormat = "[{date}]\t[{level}]\t{message}";
        $logFileName = 'log_' . date('Y-m-d') . '.log';
        $this->logger = new KLogger(
            logDirectory: $logPath->asString(),
            logLevelThreshold: LogLevel::INFO,
            options: [
                'dateFormat' => 'Y-m-d H:i:s.u',
                'logFormat' => $logFormat,
                'filename' => $logFileName,
            ]
        );
    }


    public static function create(Path $logPath): self
    {
        return new self($logPath);
    }


    public function error(Throwable $throwable): void
    {
        $logMessage = 'Error-Code: ' . $throwable->getCode() . PHP_EOL
                      . 'File: ' . $throwable->getFile() . PHP_EOL
                      . 'Line: ' . $throwable->getLine() . PHP_EOL
                      . 'Message: ' . $throwable->getMessage() . PHP_EOL
                      . 'Stack: ' . $throwable->getTraceAsString() . PHP_EOL;

        $this->logger->error($logMessage);
    }


    public function info(LogMessage $logMessage): void
    {
        $this->logger->info($logMessage->asString());
    }
}
