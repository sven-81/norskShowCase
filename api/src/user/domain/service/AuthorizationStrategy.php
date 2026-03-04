<?php

declare(strict_types=1);

namespace norsk\api\user\domain\service;

use norsk\api\user\application\AuthenticatedUserInterface;
use norsk\api\user\domain\model\AuthorizationResult;

interface AuthorizationStrategy
{
    public function authorize(AuthenticatedUserInterface $authenticatedUser): AuthorizationResult;


    public function checkActive(AuthenticatedUserInterface $authenticatedUser): void;
}
