<?php

declare(strict_types=1);

namespace norsk\api\manager\infrastructure;

use norsk\api\infrastructure\config\AppConfig;
use norsk\api\infrastructure\logging\Logger;
use norsk\api\infrastructure\persistence\DbConnection;
use norsk\api\manager\infrastructure\web\controller\VerbManager;
use norsk\api\manager\infrastructure\web\controller\WordManager;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;


#[CoversClass(ManagerFactory::class)]
class ManagerFactoryTest extends TestCase
{
    private ManagerFactory $factory;


    protected function setUp(): void
    {
        $this->factory = new ManagerFactory(
            $this->createMock(Logger::class),
            $this->createMock(DbConnection::class),
            $this->createMock(AppConfig::class)
        );
    }


    public function testCreatesWordManager(): void
    {
        $wordManager = $this->factory->wordManager();

        $this->assertInstanceOf(WordManager::class, $wordManager);
    }


    public function testCreatesVerbManager(): void
    {
        $verbManager = $this->factory->verbManager();

        $this->assertInstanceOf(VerbManager::class, $verbManager);
    }
}

