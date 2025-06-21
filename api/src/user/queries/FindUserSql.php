<?php

declare(strict_types=1);

namespace norsk\api\user\queries;

use norsk\api\app\persistence\SqlStatement;

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
