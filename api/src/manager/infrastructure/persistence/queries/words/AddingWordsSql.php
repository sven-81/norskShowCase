<?php

declare(strict_types=1);

namespace norsk\api\manager\infrastructure\persistence\queries\words;

use norsk\api\infrastructure\persistence\SqlStatement;

readonly class AddingWordsSql implements SqlStatement
{
    private string $sql;


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
