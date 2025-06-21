<?php

declare(strict_types=1);

namespace norsk\api\user;

use norsk\api\app\persistence\DbConnection;
use norsk\api\app\persistence\Parameters;
use norsk\api\user\queries\AddUserSql;

class UsersWriter
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
