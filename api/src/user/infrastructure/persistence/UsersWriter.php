<?php

declare(strict_types=1);

namespace norsk\api\user\infrastructure\persistence;

use norsk\api\infrastructure\persistence\DbConnection;
use norsk\api\infrastructure\persistence\Parameters;
use norsk\api\user\domain\model\RegisteredUser;
use norsk\api\user\domain\port\UserWritingRepository;
use norsk\api\user\infrastructure\persistence\queries\AddUserSql;

class UsersWriter implements UserWritingRepository
{
    private readonly AddUserSql $addingUserSql;


    public function __construct(private readonly DbConnection $dbConnector)
    {
        $this->addingUserSql = AddUserSql::create();
    }


    public function add(RegisteredUser $user): void
    {
        $params = Parameters::init();
        $params->addString($user->getUserName()->asString());
        $params->addString($user->getFirstName()->asString());
        $params->addString($user->getLastName()->asString());
        $params->addString($user->getPasswordHash()->asHashString());
        $params->addString($user->getSalt()->asString());

        $this->dbConnector->execute(
            $this->addingUserSql,
            $params
        );
    }
}
