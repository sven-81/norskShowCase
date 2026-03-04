<?php

declare(strict_types=1);

namespace norsk\api\user\infrastructure\persistence;

use InvalidArgumentException;
use norsk\api\infrastructure\persistence\DbConnection;
use norsk\api\infrastructure\persistence\Parameters;
use norsk\api\infrastructure\persistence\SqlResult;
use norsk\api\user\domain\exceptions\CredentialsAreInvalidException;
use norsk\api\user\domain\exceptions\NoActiveManagerException;
use norsk\api\user\domain\model\Role;
use norsk\api\user\domain\model\UserData;
use norsk\api\user\domain\port\ManagerAuthorizationRepository;
use norsk\api\user\domain\port\UserReadingRepository;
use norsk\api\user\domain\valueObjects\FirstName;
use norsk\api\user\domain\valueObjects\LastName;
use norsk\api\user\domain\valueObjects\PasswordHash;
use norsk\api\user\domain\valueObjects\Salt;
use norsk\api\user\domain\valueObjects\UserName;
use norsk\api\user\infrastructure\persistence\queries\ActiveManagerSql;
use norsk\api\user\infrastructure\persistence\queries\FindUserDataSql;
use norsk\api\user\infrastructure\persistence\queries\FindUserSql;

class UsersReader implements UserReadingRepository, ManagerAuthorizationRepository
{
    private const string USERNAME = 'username';
    private const string FIRST_NAME = 'firstname';
    private const string LAST_NAME = 'lastname';
    private const string PASSWORD_HASH = 'password_hash';
    private const string SALT = 'salt';
    private const string ACTIVE = 'active';
    private const string ROLE = 'role';

    private readonly FindUserDataSql $findUserData;

    private readonly FindUserSql $findUser;

    private readonly ActiveManagerSql $activeManager;


    public function __construct(private readonly DbConnection $dbConnection)
    {
        $this->findUserData = FindUserDataSql::create();
        $this->findUser = FindUserSql::create();
        $this->activeManager = ActiveManagerSql::create();
    }


    public function findByUserName(UserName $userName): UserData
    {
        $params = Parameters::init();
        $params->addString($userName->asString());

        $result = $this->dbConnection->getResult($this->findUserData, $params);

        $this->ensureUserExists($result);

        return $this->mapToUserData($result->asArray()[0]);
    }


    private function mapToUserData(array $record): UserData
    {
        $this->ensureRequiredFieldsPresent($record);

        return UserData::of(
            userName: UserName::by($record[self::USERNAME]),
            firstName: FirstName::by($record[self::FIRST_NAME]),
            lastName: LastName::by($record[self::LAST_NAME]),
            passwordHash: PasswordHash::by($record[self::PASSWORD_HASH]),
            salt: Salt::by($record[self::SALT]),
            role: Role::from($record[self::ROLE]),
            isActive: (bool) $record[self::ACTIVE],
        );
    }


    private function ensureRequiredFieldsPresent(array $record): void
    {
        $required = [self::USERNAME, self::FIRST_NAME, self::LAST_NAME, self::PASSWORD_HASH, self::SALT, self::ROLE, self::ACTIVE];
        foreach ($required as $field) {
            if (!array_key_exists($field, $record)) {
                throw new InvalidArgumentException('Missing field in user record: ' . $field);
            }
        }
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

        $result = $this->dbConnection->getResult($this->findUser, $params);

        $this->ensureUserExists($result);
    }


    public function isActiveManager(UserName $userName): void
    {
        $params = Parameters::init();
        $params->addString($userName->asString());

        $result = $this->dbConnection->getResult($this->activeManager, $params);

        $this->ensureUserIsActiveManager($result);
    }


    private function ensureUserIsActiveManager(SqlResult $result): void
    {
        if ($result->count() === 0) {
            throw new NoActiveManagerException('Unauthorized: Current user is no active manager');
        }
    }
}
