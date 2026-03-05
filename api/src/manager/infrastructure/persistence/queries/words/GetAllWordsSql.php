<?php

declare(strict_types=1);

namespace norsk\api\manager\infrastructure\persistence\queries\words;

use norsk\api\infrastructure\persistence\SqlStatement;

readonly class GetAllWordsSql implements SqlStatement
{
    private string $sql;


    private function __construct()
    {
        $this->sql = 'SELECT `id`, `german`, `norsk` FROM `words` '
                     . 'WHERE `active`=1 '
                     . 'ORDER BY `id` ASC;';
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
