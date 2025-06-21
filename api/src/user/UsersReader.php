<?php

declare(strict_types=1);

namespace norsk\api\user;

use norsk\api\app\persistence\DbConnection;
use norsk\api\app\persistence\Parameters;
use norsk\api\app\persistence\SqlResult;
use norsk\api\user\exceptions\CredentialsAreInvalidException;
use norsk\api\user\exceptions\NoActiveManagerException;
use norsk\api\user\queries\ActiveManagerSql;
use norsk\api\user\queries\FindUserDataSql;
use norsk\api\user\queries\FindUserSql;

class UsersReader
{
    private readonly FindUserDataSql $findUserData;

    private readonly FindUserSql $findUser;

    private readonly ActiveManagerSql $activeManager;


    public function __construct(private readonly DbConnection $dbConnection)
    {
        $this->findUserData = FindUserDataSql::create();
        $this->findUser = FindUserSql::create();
        $this->activeManager = ActiveManagerSql::create();
    }


    public function getDataFor(
        UserName $userName,
        InputPassword $inputPassword,
        Pepper $pepper
    ): ValidatedUser {
        $params = Parameters::init();
        $params->addString($userName->asString());

        $result = $this->dbConnection->getResult(
            $this->findUserData,
            $params
        );

        $this->ensureUserExists($result);

        return ValidatedUser::createBySqlResultAndPasswordHash($result, $inputPassword, $pepper);
    }


    private function ensureUserExists(SqlResult $result): void
    {
        if ($result->count() === 0) {
            throw new CredentialsAreInvalidException();
        }
    }


    public function checkIfUserExists(UserName $userName): void
    {
        $params = Parameters::init();
        $params->addString($userName->asString());

        $result = $this->dbConnection->getResult(
            $this->findUser,
            $params
        );

        $this->ensureUserExists($result);
    }


    public function isActiveManager(UserName $userName): void
    {
        $params = Parameters::init();
        $params->addString($userName->asString());

        $result = $this->dbConnection->getResult(
            $this->activeManager,
            $params
        );

        $this->ensureUserIsActiveManager($result);
    }


    private function ensureUserIsActiveManager(SqlResult $result): void
    {
        if ($result->count() === 0) {
            throw new NoActiveManagerException('Current user is no active manager');
        }
    }
}
