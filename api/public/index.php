<?php

declare(strict_types=1);

namespace norsk\api;

use norsk\api\infrastructure\config\AppConfig;
use norsk\api\infrastructure\config\DbConfig;
use norsk\api\infrastructure\config\Path;
use norsk\api\infrastructure\Factory;
use norsk\api\infrastructure\logging\Logger;
use norsk\api\shared\infrastructure\http\response\ResponseCode;
use RuntimeException;
use Throwable;

require(__DIR__ . '/../vendor/autoload.php');

try {
    runApi();
} catch (Throwable $throwable) {
    logException($throwable);
    throw new RuntimeException(
        'Could not initialize app:' . PHP_EOL
        . $throwable->getMessage(), ResponseCode::serverError->value
    );
}


function runApi(): void
{
    $appConfig = AppConfig::fromPath(
        Path::fromString(__DIR__ . '/../configs/appConfig.ini')
    );
    $dbConfig = DbConfig::fromPath(
        Path::fromString(__DIR__ . '/../configs/mySqlConfig.ini')
    );

    $factory = Factory::fromConfigs($appConfig, $dbConfig);
    $api = $factory->createNorskApi();
    $api->run();
}

function logException(Throwable $throwable): void
{
    $logger = Logger::create(Path::fromString(__DIR__ . '/../logs'));
    $logger->error($throwable);
}
