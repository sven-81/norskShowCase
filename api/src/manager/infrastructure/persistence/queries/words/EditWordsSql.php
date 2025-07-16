<?php

declare(strict_types=1);

namespace norsk\api\manager\infrastructure\persistence\queries\words;

use norsk\api\infrastructure\persistence\SqlStatement;

readonly class EditWordsSql implements SqlStatement
{
    private string $sql;


    private function __construct()
    {
        $this->sql = 'UPDATE `words` SET `german`=?, `norsk`=? '
                     . 'WHERE `id`=? AND `active`=1;';
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
