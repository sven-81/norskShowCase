<?php

declare(strict_types=1);

namespace norsk\api\infrastructure\persistence;

enum TableName: string
{
    case users = 'users';
    case words = 'words';
    case verbs = 'verbs';
    case verbsSuccessCounterToUsers = 'verbsSuccessCounterToUsers';
    case wordsSuccessCounterToUsers = 'wordsSuccessCounterToUsers';
}
