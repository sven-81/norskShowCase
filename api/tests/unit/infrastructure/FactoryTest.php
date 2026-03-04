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
        $appConfigStub = $this->createStub(AppConfig::class);
        $dbConfigStub = $this->createStub(DbConfig::class);
        $factory = Factory::fromConfigs($appConfigStub, $dbConfigStub);

        self::assertInstanceOf(NorskApi::class, $factory->createNorskApi());
    }
}
