<?php

declare(strict_types=1);

namespace norsk\api\user\application;

use norsk\api\user\application\useCases\RegisterUser;
use norsk\api\user\domain\model\RegisteredUser;
use norsk\api\user\domain\port\UserWritingRepository;
use norsk\api\user\domain\valueObjects\PasswordVector;

class UserRegistration
{
    public function __construct(
        private readonly UserWritingRepository $userRepository,
        private readonly PasswordVector $passwordVector,
    ) {
    }


    public function handle(RegisterUser $command): RegisteredUser
    {
        $user = RegisteredUser::create(
            $command->getUserName(),
            $command->getFirstName(),
            $command->getLastName(),
            $command->getInputPassword(),
            $this->passwordVector
        );
        $this->userRepository->add($user);

        return $user;
    }
}
