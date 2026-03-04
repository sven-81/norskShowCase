<?php

declare(strict_types=1);

namespace norsk\api\user\domain\exceptions;

use InvalidArgumentException;

class NoActiveUserException extends InvalidArgumentException
{
}
