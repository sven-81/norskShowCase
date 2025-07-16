<?php

declare(strict_types=1);

namespace norsk\api\trainer\infrastructure;

use norsk\api\infrastructure\config\AppConfig;
use norsk\api\infrastructure\logging\Logger;
use norsk\api\infrastructure\persistence\DbConnection;
use norsk\api\trainer\infrastructure\web\controller\VerbTrainer;
use norsk\api\trainer\infrastructure\web\controller\WordTrainer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(TrainerFactory::class)]
class TrainerFactoryTest extends TestCase
{
    private TrainerFactory $factory;


    protected function setUp(): void
    {
        $this->factory = new TrainerFactory(
            $this->createMock(Logger::class),
            $this->createMock(DbConnection::class),
            $this->createMock(AppConfig::class)
        );
    }


    public function testCreatesWordTrainer(): void
    {
        $controller = $this->factory->wordTrainer();

        $this->assertInstanceOf(WordTrainer::class, $controller);
    }


    public function testCreatesVerbTrainer(): void
    {
        $controller = $this->factory->verbTrainer();

        $this->assertInstanceOf(VerbTrainer::class, $controller);
    }
}
