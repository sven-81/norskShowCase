<?php

declare(strict_types=1);

namespace norsk\api\user\infrastructure;

use norsk\api\infrastructure\config\AppConfig;
use norsk\api\infrastructure\logging\Logger;
use norsk\api\infrastructure\persistence\DbConnection;
use norsk\api\user\application\UserLogin;
use norsk\api\user\application\UserRegistration;
use norsk\api\user\domain\service\JwtService;
use norsk\api\user\domain\valueObjects\PasswordVector;
use norsk\api\user\domain\valueObjects\Salt;
use norsk\api\user\infrastructure\persistence\UsersReader;
use norsk\api\user\infrastructure\persistence\UsersWriter;
use norsk\api\user\infrastructure\web\controller\Login;
use norsk\api\user\infrastructure\web\controller\Registration;

class UserManagementFactory
{
    public function __construct(
        private readonly Logger $logger,
        private readonly DbConnection $dbConnection,
        private readonly JwtService $jwtManagement,
        private readonly AppConfig $appConfig
    ) {
    }


    public function login(): Login
    {
        return new Login(
            $this->logger,
            $this->createUserLogin(),
            $this->appConfig->getUrl()
        );
    }


    public function registration(): Registration
    {
        return new Registration(
            $this->logger,
            $this->createUserRegistration(),
            $this->appConfig->getUrl()
        );
    }


    private function createUserLogin(): UserLogin
    {
        return new UserLogin(new UsersReader($this->dbConnection), $this->appConfig->getPepper(), $this->jwtManagement);
    }


    private function createUserRegistration(): UserRegistration
    {
        $vector = PasswordVector::by(Salt::init(), $this->appConfig->getPepper());
        $writer = new UsersWriter($this->dbConnection);

        return new UserRegistration($writer, $vector);
    }
}
