<?php

declare(strict_types=1);

namespace norsk\api\shared\domain;

use norsk\api\shared\application\Json;

interface Vocabulary
{
    public function getId(): Id;


    public function asJson(): Json;

}
