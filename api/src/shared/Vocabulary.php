<?php

declare(strict_types=1);

namespace norsk\api\shared;

interface Vocabulary
{
    public function getId(): Id;


    public function asJson(): Json;
}
