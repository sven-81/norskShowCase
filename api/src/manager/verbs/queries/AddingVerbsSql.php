<?php

declare(strict_types=1);

namespace norsk\api\manager\verbs\queries;

use norsk\api\app\persistence\SqlStatement;

class AddingVerbsSql implements SqlStatement
{
    private readonly string $sql;


    private function __construct()
    {
        $this->sql = 'INSERT INTO `verbs` (`german`, `norsk`, `norsk_present`, `norsk_past`, `norsk_past_perfekt`) '
                     . 'VALUES (?, ?, ?, ?, ?);';
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
