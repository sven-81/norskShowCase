<?php

declare(strict_types=1);

namespace norsk\api\user\application;

use norsk\api\user\application\useCases\LoginUser;
use norsk\api\user\domain\model\LoggedInUser;
use norsk\api\user\domain\model\ValidatedUser;
use norsk\api\user\domain\port\UserReadingRepository;
use norsk\api\user\domain\service\JwtService;
use norsk\api\user\domain\valueObjects\Pepper;

readonly class UserLogin
{
    public function __construct(
        private UserReadingRepository $userRepository,
        private Pepper $pepper,
        private JwtService $jwtManagement
    ) {
    }


    public function handle(LoginUser $command): LoggedInUser
    {
        $userData = $this->userRepository->findByUserName($command->getUserName());

        $validatedUser = ValidatedUser::fromUserData($userData, $command->getPassword(), $this->pepper);

        $token = $this->jwtManagement->create($validatedUser);

        return LoggedInUser::by($validatedUser, $token);
    }
}
