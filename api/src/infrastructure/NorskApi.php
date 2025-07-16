<?php

declare(strict_types=1);

namespace norsk\api\infrastructure;

use norsk\api\infrastructure\logging\AppLoggerConfig;
use norsk\api\infrastructure\logging\Logger;
use norsk\api\infrastructure\logging\LogMessage;
use norsk\api\infrastructure\routing\CorsMiddleware;
use norsk\api\infrastructure\routing\Router;
use Slim\App;

class NorskApi
{
    public function __construct(
        private readonly Logger $logger,
        private readonly Router $router,
        private readonly App $app,
        private readonly CorsMiddleware $corsMiddleware,
        private readonly AppLoggerConfig $appLoggerConfig,
    ) {
    }


    public function run(): void
    {
        $this->logger->info(LogMessage::fromString('Starting Norsk API'));

        $this->app->addBodyParsingMiddleware();
        $this->app->addRoutingMiddleware();
        $this->app->addErrorMiddleware(
            $this->appLoggerConfig->isDisplayErrorDetails(),
            $this->appLoggerConfig->isLogErrors(),
            $this->appLoggerConfig->isLogErrorDetails()
        );
        $this->app->addMiddleware($this->corsMiddleware);

        $this->router->run($this->app);
        $this->app->run();
        $this->logger->info(LogMessage::fromString('Stopping Norsk API'));
    }
}
