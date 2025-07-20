<?php

declare(strict_types=1);

namespace norsk\api\user\infrastructure\identityAccessManagement\authentication;

use norsk\api\infrastructure\routing\ControllerName;
use norsk\api\infrastructure\routing\ControllerResolver;
use norsk\api\infrastructure\routing\Method;
use norsk\api\trainer\infrastructure\web\controller\WordTrainer;
use norsk\api\user\domain\model\JwtAuthenticatedUser;
use norsk\api\user\domain\model\Role;
use norsk\api\user\domain\valueObjects\UserName;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use stdClass;

#[CoversClass(RouteHandlerWithAuthenticatedUser::class)]
class RouteHandlerWithAuthenticatedUserTest extends TestCase
{
    private const string AUTHENTICATED_USER = 'authenticatedUser';

    private Method $method;

    private ServerRequestInterface|MockObject $requestMock;

    private ResponseInterface|MockObject $responseMock;

    private WordTrainer|MockObject $controllerMock;

    private ResponseInterface|MockObject $controllerResponseMock;

    private ControllerResolver|MockObject $controllerResolverMock;

    private ControllerName|MockObject $controllerNameMock;


    protected function setUp(): void
    {
        $this->method = Method::of('saveSuccess');
        $this->requestMock = $this->createMock(ServerRequestInterface::class);
        $this->responseMock = $this->createMock(ResponseInterface::class);

        $this->controllerMock = $this->createMock(WordTrainer::class);
        $this->controllerResponseMock = $this->createMock(ResponseInterface::class);

        $this->controllerResolverMock = $this->createMock(ControllerResolver::class);
        $this->controllerNameMock = $this->createMock(ControllerName::class);
    }


    public function testCanGetResponse(): void
    {
        $userMock = $this->createMock(JwtAuthenticatedUser::class);
        $userMock->expects($this->exactly(2))
            ->method('getUserName')
            ->willReturn(UserName::by('someUser'));
        $userMock->expects($this->exactly(2))
            ->method('getRole')
            ->willReturn(Role::MANAGER);

        $this->requestMock->method('getAttribute')
            ->with(self::AUTHENTICATED_USER)
            ->willReturn($userMock);

        $this->controllerResolverMock
            ->method('resolve')
            ->with($this->controllerNameMock)
            ->willReturn($this->controllerMock);

        $user = JwtAuthenticatedUser::byRequest($this->requestMock);

        $this->controllerMock->expects($this->once())
            ->method('saveSuccess')
            ->with($user, $this->requestMock)
            ->willReturn($this->controllerResponseMock);

        $handler = RouteHandlerWithAuthenticatedUser::by(
            $this->controllerResolverMock,
            $this->controllerNameMock,
            $this->method
        );
        $result = $handler($this->requestMock, $this->responseMock, []);

        $this->assertSame($this->controllerResponseMock, $result);
    }
}
