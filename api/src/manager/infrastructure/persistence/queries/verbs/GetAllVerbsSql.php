<?php

declare(strict_types=1);

namespace norsk\api\manager\infrastructure\persistence\queries\verbs;

use norsk\api\infrastructure\persistence\SqlStatement;

readonly class GetAllVerbsSql implements SqlStatement
{
    private string $sql;


    private function __construct()
    {
        $this->sql = 'SELECT `id`, `german`, `norsk`, `norsk_present`, `norsk_past`, `norsk_past_perfekt` FROM `verbs` '
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
