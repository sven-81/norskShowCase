<?php

declare(strict_types=1);

namespace norsk\api\tests\stubs;

use mysqli;
use mysqli_result;
use norsk\api\app\config\DbConfig;
use norsk\api\app\persistence\GenericSqlStatement;
use norsk\api\app\persistence\Parameters;
use norsk\api\app\persistence\SqlResult;
use norsk\api\app\persistence\TableName;
use RuntimeException;

class VirtualTestDatabase
{
    private const int SECONDS_0_1 = 100000;


    private function __construct(
        private readonly mysqli $mysqli,
        private readonly string $host,
        private readonly string $user,
        private readonly string $password,
        private readonly string $database
    ) {
    }


    public function insertInitialEntryToAvoidFailing(string $sqlStatements): void
    {
        $insertInitialEntries = $this->mysqli->multi_query($sqlStatements);
        if (!$insertInitialEntries) {
            echo 'Initial insert failed: ' . $this->mysqli->error;
        }
        $this->mysqli->close();
        $this->reconnect();
    }


    private function reconnect(): void
    {
        $this->mysqli->connect($this->host, $this->user, $this->password, $this->database);
    }


    public function truncate(TableName $tableName): void
    {
        $this->mysqli->query("SET FOREIGN_KEY_CHECKS=0;");
        $this->waitForDatabase();
        $this->mysqli->query("TRUNCATE `$tableName->value`");
        $this->waitForDatabase();
        $this->mysqli->query("SET FOREIGN_KEY_CHECKS=1;");
        $this->waitForDatabase();
    }


    public function waitForDatabase(): void
    {
        usleep(self::SECONDS_0_1);
    }


    public function deleteAll(
        TableName $tableName
    ): void {
        $this->executePreparedStatement(
            GenericSqlStatement::create(
                'DELETE FROM ' . $tableName->value . ';'
            ),
            Parameters::init()
        );
    }


    private function executePreparedStatement(
        GenericSqlStatement $sql,
        Parameters $params
    ): bool|mysqli_result {
        $success = $this->mysqli->execute_query($sql->asString(), $params->asArray());

        if (!$success) {
            $this->throwExceptionCouldNotExecuteQuery($sql);
        }

        return $success;
    }


    private function throwExceptionCouldNotExecuteQuery(GenericSqlStatement $sql): void
    {
        throw new RuntimeException('Could not execute query: ' . $sql->asString());
    }


    public static function create(DbConfig $dbConfig): self
    {
        $host = $dbConfig->host()->asString();
        $user = $dbConfig->user()->asString();
        $password = $dbConfig->password()->asString();
        $database = $dbConfig->database()->asString();
        $mysqli = new mysqli($host, $user, $password, $database);

        return new self($mysqli, $host, $user, $password, $database);
    }


    public function select(
        GenericSqlStatement $sql,
        Parameters $params
    ): SqlResult {
        $statement = $this->executePreparedStatement($sql, $params);
        $results = $statement->fetch_all(MYSQLI_ASSOC);

        return SqlResult::resultFromArray($results);
    }


    public function update(GenericSqlStatement $sql, Parameters $params): void
    {
        $this->executePreparedStatement($sql, $params);
    }


    public function alter(GenericSqlStatement $sql): void
    {
        $this->executePreparedStatement($sql, Parameters::init());
    }


    public function recreate(TableName $table): void
    {
        if ($table === TableName::wordsSuccessCounterToUsers) {
            $sql = file_get_contents(
                __DIR__ . '/../system/resources/queries/recreateWordsSuccessCounterToUsers.sql'
            );
            $this->executePreparedStatement(GenericSqlStatement::create($sql), Parameters::init());
        }

        if ($table === TableName::verbsSuccessCounterToUsers) {
            $sql = file_get_contents(
                __DIR__ . '/../system/resources/queries/recreateVerbsSuccessCounterToUsers.sql'
            );
            $this->executePreparedStatement(GenericSqlStatement::create($sql), Parameters::init());
        }

        if ($table === TableName::users) {
            $sql = file_get_contents(
                __DIR__ . '/../system/resources/queries/recreateUsers.sql'
            );
            $this->executePreparedStatement(GenericSqlStatement::create($sql), Parameters::init());
        }
    }
}
