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

    private ResponseInterface $responseStub;

    private WordTrainer|MockObject $controllerMock;

    private ResponseInterface $controllerResponseStub;

    private ControllerResolver|MockObject $controllerResolverMock;

    private ControllerName $controllerNameStub;


    protected function setUp(): void
    {
        $this->method = Method::of('saveSuccess');
        $this->requestMock = $this->createMock(ServerRequestInterface::class);
        $this->responseStub = $this->createStub(ResponseInterface::class);

        $this->controllerMock = $this->createMock(WordTrainer::class);
        $this->controllerResponseStub = $this->createStub(ResponseInterface::class);

        $this->controllerResolverMock = $this->createMock(ControllerResolver::class);
        $this->controllerNameStub = $this->createStub(ControllerName::class);
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

        $this->requestMock->expects($this->exactly(2))
            ->method('getAttribute')
            ->with(self::AUTHENTICATED_USER)
            ->willReturn($userMock);

        $this->controllerResolverMock
            ->expects($this->once())
            ->method('resolve')
            ->with($this->controllerNameStub)
            ->willReturn($this->controllerMock);

        $user = JwtAuthenticatedUser::byRequest($this->requestMock);

        $this->controllerMock->expects($this->once())
            ->method('saveSuccess')
            ->with($user, $this->requestMock)
            ->willReturn($this->controllerResponseStub);

        $handler = RouteHandlerWithAuthenticatedUser::by(
            $this->controllerResolverMock,
            $this->controllerNameStub,
            $this->method
        );
        $result = $handler($this->requestMock, $this->responseStub, []);

        $this->assertSame($this->controllerResponseStub, $result);
    }
}
