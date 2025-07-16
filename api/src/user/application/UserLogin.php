<?php

declare(strict_types=1);

namespace norsk\api\user\application;

use norsk\api\user\application\useCases\LoginUser;
use norsk\api\user\domain\model\LoggedInUser;
use norsk\api\user\domain\port\UserReadingRepository;
use norsk\api\user\domain\service\JwtService;
use norsk\api\user\domain\valueObjects\Pepper;

class UserLogin
{
    public function __construct(
        private readonly UserReadingRepository $userRepository,
        private readonly Pepper $pepper,
        private readonly JwtService $jwtManagement
    ) {
    }


    public function handle(LoginUser $command): LoggedInUser
    {
        $validatedUser = $this->userRepository->getDataFor(
            $command->getUserName(),
            $command->getPassword(),
            $this->pepper
        );
        $jwToken = $this->jwtManagement->create($validatedUser);

        return LoggedInUser::by($validatedUser, $jwToken);
    }
}
