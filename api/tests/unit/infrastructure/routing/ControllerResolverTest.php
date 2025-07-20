<?php

declare(strict_types=1);

namespace norsk\api\infrastructure\routing;

use norsk\api\manager\infrastructure\ManagerFactory;
use norsk\api\manager\infrastructure\web\controller\VerbManager;
use norsk\api\manager\infrastructure\web\controller\WordManager;
use norsk\api\trainer\infrastructure\TrainerFactory;
use norsk\api\trainer\infrastructure\web\controller\VerbTrainer;
use norsk\api\trainer\infrastructure\web\controller\WordTrainer;
use norsk\api\user\infrastructure\web\controller\Login;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;

#[CoversClass(ControllerResolver::class)]
class ControllerResolverTest extends TestCase
{
    private ManagerFactory|MockObject $managerFactoryMock;

    private TrainerFactory|MockObject $trainerFactoryMock;


    protected function setUp(): void
    {
        $this->managerFactoryMock = $this->createMock(ManagerFactory::class);
        $this->trainerFactoryMock = $this->createMock(TrainerFactory::class);
    }


    public static function getControl(): array
    {
        return [
            'wordManager' => [WordManager::class, 'wordManager', 'manager'],
            'verbManager' => [VerbManager::class, 'verbManager', 'manager'],
            'wordTrainer' => [WordTrainer::class, 'wordTrainer', 'trainer'],
            'verbTrainer' => [VerbTrainer::class, 'verbTrainer', 'trainer'],
        ];
    }


    #[DataProvider('getControl')]
    public function testCanResolveController(
        string $controllerClass,
        string $expectedFactoryMethod,
        string $factoryType
    ): void {
        /** @var ControllerInterface|MockObject $controllerMock */
        $controllerMock = $this->createMock($controllerClass);

        $this->getFactoryMock(
            $factoryType,
            $this->managerFactoryMock,
            $expectedFactoryMethod,
            $controllerMock,
            $this->trainerFactoryMock
        );
        $resolver = new ControllerResolver($this->trainerFactoryMock, $this->managerFactoryMock);

        $name = $this->createMock(ControllerName::class);
        $name->expects($this->once())
            ->method('asString')
            ->willReturn($controllerClass);

        $this->assertEquals($controllerMock, $resolver->resolve($name));
    }


    private function getFactoryMock(
        string $factoryType,
        ManagerFactory|MockObject $managerFactoryMock,
        string $expectedFactoryMethod,
        ControllerInterface|MockObject $controllerMock,
        TrainerFactory|MockObject $trainerFactoryMock
    ): void {
        if ($factoryType === 'manager') {
            $managerFactoryMock
                ->expects($this->once())
                ->method($expectedFactoryMethod)
                ->willReturn($controllerMock);
        } else {
            $trainerFactoryMock
                ->expects($this->once())
                ->method($expectedFactoryMethod)
                ->willReturn($controllerMock);
        }
    }


    public function testThrowsExceptionIfControllerIsUnknown(): void
    {
        $this->expectExceptionObject(new RuntimeException('Unknown controller: ' . Login::class));

        $name = $this->createMock(ControllerName::class);
        $name->method('asString')
            ->willReturn(Login::class);

        $resolver = new ControllerResolver($this->trainerFactoryMock, $this->managerFactoryMock);
        $resolver->resolve($name);
    }
}
