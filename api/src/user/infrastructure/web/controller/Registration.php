<?php

declare(strict_types=1);

namespace norsk\api\user\infrastructure\web\controller;

use GuzzleHttp\Psr7\ServerRequest as Request;
use norsk\api\infrastructure\logging\Logger;
use norsk\api\infrastructure\logging\LogMessage;
use norsk\api\shared\infrastructure\http\request\Payload;
use norsk\api\shared\infrastructure\http\response\exceptionMapper\RegisterExceptionMapper;
use norsk\api\shared\infrastructure\http\response\responses\CreatedResponse;
use norsk\api\shared\infrastructure\http\response\Url;
use norsk\api\user\application\useCases\RegisterUser;
use norsk\api\user\application\UserRegistration;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class Registration
{
    public function __construct(
        private readonly Logger $logger,
        private readonly UserRegistration $userRegistration,
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

            $command = RegisterUser::by($payload);
            $registeredUser = $this->userRegistration->handle($command);

            $this->logger->info(
                LogMessage::fromString('Added new User: ' . $registeredUser->getUserName()->asString())
            );

            return CreatedResponse::savedNewUser($this->url);
        } catch (Throwable $throwable) {
            $this->logger->error($throwable);

            return RegisterExceptionMapper::map($throwable, $this->url);
        }
    }
}
