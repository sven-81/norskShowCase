<?php

declare(strict_types=1);

namespace norsk\api\infrastructure;

use norsk\api\infrastructure\config\AppConfig;
use norsk\api\infrastructure\config\DbConfig;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Factory::class)]
class FactoryTest extends TestCase
{
    public function testCanCreateNorskApi(): void
    {
        $appConfigMock = $this->createMock(AppConfig::class);
        $dbConfigMock = $this->createMock(DbConfig::class);
        $factory = Factory::fromConfigs($appConfigMock, $dbConfigMock);

        self::assertInstanceOf(NorskApi::class, $factory->createNorskApi());
    }
}
