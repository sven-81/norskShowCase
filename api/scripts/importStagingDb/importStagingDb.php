<?php

declare(strict_types=1);

namespace norsk\api;

use norsk\api\app\config\DbConfig;
use norsk\api\app\config\Path;
use norsk\api\tests\stubs\VirtualTestDatabase;
use Throwable;

require(__DIR__ . '/../../vendor/autoload.php');

function runScript(): void
{
    $mySqlConfig = Path::fromString(__DIR__ . '/../../configs/mySqlConfig.ini');
    $intDatabase = VirtualTestDatabase::create(DbConfig::fromPath($mySqlConfig));
    $initialSql = file_get_contents(__DIR__ . '/insertInitialStaging.sql');

    $intDatabase->insertInitialEntryToAvoidFailing($initialSql);

    $jwt = __DIR__ . '/../../configs/import.jwt';
    echo PHP_EOL . 'JWT for manager test: ' . PHP_EOL . $jwt . PHP_EOL . PHP_EOL;
}

try {
    runScript();
} catch (Throwable $throwable) {
    print ($throwable->getMessage());
}
