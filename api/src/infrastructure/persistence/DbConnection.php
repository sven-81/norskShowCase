<?php

declare(strict_types=1);

namespace norsk\api\infrastructure\persistence;

use LogicException;
use mysqli_result;
use norsk\api\infrastructure\config\DbConfig;
use norsk\api\shared\infrastructure\http\response\ResponseCode;
use RuntimeException;
use Throwable;

class DbConnection
{
    private const string CHARSET = 'utf8';

    private bool $isConnected = false;


    public function __construct(
        private readonly MysqliWrapper $mysqli,
        private readonly DbConfig $dbConfig
    ) {
    }


    public function getResult(
        SqlStatement $sql,
        Parameters $params
    ): SqlResult {
        $results = $this->getResults($sql, $params);

        return SqlResult::resultFromArray($results);
    }


    private function getResults(
        SqlStatement $sql,
        Parameters $params
    ): array {
        $results = $this->executePreparedStatement($sql, $params);
        $this->ensureGetResultsWasImplementedCorrectly($results);

        return $results->fetch_all(MYSQLI_ASSOC);
    }


    private function executePreparedStatement(
        SqlStatement $sql,
        Parameters $params
    ): bool|mysqli_result {
        $this->createConnection();
        $success = $this->mysqli->execute_query($sql->asString(), $params->asArray());

        if (!$success) {
            $this->throwExceptionCouldNotExecuteQuery($sql);
        }

        return $success;
    }


    public function createConnection(): void
    {
        $this->enableExceptionInsteadOfFalse();

        try {
            if (!$this->isConnected) {
                $this->isConnected = $this->mysqli->connect(
                    $this->dbConfig->host()->asString(),
                    $this->dbConfig->user()->asString(),
                    $this->dbConfig->password()->asString(),
                    $this->dbConfig->database()->asString(),
                    $this->dbConfig->port()->asInt()
                );
            }

            $this->setCharset();
        } catch (Throwable $throwable) {
            throw new RuntimeException(
                'Could not connect to server: ' . $this->dbConfig->host()->asString()
                . '. Because: ' . $throwable->getMessage(),
                ResponseCode::serverError->value,
                $throwable
            );
        }
    }


    private function setCharset(): void
    {
        $this->mysqli->set_charset(self::CHARSET);
    }


    private function throwExceptionCouldNotExecuteQuery(SqlStatement $sql): void
    {
        throw new RuntimeException('Could not execute query: ' . $sql->asString());
    }


    private function ensureGetResultsWasImplementedCorrectly(mysqli_result|bool $results): void
    {
        if ($results === true) {
            throw new LogicException('getResults is supposed to be used for SELECT, SHOW, DESCRIBE or EXPLAIN');
        }
    }


    public function execute(
        SqlStatement $sql,
        Parameters $params
    ): AffectedRows {
        $this->executePreparedStatement($sql, $params);
        $rows = $this->mysqli->affectedRows();

        return AffectedRows::fromInt($rows);
    }


    private function enableExceptionInsteadOfFalse(): void
    {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    }
}
