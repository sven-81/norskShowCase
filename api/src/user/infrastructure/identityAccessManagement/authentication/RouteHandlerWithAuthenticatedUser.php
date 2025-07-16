<?php

declare(strict_types=1);

namespace norsk\api\user\infrastructure\identityAccessManagement\authentication;

use norsk\api\infrastructure\routing\ControllerName;
use norsk\api\infrastructure\routing\ControllerResolver;
use norsk\api\infrastructure\routing\Method;
use norsk\api\user\domain\model\JwtAuthenticatedUser;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

readonly class RouteHandlerWithAuthenticatedUser
{

    private function __construct(
        private ControllerResolver $controllerResolver,
        private ControllerName $controllerName,
        private Method $method
    ) {
    }


    public static function by(
        ControllerResolver $controllerResolver,
        ControllerName $controllerName,
        Method $method
    ): self {
        return new self($controllerResolver, $controllerName, $method);
    }


    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        $user = JwtAuthenticatedUser::byRequest($request);

        $controller = $this->controllerResolver->resolve($this->controllerName);
        $methodName = $this->method->asString();

        return $controller->$methodName($user, $request);
    }
}
