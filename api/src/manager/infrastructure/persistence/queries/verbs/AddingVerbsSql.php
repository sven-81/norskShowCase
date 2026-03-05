<?php

declare(strict_types=1);

namespace norsk\api\manager\infrastructure\persistence\queries\verbs;

use norsk\api\infrastructure\persistence\SqlStatement;

readonly class AddingVerbsSql implements SqlStatement
{
    private string $sql;


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
