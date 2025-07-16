<?php

declare(strict_types=1);

namespace norsk\api\user\domain\service;

use GuzzleHttp\Psr7\Response;
use norsk\api\infrastructure\logging\LogMessage;
use norsk\api\user\application\AuthenticatedUserInterface;
use norsk\api\user\domain\valueObjects\UserName;
use norsk\api\user\infrastructure\identityAccessManagement\authorization\AuthorizationDecision;

interface AuthorizationStrategy
{
    public function authorize(AuthenticatedUserInterface $authenticatedUser): AuthorizationDecision;


    public function checkActive(AuthenticatedUserInterface $authenticatedUser): void;


    public function unauthorizedResponse(): Response;


    public function successLogging(AuthorizationDecision $authorizationDecision): LogMessage;


    public function infoLogMessageForError(?UserName $userName): LogMessage;
}
