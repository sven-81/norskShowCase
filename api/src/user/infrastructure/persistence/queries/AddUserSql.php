<?php

declare(strict_types=1);

namespace norsk\api\user\infrastructure\persistence\queries;

use norsk\api\infrastructure\persistence\SqlStatement;

readonly class AddUserSql implements SqlStatement
{
    private string $sql;


    private function __construct()
    {
        $this->sql = 'INSERT INTO `users` '
                     . '(`username`, `firstname`, `lastname`, `password_hash`, `salt`, `active`) '
                     . 'VALUES (?, ?, ?, ?, ?, 0);';
    }


    public static function create(): self
    {
        return new self();
    }


    public function asString(): string
    {
        return $this->sql;
    }
}
