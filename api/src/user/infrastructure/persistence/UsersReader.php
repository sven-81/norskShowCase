<?php

declare(strict_types=1);

namespace norsk\api\user\infrastructure\persistence;

use norsk\api\infrastructure\persistence\DbConnection;
use norsk\api\infrastructure\persistence\Parameters;
use norsk\api\infrastructure\persistence\SqlResult;
use norsk\api\user\domain\exceptions\CredentialsAreInvalidException;
use norsk\api\user\domain\exceptions\NoActiveManagerException;
use norsk\api\user\domain\model\ValidatedUser;
use norsk\api\user\domain\port\UserReadingRepository;
use norsk\api\user\domain\valueObjects\InputPassword;
use norsk\api\user\domain\valueObjects\Pepper;
use norsk\api\user\domain\valueObjects\UserName;
use norsk\api\user\infrastructure\persistence\queries\ActiveManagerSql;
use norsk\api\user\infrastructure\persistence\queries\FindUserDataSql;
use norsk\api\user\infrastructure\persistence\queries\FindUserSql;

class UsersReader implements UserReadingRepository
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
            throw new NoActiveManagerException('Unauthorized: Current user is no active manager');
        }
    }
}
