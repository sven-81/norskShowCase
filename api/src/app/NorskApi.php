<?php

declare(strict_types=1);

namespace norsk\api\app;

use norsk\api\app\identityAccessManagement\Session;
use norsk\api\app\logging\AppLoggerConfig;
use norsk\api\app\logging\Logger;
use norsk\api\app\logging\LogMessage;
use norsk\api\app\routing\Router;
use Slim\App;

class NorskApi
{
    public function __construct(
        private readonly Logger $logger,
        private readonly Router $router,
        private readonly App $app,
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

        $session = Session::create();
        $this->router->run($this->app, $session);
        $this->app->run();
        $session->destroy();
        $this->logger->info(LogMessage::fromString('Stopping Norsk API'));
    }
}
