<?php

declare(strict_types=1);

namespace norsk\api\tests\provider;

use norsk\api\shared\infrastructure\http\request\Payload;
use norsk\api\user\domain\model\JwtAuthenticatedUser;
use stdClass;

class JwtUserProvider
{
    public static function getUser(string $name): JwtAuthenticatedUser
    {
        $class = new stdClass();
        $class->nickname = $name;
        $class->scope = 'is:user';

        $payload = Payload::by($class);

        return JwtAuthenticatedUser::byPayload($payload);
    }
}