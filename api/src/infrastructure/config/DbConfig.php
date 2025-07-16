<?php

declare(strict_types=1);

namespace norsk\api\infrastructure\config;

use norsk\api\infrastructure\config\exceptions\KeyHasEmptyValueException;
use norsk\api\infrastructure\config\exceptions\KeyIsNotSetException;
use norsk\api\infrastructure\persistence\DatabaseName;
use norsk\api\infrastructure\persistence\Host;
use norsk\api\infrastructure\persistence\Password;
use norsk\api\infrastructure\persistence\Port;
use norsk\api\infrastructure\persistence\User;
use norsk\api\shared\infrastructure\http\response\ResponseCode;

class DbConfig
{
    private const string DATABASE = 'database';
    private const string DB_SERVER = 'db_server';
    private const string DB_NAME = 'db_name';
    private const string DB_USER = 'db_user';
    private const string DB_PASSWORD = 'db_password';
    private const string DB_PORT = 'db_port';


    private function __construct(
        private readonly Host $host,
        private readonly DatabaseName $name,
        private readonly User $user,
        private readonly Password $password,
        private readonly Port $port
    ) {
    }


    public static function fromPath(Path $configPath): self
    {
        $file = File::fromPath($configPath);
        $configItems = $file->parseIniFile()->asArray();
        self::ensureConfigIsValid($configItems);

        return new self(
            Host::fromString($configItems[self::DATABASE][self::DB_SERVER]),
            DatabaseName::fromString($configItems[self::DATABASE][self::DB_NAME]),
            User::fromString($configItems[self::DATABASE][self::DB_USER]),
            Password::fromString($configItems[self::DATABASE][self::DB_PASSWORD]),
            Port::fromInt($configItems[self::DATABASE][self::DB_PORT])
        );
    }


    /**
     * @param array<array<string>> $configItems
     */
    private static function ensureConfigIsValid(array $configItems): void
    {
        self::ensureKeysExist($configItems);
        self::ensureKeysHaveValues($configItems);
    }


    private static function ensureKeysExist(array $configItems): void
    {
        $neededKeys = self::getKeys();

        foreach ($neededKeys as $neededKey) {
            if (!array_key_exists($neededKey, $configItems[self::DATABASE])) {
                throw new KeyIsNotSetException($neededKey . ' is not set.', ResponseCode::unprocessable->value);
            }
        }
    }


    /**
     * @return string[]
     */
    private static function getKeys(): array
    {
        return [
            self::DB_SERVER,
            self::DB_NAME,
            self::DB_USER,
            self::DB_PASSWORD,
            self::DB_PORT,
        ];
    }


    private static function ensureKeysHaveValues(array $configItems): void
    {
        $neededKeys = self::getKeys();

        foreach ($neededKeys as $neededKey) {
            if ($configItems[self::DATABASE][$neededKey] === '') {
                throw new KeyHasEmptyValueException($neededKey . ' is empty.', ResponseCode::unprocessable->value);
            }
        }
    }


    public function host(): Host
    {
        return $this->host;
    }


    public function database(): DatabaseName
    {
        return $this->name;
    }


    public function user(): User
    {
        return $this->user;
    }


    public function password(): Password
    {
        return $this->password;
    }


    public function port(): Port
    {
        return $this->port;
    }
}
