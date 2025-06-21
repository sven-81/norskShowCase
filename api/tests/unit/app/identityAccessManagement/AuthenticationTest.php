<?php

declare(strict_types=1);

namespace norsk\api\app\identityAccessManagement;

use norsk\api\app\response\UnauthorizedResponse;
use norsk\api\app\response\Url;
use norsk\api\shared\responses\ErrorResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RuntimeException;

#[CoversClass(Authentication::class)]
class AuthenticationTest extends TestCase
{
    private MockObject|JwtManagement $jwtManagementMock;

    private Authentication $auth;

    private ServerRequestInterface|MockObject $requestMock;

    private MockObject|RequestHandlerInterface $requestHandlerMock;

    private Url $url;


    protected function setUp(): void
    {
        $this->requestMock = $this->createMock(ServerRequestInterface::class);
        $this->requestHandlerMock = $this->createMock(RequestHandlerInterface::class);

        $this->jwtManagementMock = $this->createMock(JwtManagement::class);
        $sessionMock = $this->createMock(Session::class);
        $this->url = Url::by('http://foo');
        $this->auth = new Authentication($this->jwtManagementMock, $sessionMock, $this->url);
    }


    public function testCanProcessAValidRequest(): void
    {
        $this->requestMock->method('getHeader')
            ->willReturn(['Authorization']);

        $this->jwtManagementMock->expects($this->once())
            ->method('validate');
        $this->requestHandlerMock->expects($this->once())
            ->method('handle');

        $this->auth->process($this->requestMock, $this->requestHandlerMock);
    }


    public function testReturnsUnauthorizedResponseIfAuthHeaderIsMissing(): void
    {
        $this->requestMock->method('getHeader')
            ->willReturn([]);

        $this->jwtManagementMock->expects($this->never())
            ->method('validate');
        $this->requestHandlerMock->expects($this->never())
            ->method('handle');

        $response = $this->auth->process($this->requestMock, $this->requestHandlerMock);
        self::assertEquals(
            UnauthorizedResponse::noHeader($this->url)->getBody()->getContents(),
            $response->getBody()->getContents()
        );
    }


    public function testThrowsExceptionOnFailure(): void
    {
        $this->requestMock->method('getHeader')
            ->willReturn(['Authorization']);

        $exception = new RuntimeException('some error');
        $this->jwtManagementMock
            ->method('validate')
            ->willThrowException($exception);

        $response = $this->auth->process($this->requestMock, $this->requestHandlerMock);
        self::assertEquals(
            ErrorResponse::unauthorized($this->url, $exception)->getBody()->getContents(),
            $response->getBody()->getContents()
        );
    }
}
