<?php

declare(strict_types=1);

namespace norsk\api\manager\words\queries;

use norsk\api\app\persistence\SqlStatement;

class EditWordsSql implements SqlStatement
{
    private readonly string $sql;


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
