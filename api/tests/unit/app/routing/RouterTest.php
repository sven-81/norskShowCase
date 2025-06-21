<?php

declare(strict_types=1);

namespace norsk\api\app\routing;

use norsk\api\app\identityAccessManagement\IdentityAccessManagementFactory;
use norsk\api\app\identityAccessManagement\Session;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Slim\App;
use Slim\Routing\Route;
use Slim\Routing\RouteCollectorProxy;

#[CoversClass(Router::class)]
class RouterTest extends TestCase
{
    private MockObject|IdentityAccessManagementFactory $identityAccessManagementMock;

    private Router $router;

    private MockObject|App $appMock;

    private Session|MockObject $sessionMock;


    public function testRunCallsIdentityAccessManagementForAuthenticationForGeneralApiRoute(): void
    {
        $this->identityAccessManagementMock->expects($this->once())
            ->method('createAuthentication');

        $_GET = '/api';
        $this->router->run($this->appMock, $this->sessionMock);
    }


    public function testRouteUserNewIsRegistered(): void
    {
        $groupMock = $this->createMock(RouteCollectorProxy::class);

        $matcher = $this->exactly(2);
        $groupMock->expects($matcher)
            ->method('post');

        $this->appMock->expects($this->once())
            ->method('group')
            ->with(
                '/api/v1',
                $this->callback(function ($callable) use ($groupMock) {
                    $callable($groupMock);

                    return true;
                })
            );

        $this->router->run($this->appMock, $this->sessionMock);
    }


    public function testRouteUsersAddsCorrectRoutes(): void
    {
        $routeMock = $this->createMock(Route::class);
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
                $routeMock,
                $routeMock
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
                $this->equalTo('/train/words'),
                $this->anything()
            );

        $groupMock->expects($this->once())
            ->method('patch')
            ->with('/train/words/{id:[0-9a-zA-Z]+}', $this->anything());

        $reflection = new ReflectionClass(Router::class);
        $method = $reflection->getMethod('routeWordTraining');

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


    protected function setUp(): void
    {
        $this->identityAccessManagementMock = $this->createMock(IdentityAccessManagementFactory::class);
        $contextMock = $this->createMock(Context::class);

        $this->appMock = $this->createMock(App::class);
        $this->sessionMock = $this->createMock(Session::class);
        $this->router = new Router($this->identityAccessManagementMock, $contextMock);
    }
}
