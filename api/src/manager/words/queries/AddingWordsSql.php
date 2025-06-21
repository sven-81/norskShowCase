<?php

declare(strict_types=1);

namespace norsk\api\manager\words\queries;

use norsk\api\app\persistence\SqlStatement;

class AddingWordsSql implements SqlStatement
{
    private readonly string $sql;


    private function __construct()
    {
        $this->sql = 'INSERT INTO `words` (`german`, `norsk`) VALUES (?, ?);';
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
