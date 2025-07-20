<?php

declare(strict_types=1);

namespace norsk\api\user\infrastructure\identityAccessManagement;

use norsk\api\infrastructure\config\AppConfig;
use norsk\api\infrastructure\logging\Logger;
use norsk\api\shared\infrastructure\http\response\Url;
use norsk\api\user\infrastructure\identityAccessManagement\authentication\Authentication;
use norsk\api\user\infrastructure\identityAccessManagement\authorization\Authorization;
use norsk\api\user\infrastructure\identityAccessManagement\authorization\ManagerAuthorizationStrategy;
use norsk\api\user\infrastructure\identityAccessManagement\authorization\TrainerAuthorizationStrategy;
use norsk\api\user\infrastructure\identityAccessManagement\jwt\JwtManagement;
use norsk\api\user\infrastructure\persistence\UsersReader;

readonly class IdentityAccessManagementFactory
{
    public function __construct(
        private AppConfig $appConfig,
        private Logger $logger,
        private UsersReader $usersReader,
        private Url $url
    ) {
    }


    public function createTrainerAuthorization(): Authorization
    {
        return new Authorization(
            $this->logger,
            new TrainerAuthorizationStrategy($this->usersReader, $this->url),
            $this->url
        );
    }


    public function createManagerAuthorization(): Authorization
    {
        return new Authorization(
            $this->logger,
            new ManagerAuthorizationStrategy($this->usersReader, $this->url),
            $this->url
        );
    }


    public function createAuthentication(): Authentication
    {
        return new Authentication($this->createJwtManagement(), $this->url);
    }


    public function createJwtManagement(): JwtManagement
    {
        return new JwtManagement(
            $this->appConfig,
            $this->createClock(),
            $this->logger,
        );
    }


    private function createClock(): EnhancedClock
    {
        return new EnhancedClock(new Clock());
    }
}
