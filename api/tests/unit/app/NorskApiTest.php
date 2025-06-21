<?php

declare(strict_types=1);

namespace norsk\api\app;

use norsk\api\app\logging\AppLoggerConfig;
use norsk\api\app\logging\Logger;
use norsk\api\app\logging\LogMessage;
use norsk\api\app\routing\Router;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Slim\App;

#[CoversClass(NorskApi::class)]
class NorskApiTest extends TestCase
{
    public function testCanRun(): void
    {
        $loggerMock = $this->createMock(Logger::class);

        $matcher = $this->exactly(2);
        $loggerMock->expects($matcher)
            ->method('info')
            ->willReturnCallback(
                function (...$args) use ($matcher): void {
                    if ($matcher->numberOfInvocations() === 1) {
                        self::assertEquals([LogMessage::fromString('Starting Norsk API')], $args);
                    }
                    if ($matcher->numberOfInvocations() === 2) {
                        self::assertEquals([LogMessage::fromString('Stopping Norsk API')], $args);
                    }
                }
            );

        $routerMock = $this->createMock(Router::class);
        $routerMock->expects($this->once())
            ->method('run');

        $appMock = $this->createMock(App::class);
        $appMock->expects($this->once())
            ->method('addBodyParsingMiddleware');
        $appMock->expects($this->once())
            ->method('addRoutingMiddleware');
        $appMock->expects($this->once())
            ->method('addErrorMiddleware');
        $appMock->expects($this->once())
            ->method('run');

        $appLoggerConfigMock = $this->createMock(AppLoggerConfig::class);
        $appLoggerConfigMock->expects($this->once())
            ->method('isDisplayErrorDetails');
        $appLoggerConfigMock->expects($this->once())
            ->method('isLogErrors');
        $appLoggerConfigMock->expects($this->once())
            ->method('isLogErrorDetails');

        $api = new NorskApi($loggerMock, $routerMock, $appMock, $appLoggerConfigMock);
        $api->run();
    }
}
