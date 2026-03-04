<?php

declare(strict_types=1);

namespace norsk\api\user\infrastructure\identityAccessManagement\authorization;

use GuzzleHttp\Psr7\Response;
use norsk\api\infrastructure\logging\LogMessage;
use norsk\api\user\domain\model\AuthorizationResult;
use norsk\api\user\domain\service\AuthorizationStrategy;
use norsk\api\user\domain\valueObjects\UserName;

interface AuthorizationMiddlewareStrategy extends AuthorizationStrategy
{
    public function unauthorizedResponse(): Response;


    public function successLogging(AuthorizationResult $result): LogMessage;


    public function infoLogMessageForError(?UserName $userName): LogMessage;
}

