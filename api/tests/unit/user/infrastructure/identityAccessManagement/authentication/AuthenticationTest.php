<?php

declare(strict_types=1);

namespace norsk\api\user\infrastructure\identityAccessManagement\authentication;

use norsk\api\shared\infrastructure\http\request\Payload;
use norsk\api\shared\infrastructure\http\response\responses\ErrorResponse;
use norsk\api\shared\infrastructure\http\response\UnauthorizedResponse;
use norsk\api\shared\infrastructure\http\response\Url;
use norsk\api\user\domain\model\JwtAuthenticatedUser;
use norsk\api\user\infrastructure\identityAccessManagement\jwt\JwtManagement;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RuntimeException;
use stdClass;

#[CoversClass(Authentication::class)]
class AuthenticationTest extends TestCase
{
    private MockObject|JwtManagement $jwtManagementMock;

    private Authentication $auth;

    private ServerRequestInterface|MockObject $requestMock;

    private Url $url;


    protected function setUp(): void
    {
        $this->requestMock = $this->createMock(ServerRequestInterface::class);

        $this->jwtManagementMock = $this->createMock(JwtManagement::class);
        $this->url = Url::by('http://foo');
        $this->auth = new Authentication($this->jwtManagementMock, $this->url);
    }


    public function testCanProcessAValidRequest(): void
    {
        $class = new stdClass();
        $class->nickname = 'someOne';
        $class->scope = 'is:user';
        $payload = Payload::by($class);
        $authenticatedUser = JwtAuthenticatedUser::byPayload($payload);

        $this->requestMock->expects($this->once())
            ->method('getHeader')
            ->willReturn(['Authorization']);

        $this->jwtManagementMock->expects($this->once())
            ->method('validate')
            ->willReturn($payload);

        $expectedRequest = $this->createStub(ServerRequestInterface::class);
        $this->requestMock
            ->expects($this->once())
            ->method('withAttribute')
            ->with('authenticatedUser', $authenticatedUser)
            ->willReturn($expectedRequest);

        $requestHandlerMock = $this->createMock(RequestHandlerInterface::class);
        $expectedResponse = $this->createStub(ResponseInterface::class);
        $requestHandlerMock
            ->expects($this->once())
            ->method('handle')
            ->with($expectedRequest)
            ->willReturn($expectedResponse);

        $result = $this->auth->process($this->requestMock, $requestHandlerMock);

        $this->assertSame($expectedResponse, $result);
    }


    public function testReturnsUnauthorizedResponseIfAuthHeaderIsMissing(): void
    {
        $this->requestMock->expects($this->once())
            ->method('getHeader')
            ->willReturn([]);

        $this->jwtManagementMock->expects($this->never())
            ->method('validate');

        $requestHandlerStub = $this->createStub(RequestHandlerInterface::class);
        $response = $this->auth->process($this->requestMock, $requestHandlerStub);
        self::assertEquals(
            UnauthorizedResponse::noHeader($this->url)->getBody()->getContents(),
            $response->getBody()->getContents()
        );
    }


    public function testThrowsExceptionOnFailure(): void
    {
        $this->requestMock->expects($this->once())
            ->method('getHeader')
            ->willReturn(['Authorization']);

        $exception = new RuntimeException('some error');
        $this->jwtManagementMock
            ->expects($this->once())
            ->method('validate')
            ->willThrowException($exception);

        $response = $this->auth->process($this->requestMock, $this->createStub(RequestHandlerInterface::class));
        self::assertEquals(
            ErrorResponse::unauthorized($this->url, $exception)->getBody()->getContents(),
            $response->getBody()->getContents()
        );
    }
}
