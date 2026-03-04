<?php

declare(strict_types=1);

namespace norsk\api\infrastructure\logging;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger as MonologLogger;
use norsk\api\infrastructure\config\Path;
use Throwable;

readonly class Logger
{
    private MonologLogger $logger;


    private function __construct(Path $logPath)
    {
        $logFileName = $logPath->asString() . '/log_' . date('Y-m-d') . '.log';

        $formatter = new LineFormatter(
            format: "[%datetime%]\t[%level_name%]\t%message%\n",
            dateFormat: 'Y-m-d H:i:s.u',
            allowInlineLineBreaks: true,
            ignoreEmptyContextAndExtra: true,
        );

        $handler = new StreamHandler(stream: $logFileName, level: Level::Info);
        $handler->setFormatter($formatter);

        $this->logger = new MonologLogger(name: 'norsk');
        $this->logger->pushHandler($handler);
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
