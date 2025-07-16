<?php

declare(strict_types=1);

namespace norsk\api\infrastructure\routing;

use norsk\api\shared\infrastructure\http\response\ResponseHeaderApplier;
use norsk\api\shared\infrastructure\http\response\ResponseHeaders;
use norsk\api\shared\infrastructure\http\response\responses\SuccessResponse;
use norsk\api\shared\infrastructure\http\response\Url;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class CorsMiddleware implements MiddlewareInterface
{
    private ResponseHeaders $responseHeaders;


    public function __construct(private readonly Url $url)
    {
        $this->responseHeaders = ResponseHeaders::create($this->url);
    }


    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($request->getMethod() === 'OPTIONS') {
            return SuccessResponse::corsOptions($this->url);
        }

        return $this->passRequestToNextMiddleware($handler, $request);
    }


    private function passRequestToNextMiddleware(
        RequestHandlerInterface $handler,
        ServerRequestInterface $request
    ): ResponseInterface {
        $response = $handler->handle($request);
        $headerApplier = ResponseHeaderApplier::fromHeaders($this->responseHeaders);

        return $headerApplier->apply($response);
    }
}