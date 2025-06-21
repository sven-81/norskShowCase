<?php

declare(strict_types=1);

namespace norsk\api\manager\verbs\queries;

use norsk\api\app\persistence\SqlStatement;

class EditVerbsSql implements SqlStatement
{
    private readonly string $sql;


    private function __construct()
    {
        $this->sql = 'UPDATE `verbs` SET `german`=?, `norsk`=? , '
                     . '`norsk_present`=?, `norsk_past`=?, `norsk_past_perfekt`=? '
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
