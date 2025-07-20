<?php

declare(strict_types=1);

namespace norsk\api\user\infrastructure\persistence\queries;

use norsk\api\infrastructure\persistence\SqlStatement;

class FindUserDataSql implements SqlStatement
{
    private readonly string $sql;


    private function __construct()
    {
        $this->sql = 'SELECT `username`, `firstname`, `lastname`, `password_hash`, `salt`, `role`, `active` '
                     . 'FROM `users` '
                     . 'WHERE `username` = ?;';
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
