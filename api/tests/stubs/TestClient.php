<?php

declare(strict_types=1);

namespace norsk\api\tests\stubs;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use League\OpenAPIValidation\PSR7\OperationAddress;
use League\OpenAPIValidation\PSR7\ValidatorBuilder;
use norsk\api\shared\application\Json;
use Psr\Http\Message\ResponseInterface;

class TestClient
{
    private const string DOCKER_SERVICE_NAME_BASE_URI = 'http://api/api/v1/:80';


    public static function createWithApiDocValidation(
        string $bearerPath,
        string $method,
        string $uri,
        ?Json  $requestBody = null,
    ): ResponseInterface
    {
        $client = new Client([
            'handler' => self::createHandlerStack(),
            'base_uri' => self::DOCKER_SERVICE_NAME_BASE_URI,
        ]);

        $options = self::getOptions($requestBody, $bearerPath);

        return $client->request($method, $uri, $options);
    }


    public static function createWithoutApiDocValidation(
        string $bearerPath,
        string $method,
        string $uri,
        ?Json  $requestBody = null,
    ): ResponseInterface
    {
        $client = new Client(
            [
                'base_uri' => self::DOCKER_SERVICE_NAME_BASE_URI,
            ]
        );

        $options = self::getOptions($requestBody, $bearerPath);

        return $client->request($method, $uri, $options);
    }


    private static function getOptions(?Json $requestBody, string $bearerPath): array
    {
        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . file_get_contents($bearerPath),
            'tokenType' => 'Bearer',
            'expiresIn' => 7200,
        ];

        if ($requestBody !== null) {
            return ['headers' => $headers, 'body' => $requestBody->asString()];
        }

        return ['headers' => $headers];
    }

    private static function createHandlerStack(): HandlerStack
    {
        $middleware = self::openApiMiddleware();

        $stack = HandlerStack::create();
        $stack->push($middleware, 'openapi_validation');

        return $stack;
    }

    private static function openApiMiddleware(): callable
    {
        $builder = new ValidatorBuilder();
        $builder->fromYamlFile(__DIR__ . '/../../../tools/norskApi.yaml');

        $requestValidator = $builder->getRequestValidator();
        $responseValidator = $builder->getResponseValidator();

        return static function (callable $handler) use ($requestValidator, $responseValidator) {

            return static function ($request, array $options) use ($handler, $requestValidator, $responseValidator) {
                self::validateRequest($requestValidator, $request);
                $promise = $handler($request, $options);

                return $promise->then(fn($response) => self::validateResponse($responseValidator, $request, $response));
            };
        };
    }

    private static function validateRequest($validator, $request): void
    {
        $validator->validate($request);
    }

    private static function validateResponse($validator, $request, $response)
    {
        $path = $request->getUri()->getPath();
        $method = strtolower($request->getMethod());
        $operationAddress = new OperationAddress($path, $method);
        $validator->validate($operationAddress, $response);

        return $response;
    }
}
