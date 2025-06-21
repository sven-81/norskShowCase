<?php

declare(strict_types=1);

namespace norsk\api\user;

use Exception;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest as Request;
use norsk\api\app\logging\Logger;
use norsk\api\app\logging\LogMessage;
use norsk\api\app\request\Payload;
use norsk\api\app\response\ResponseCode;
use norsk\api\app\response\Url;
use norsk\api\shared\responses\ConflictResponse;
use norsk\api\shared\responses\CreatedResponse;
use norsk\api\shared\responses\ErrorResponse;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class Registration
{
    private const int DUPLICATE_KEY = 1062;


    public function __construct(
        private readonly Logger $logger,
        private readonly UsersWriter $usersWriter,
        private readonly PasswordVector $passwordVector,
        private readonly Url $url
    ) {
    }


    public function registerUser(Request $request): ResponseInterface
    {
        return $this->createRegisterResponse($request);
    }


    private function createRegisterResponse(Request $request): ResponseInterface
    {
        try {
            $this->logger->info(LogMessage::fromString('Getting request for user registration'));
            $payload = Payload::of($request);
            $user = RegisteredUser::createByPayload($payload, $this->passwordVector);
            $this->usersWriter->add($user);
            $this->logger->info(LogMessage::fromString('Added new User: ' . $user->getUserName()->asString()));

            return CreatedResponse::savedNewUser($this->url);
        } catch (Throwable $throwable) {
            $this->logger->error($throwable);

            return match ($throwable->getCode()) {
                ResponseCode::badRequest->value => $this->parameterMissingResponse($throwable),
                ResponseCode::unprocessable->value => $this->invalidPasswordResponse($throwable),
                self::DUPLICATE_KEY => $this->userAlreadyExistsResponse(),
                default => ErrorResponse::serverError($this->url, $throwable),
            };
        }
    }


    private function parameterMissingResponse(Throwable|Exception $throwable): Response
    {
        return ErrorResponse::badRequest($this->url, $throwable);
    }


    private function invalidPasswordResponse(Throwable|Exception $throwable): Response
    {
        return ErrorResponse::unprocessable($this->url, $throwable);
    }


    private function userAlreadyExistsResponse(): Response
    {
        return ConflictResponse::create($this->url);
    }
}
