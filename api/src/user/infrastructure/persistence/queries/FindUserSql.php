<?php

declare(strict_types=1);

namespace norsk\api\user\infrastructure\persistence\queries;

use norsk\api\infrastructure\persistence\SqlStatement;

class FindUserSql implements SqlStatement
{
    private readonly string $sql;


    private function __construct()
    {
        $this->sql = 'SELECT `username` '
                     . 'FROM `users` '
                     . 'WHERE `username` = ? AND `active` = 1;';
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
