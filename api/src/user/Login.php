<?php

declare(strict_types=1);

namespace norsk\api\user;

use Exception;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest as Request;
use norsk\api\app\identityAccessManagement\JwtManagement;
use norsk\api\app\logging\Logger;
use norsk\api\app\logging\LogMessage;
use norsk\api\app\request\Parameter;
use norsk\api\app\request\Payload;
use norsk\api\app\response\ResponseCode;
use norsk\api\app\response\Url;
use norsk\api\shared\responses\ErrorResponse;
use norsk\api\shared\responses\SuccessResponse;
use norsk\api\user\exceptions\ParameterMissingException;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class Login
{
    private const string USERNAME = 'username';
    private const string PASSWORD = 'password';


    public function __construct(
        private readonly Logger $logger,
        private readonly UsersReader $usersReader,
        private readonly JwtManagement $jwtManagement,
        private readonly Pepper $pepper,
        private readonly Url $url,
    ) {
    }


    public function run(Request $request): ResponseInterface
    {
        return $this->createResponseForLogin($request);
    }


    private function createResponseForLogin(Request $request): ResponseInterface
    {
        try {
            $this->logger->info(LogMessage::fromString('Getting request for user login'));
            $payload = Payload::of($request);
            $validatedUser = $this->getUser($payload);
            $jwToken = $this->jwtManagement->create($validatedUser);

            $loggedInUser = LoggedInUser::by($validatedUser, $jwToken);
            $this->logger->info(
                LogMessage::fromString('User verified successfully: ' . $loggedInUser->getUserName()->asString())
            );

            $body = $loggedInUser->asBodyJson();

            return SuccessResponse::loggedIn($this->url, $body);
        } catch (Throwable $throwable) {
            $this->logger->error($throwable);

            return match ($throwable->getCode()) {
                ResponseCode::unauthorized->value => $this->credentialsAreInvalidOrUserDoesNotExistResponse($throwable),
                ResponseCode::forbidden->value => $this->noActiveUserResponse($throwable),
                ResponseCode::badRequest->value => $this->parameterMissingResponse($throwable),
                ResponseCode::unprocessable->value => $this->inputNotValidResponse($throwable),
                default => ErrorResponse::serverError($this->url, $throwable),
            };
        }
    }


    private function getUser(Payload $payload): ValidatedUser
    {
        try {
            $payloadArray = $payload->asArray();

            $userName = UserName::by($payloadArray[self::USERNAME]);
            $inputPassword = InputPassword::by($payloadArray[self::PASSWORD]);

            return $this->usersReader->getDataFor($userName, $inputPassword, $this->pepper);
        } catch (Throwable $throwable) {
            $this->logger->error($throwable);
            $field = $this->getMissingField($throwable);
            $this->throwMissingFieldOrPreviousThrowable($field, $throwable);
        }
    }


    private function getMissingField(Throwable|Exception $throwable): ?string
    {
        preg_match('#.*(\(\$(.*)\)).*#', $throwable->getMessage(), $matches);

        if (isset($matches[2])) {
            return $matches[2];
        }

        return null;
    }


    private function credentialsAreInvalidOrUserDoesNotExistResponse(Throwable|Exception $throwable): Response
    {
        return ErrorResponse::unauthorized($this->url, $throwable);
    }


    private function noActiveUserResponse(Throwable|Exception $throwable): Response
    {
        return ErrorResponse::forbidden($this->url, $throwable);
    }


    private function parameterMissingResponse(Throwable|Exception $throwable): Response
    {
        return ErrorResponse::badRequest($this->url, $throwable);
    }


    private function throwMissingFieldOrPreviousThrowable(?string $field, Throwable|Exception $throwable): void
    {
        if ($field !== null) {
            throw new ParameterMissingException(Parameter::by($field));
        }

        throw $throwable;
    }


    private function inputNotValidResponse(Throwable|Exception $throwable): Response
    {
        return ErrorResponse::unprocessable($this->url, $throwable);
    }
}
