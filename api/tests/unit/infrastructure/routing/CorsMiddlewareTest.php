<?php

declare(strict_types=1);

namespace norsk\api\infrastructure\routing;

use GuzzleHttp\Psr7\Response;
use norsk\api\infrastructure\routing\CorsMiddleware;
use norsk\api\shared\infrastructure\http\response\ResponseCode;
use norsk\api\shared\infrastructure\http\response\Url;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

#[CoversClass(CorsMiddleware::class)]
class CorsMiddlewareTest extends TestCase
{

    public function testReturnsCorsOptionsResponseForOptionsRequest(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getMethod')
            ->willReturn('OPTIONS');

        $url = Url::by('http://foo.bar');
        $middleware = new CorsMiddleware($url);
        $response = $middleware->process($request, $this->createMock(RequestHandlerInterface::class));

        self::assertEquals(ResponseCode::success->value, $response->getStatusCode());
        self::assertTrue($response->hasHeader('Access-Control-Allow-Origin'));
    }


    public function testPassesNonOptionsRequestAndAppliesHeaders(): void
    {
        $url = Url::by('http://foo.bar');
        $middleware = new CorsMiddleware($url);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getMethod')
            ->willReturn('GET');

        $originalResponse = new Response();

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->method('handle')
            ->willReturn($originalResponse);

        $response = $middleware->process($request, $handler);

        self::assertTrue($response->hasHeader('Access-Control-Allow-Origin'));
        self::assertSame(['http://foo.bar'], $response->getHeader('Access-Control-Allow-Origin'));
    }
}
