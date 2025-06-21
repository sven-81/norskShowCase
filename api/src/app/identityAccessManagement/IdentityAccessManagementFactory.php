<?php

declare(strict_types=1);

namespace norsk\api\app\identityAccessManagement;

use DateTimeImmutable;
use norsk\api\app\config\AppConfig;
use norsk\api\app\logging\Logger;
use norsk\api\app\response\Url;
use norsk\api\user\UsersReader;

readonly class IdentityAccessManagementFactory
{
    public function __construct(
        private AppConfig $appConfig,
        private Logger $logger,
        private UsersReader $usersReader,
        private Url $url
    ) {
    }


    public function createAuthorization(): Authorization
    {
        return new Authorization($this->logger, $this->usersReader, $this->url);
    }


    public function createAuthentication(Session $session): Authentication
    {
        return new Authentication($this->createJwtManagement(), $session, $this->url);
    }


    public function createJwtManagement(): JwtManagement
    {
        return new JwtManagement(
            $this->appConfig,
            $this->createClock(),
            $this->logger,
            $this->usersReader
        );
    }


    private function createClock(): Clock
    {
        return new Clock(new DateTimeImmutable('now'));
    }
}
