<?php

declare(strict_types=1);

namespace norsk\api\app\config;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;

#[CoversClass(DbConfig::class)]
class DbConfigTest extends TestCase
{
    private const string DB_CONFIG_PATH = __DIR__ . '/../resources/mySqlConfig.created.ini';

    private DbConfig $config;


    public static function getConfigKeys(): array
    {
        return [
            'host' => ['[database]', 'db_server'],
            'database' => ["[database]\rdb_server = foo", 'db_name'],
            'user' => ["[database]\rdb_server = foo\rdb_name = bar", 'db_user'],
            'password' => ["[database]\rdb_server = foo\rdb_name = bar\rdb_user = foobar", 'db_password'],
            'port' => ["[database]\rdb_server = foo\rdb_name = bar\rdb_user = foobar\rdb_password = foobar", 'db_port'],
        ];
    }


    public static function getConfigValues(): array
    {
        return [
            'host' => [
                "[database]\rdb_server = \rdb_name = foo\rdb_user = foo\rdb_password = foo\rdb_port = foo",
                'db_server',
            ],
            'database' => [
                "[database]\rdb_server = foo\rdb_name = \rdb_user = foo\rdb_password = foo\rdb_port = foo",
                'db_name',
            ],
            'user' => [
                "[database]\rdb_server = foo\rdb_name = foo\rdb_user = \rdb_password = foo\rdb_port = foo",
                'db_user',
            ],
            'password' => [
                "[database]\rdb_server = foo\rdb_name = foo\rdb_user = foo\rdb_password = \rdb_port = foo",
                'db_password',
            ],
            'port' => [
                "[database]\rdb_server = foo\rdb_name = foo\rdb_user = foo\rdb_password = foo\rdb_port = ",
                'db_port',
            ],
        ];
    }


    public function testCannotReadConfig(): void
    {
        $this->expectExceptionObject(new RuntimeException('Cannot read file:'));

        DbConfig::fromPath(Path::fromString('/foo'));
    }


    public function testCanReadDbServer(): void
    {
        self::assertSame('localhost', $this->config->host()->asString());
    }


    public function testCanReadDatabaseName(): void
    {
        self::assertSame('someDatabase', $this->config->database()->asString());
    }


    public function testCanReadDbPassword(): void
    {
        self::assertSame('123foo', $this->config->password()->asString());
    }


    public function testCanReadPort(): void
    {
        self::assertSame(3306, $this->config->port()->asInt());
    }


    public function testCanReadDbUser(): void
    {
        self::assertSame('mats', $this->config->user()->asString());
    }


    #[DataProvider('getConfigKeys')]
    public function testThrowsExceptionIfKeyDoesNotExist(string $content, string $key): void
    {
        $this->createTempConfig($content);

        $this->expectExceptionObject(new RuntimeException($key . ' is not set.', 422));

        DbConfig::fromPath(Path::fromString(self::DB_CONFIG_PATH));
    }


    private function createTempConfig(string $content): void
    {
        file_put_contents(self::DB_CONFIG_PATH, $content);
    }


    #[DataProvider('getConfigValues')]
    public function testThrowsExceptionIfValueDoesNotExist(string $content, string $key): void
    {
        $this->createTempConfig($content);

        $this->expectExceptionObject(new RuntimeException($key . ' is empty.', 422));

        DbConfig::fromPath(Path::fromString(self::DB_CONFIG_PATH));
    }


    protected function setUp(): void
    {
        $this->config = DbConfig::fromPath(
            Path::fromString(__DIR__ . '/../resources/mySqlConfig.test.ini')
        );
    }


    protected function tearDown(): void
    {
        if (file_exists(self::DB_CONFIG_PATH)) {
            unlink(self::DB_CONFIG_PATH);
        }
    }
}
