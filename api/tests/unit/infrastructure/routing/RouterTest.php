<?php

declare(strict_types=1);

namespace norsk\api\infrastructure\routing;

use norsk\api\user\infrastructure\identityAccessManagement\IdentityAccessManagementFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Slim\App;
use Slim\Routing\Route;
use Slim\Routing\RouteCollectorProxy;

#[CoversClass(Router::class)]
class RouterTest extends TestCase
{
    private Router $router;


    protected function setUp(): void
    {
        $contextStub = $this->createStub(Context::class);
        $controllerResolverStub = $this->createStub(ControllerResolver::class);
        $identityAccessManagementStub = $this->createStub(IdentityAccessManagementFactory::class);

        $this->router = new Router($identityAccessManagementStub, $contextStub, $controllerResolverStub);
    }


    public function testRunCallsIdentityAccessManagementForAuthenticationForGeneralApiRoute(): void
    {
        $identityAccessManagementMock = $this->createMock(IdentityAccessManagementFactory::class);
        $identityAccessManagementMock->expects($this->once())
            ->method('createAuthentication');

        $router = new Router(
            $identityAccessManagementMock,
            $this->createStub(Context::class),
            $this->createStub(ControllerResolver::class)
        );

        $_GET = '/api';
        $router->run($this->createStub(App::class));
    }


    public function testRouteUserNewIsRegistered(): void
    {
        $groupMock = $this->createMock(RouteCollectorProxy::class);

        $matcher = $this->exactly(2);
        $groupMock->expects($matcher)
            ->method('post');

        $appMock = $this->createMock(App::class);
        $appMock->expects($this->once())
            ->method('group')
            ->with(
                '/api/v1',
                $this->callback(function ($callable) use ($groupMock) {
                    $callable($groupMock);

                    return true;
                })
            );

        $this->router->run($appMock);
    }


    public function testRouteUsersAddsCorrectRoutes(): void
    {
        $routeStub = $this->createStub(Route::class);
        $groupMock = $this->createMock(RouteCollectorProxy::class);

        $matcher = $this->exactly(2);
        $groupMock->expects($matcher)
            ->method('post')
            ->willReturnCallback(
                function (...$args) use ($matcher): void {
                    if ($matcher->numberOfInvocations() === 1) {
                        self::assertArrayIsEqualToArrayIgnoringListOfKeys(['/user/new'], $args, [1]);
                    }
                    if ($matcher->numberOfInvocations() === 2) {
                        self::assertArrayIsEqualToArrayIgnoringListOfKeys(['/user'], $args, [1]);
                    }
                }
            )
            ->willReturn(
                $routeStub,
                $routeStub
            );

        $reflection = new ReflectionClass(Router::class);
        $method = $reflection->getMethod('routeUsers');
        $method->invokeArgs($this->router, [$groupMock]);
    }


    public function testRouteWordTrainingAddsCorrectRoutes(): void
    {
        $groupMock = $this->createMock(RouteCollectorProxy::class);
        $groupMock->expects($this->once())
            ->method('get')
            ->with(
                $this->equalTo('/words'),
                $this->anything()
            );

        $groupMock->expects($this->once())
            ->method('patch')
            ->with('/words/{id:[0-9a-zA-Z]+}', $this->anything());

        $reflection = new ReflectionClass(Router::class);
        $method = $reflection->getMethod('trainWords');

        $method->invokeArgs($this->router, [$groupMock]);
    }


    public function testRouteVerbTrainingAddsCorrectRoutes(): void
    {
        $groupMock = $this->createMock(RouteCollectorProxy::class);
        $groupMock->expects($this->once())
            ->method('get')
            ->with(
                $this->equalTo('/verbs'),
                $this->anything()
            );

        $groupMock->expects($this->once())
            ->method('patch')
            ->with('/verbs/{id:[0-9a-zA-Z]+}', $this->anything());

        $reflection = new ReflectionClass(Router::class);
        $method = $reflection->getMethod('trainVerbs');

        $method->invokeArgs($this->router, [$groupMock]);
    }


    public function testRouteManageWordsAddsCorrectRoutes(): void
    {
        $groupMock = $this->createMock(RouteCollectorProxy::class);
        $groupMock->expects($this->once())
            ->method('get')
            ->with(
                $this->equalTo('/words'),
                $this->anything()
            );

        $groupMock->expects($this->once())
            ->method('post')
            ->with('/words', $this->anything());
        $groupMock->expects($this->once())
            ->method('put')
            ->with('/words/{id:[0-9]+}', $this->anything());
        $groupMock->expects($this->once())
            ->method('delete')
            ->with('/words/{id:[0-9]+}', $this->anything());

        $reflection = new ReflectionClass(Router::class);
        $method = $reflection->getMethod('manageWords');

        $method->invokeArgs($this->router, [$groupMock]);
    }


    public function testRouteManageVerbsAddsCorrectRoutes(): void
    {
        $groupMock = $this->createMock(RouteCollectorProxy::class);
        $groupMock->expects($this->once())
            ->method('get')
            ->with(
                $this->equalTo('/verbs'),
                $this->anything()
            );

        $groupMock->expects($this->once())
            ->method('post')
            ->with('/verbs', $this->anything());
        $groupMock->expects($this->once())
            ->method('put')
            ->with('/verbs/{id:[0-9]+}', $this->anything());
        $groupMock->expects($this->once())
            ->method('delete')
            ->with('/verbs/{id:[0-9]+}', $this->anything());

        $reflection = new ReflectionClass(Router::class);
        $method = $reflection->getMethod('manageVerbs');

        $method->invokeArgs($this->router, [$groupMock]);
    }
}
