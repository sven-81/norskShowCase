<?php

declare(strict_types=1);

namespace norsk\api\user\infrastructure\web\controller;

use GuzzleHttp\Psr7\ServerRequest as Request;
use norsk\api\infrastructure\logging\Logger;
use norsk\api\infrastructure\logging\LogMessage;
use norsk\api\shared\infrastructure\http\request\Payload;
use norsk\api\shared\infrastructure\http\response\exceptionMapper\LoginExceptionMapper;
use norsk\api\shared\infrastructure\http\response\responses\SuccessResponse;
use norsk\api\shared\infrastructure\http\response\Url;
use norsk\api\user\application\useCases\LoginUser;
use norsk\api\user\application\UserLogin;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class Login
{
    public function __construct(
        private readonly Logger $logger,
        private readonly UserLogin $userLogin,
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

            $command = LoginUser::by($payload);
            $loggedInUser = $this->userLogin->handle($command);

            $this->logger->info(
                LogMessage::fromString('User verified successfully: ' . $loggedInUser->getUserName()->asString())
            );

            $body = $loggedInUser->asBodyJson();

            return SuccessResponse::loggedIn($this->url, $body);
        } catch (Throwable $throwable) {
            $this->logger->error($throwable);

            return LoginExceptionMapper::map($throwable, $this->url);
        }
    }
}
