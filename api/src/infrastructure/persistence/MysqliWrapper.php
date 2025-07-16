<?php

declare(strict_types=1);

namespace norsk\api\infrastructure\persistence;

use mysqli;

/**
 * @codeCoverageIgnore
 */
class MysqliWrapper extends mysqli
{
    public function __construct()
    {
        parent::__construct();
    }


    public function affectedRows(): int|string
    {
        return $this->affected_rows;
    }
}
