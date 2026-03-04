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
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use RuntimeException;

#[CoversClass(ControllerResolver::class)]
class ControllerResolverTest extends TestCase
{
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
        $managerFactoryMock = $factoryType === 'manager'
            ? $this->createMock(ManagerFactory::class)
            : $this->createStub(ManagerFactory::class);

        $trainerFactoryMock = $factoryType === 'trainer'
            ? $this->createMock(TrainerFactory::class)
            : $this->createStub(TrainerFactory::class);

        /** @var Stub&ControllerInterface $controllerStub */
        $controllerStub = $this->createStub($controllerClass);

        $this->configureFactoryMock(
            $factoryType,
            $managerFactoryMock,
            $expectedFactoryMethod,
            $controllerStub,
            $trainerFactoryMock
        );

        $resolver = new ControllerResolver($trainerFactoryMock, $managerFactoryMock);

        $name = $this->createMock(ControllerName::class);
        $name->expects($this->once())
            ->method('asString')
            ->willReturn($controllerClass);

        $this->assertEquals($controllerStub, $resolver->resolve($name));
    }


    private function configureFactoryMock(
        string $factoryType,
        ManagerFactory|MockObject $managerFactoryMock,
        string $expectedFactoryMethod,
        ControllerInterface $controllerStub,
        TrainerFactory|MockObject $trainerFactoryMock
    ): void {
        if ($factoryType === 'manager') {
            $managerFactoryMock
                ->expects($this->once())
                ->method($expectedFactoryMethod)
                ->willReturn($controllerStub);
        } else {
            $trainerFactoryMock
                ->expects($this->once())
                ->method($expectedFactoryMethod)
                ->willReturn($controllerStub);
        }
    }


    public function testThrowsExceptionIfControllerIsUnknown(): void
    {
        $this->expectExceptionObject(new RuntimeException('Unknown controller: ' . Login::class));

        $nameStub = $this->createStub(ControllerName::class);
        $nameStub->method('asString')
            ->willReturn(Login::class);

        $resolver = new ControllerResolver(
            $this->createStub(TrainerFactory::class),
            $this->createStub(ManagerFactory::class)
        );
        $resolver->resolve($nameStub);
    }
}
