SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE wordsSuccessCounterToUsers;
TRUNCATE verbsSuccessCounterToUsers;
TRUNCATE users;
TRUNCATE words;
TRUNCATE verbs;
INSERT INTO `users` (`username`, `firstname`, `lastname`, `password_hash`, `salt`, `role`, `active`)
VALUES ('heinz', 'Heinz', 'Klaus', '$2y$10$b6aYdOjCDKVpsM92eIsJk.d6fQeRVFs/qciy8wIKkAaf6c3DANeDK',
        '3c29232db4c9ba0b8014b39ba0120f7cedae73a81aa14059e6a4c26b08ddb144', 'manager', 1);
INSERT INTO `users` (`username`, `firstname`, `lastname`, `password_hash`, `salt`, `role`, `active`)
VALUES ('karl', 'Heinz', 'Karl', '$2y$10$VlMTxYn6lnARKkQHq1oSMefy.ELKdsI8wg9XbS9aP115tlSaL7ALm',
        'c651f9240300f3c3bbcc9482105b04c43a5b1b539e3135a565b8a4feab59b6c9', 'user', 0);
INSERT INTO `users` (`username`, `firstname`, `lastname`, `password_hash`, `salt`, `role`, `active`)
VALUES ('test', 'test', 'test', '$2y$10$mahliTm/oOKhLh7syW02sOwxUw6u.bIaI.X8kihVOxs2KHP9sRCna',
        '236f676656a0c1a4b9934da2abdd2c650ca2fd3d9bf2c6654e82e3489c05fa06', 'manager', 1);
INSERT INTO `words` (`id`, `german`, `norsk`, `active`, `datetime`)
VALUES (1, 'Berg', 'fjell', 1, '2024-07-16 16:21:47');
INSERT INTO `words` (`id`, `german`, `norsk`, `active`, `datetime`)
VALUES (2, 'Herz', 'hjerte', 1, '2024-07-16 16:21:47');
INSERT INTO `words` (`id`, `german`, `norsk`, `active`, `datetime`)
VALUES (3, 'Schärenküste', 'skjærgård', 1, '2024-07-16 16:21:47');
INSERT INTO `words` (`id`, `german`, `norsk`, `active`, `datetime`)
VALUES (4, 'Silber', 'sølv', 1, '2024-07-16 16:21:47');
INSERT INTO `words` (`id`, `german`, `norsk`, `active`, `datetime`)
VALUES (5, 'Acker', 'åker', 1, '2024-07-16 16:21:47');
INSERT INTO `words` (`id`, `german`, `norsk`, `active`, `datetime`)
VALUES (6, 'Wellen', 'bølger', 1, '2024-07-16 16:21:47');
INSERT INTO `words` (`id`, `german`, `norsk`, `active`, `datetime`)
VALUES (7, 'Liebe', 'kjærlighet', 1, '2024-07-16 16:21:47');
INSERT INTO `words` (`id`, `german`, `norsk`, `active`, `datetime`)
VALUES (8, 'Stall', 'fjøs', 1, '2024-07-16 16:21:47');
INSERT INTO `words` (`id`, `german`, `norsk`, `active`, `datetime`)
VALUES (9, 'Nacht', 'natt', 1, '2024-07-16 16:21:47');
INSERT INTO `words` (`id`, `german`, `norsk`, `active`, `datetime`)
VALUES (10, 'Grün', 'grønn', 0, '2024-07-16 16:21:47');
INSERT INTO `wordsSuccessCounterToUsers` (`username`, `wordId`, `successCounter`, `timestamp`)
VALUES ('test', 1, 1, '2024-09-01 14:29:14');
INSERT INTO `wordsSuccessCounterToUsers` (`username`, `wordId`, `successCounter`, `timestamp`)
VALUES ('karl', 1, 3, '2024-09-01 14:29:24');
INSERT INTO `wordsSuccessCounterToUsers` (`username`, `wordId`, `successCounter`, `timestamp`)
VALUES ('test', 3, 10, '2024-09-01 14:29:50');
INSERT INTO `wordsSuccessCounterToUsers` (`username`, `wordId`, `successCounter`, `timestamp`)
VALUES ('test', 4, 3, '2024-09-01 14:29:58');
INSERT INTO `wordsSuccessCounterToUsers` (`username`, `wordId`, `successCounter`, `timestamp`)
VALUES ('test', 5, 4, '2024-09-01 14:29:58');
INSERT INTO `wordsSuccessCounterToUsers` (`username`, `wordId`, `successCounter`, `timestamp`)
VALUES ('test', 7, 2, '2024-09-01 14:29:37');
INSERT INTO `wordsSuccessCounterToUsers` (`username`, `wordId`, `successCounter`, `timestamp`)
VALUES ('test', 8, 2, '2024-09-01 14:29:37');
INSERT INTO `wordsSuccessCounterToUsers` (`username`, `wordId`, `successCounter`, `timestamp`)
VALUES ('test', 9, 5, '2024-09-01 14:29:37');
INSERT INTO `wordsSuccessCounterToUsers` (`username`, `wordId`, `successCounter`, `timestamp`)
VALUES ('test', 10, 5, '2024-09-01 14:29:37');
INSERT INTO `verbs` (`id`, `german`, `norsk`, `norsk_present`, `norsk_past`, `norsk_past_perfekt`, `active`, `datetime`)
VALUES (1, 'finden', 'finne', 'finner', 'fant', 'har funnet', 1, '2024-10-31 16:37:12');
INSERT INTO `verbs` (`id`, `german`, `norsk`, `norsk_present`, `norsk_past`, `norsk_past_perfekt`, `active`, `datetime`)
VALUES (2, 'gehen', 'gå', 'går', 'gikk', 'har gått', 1, '2024-10-31 16:37:12');
INSERT INTO `verbs` (`id`, `german`, `norsk`, `norsk_present`, `norsk_past`, `norsk_past_perfekt`, `active`, `datetime`)
VALUES (3, 'essen', 'spise', 'spiser', 'spiste', 'har spist', 1, '2024-10-31 16:37:12');
INSERT INTO `verbs` (`id`, `german`, `norsk`, `norsk_present`, `norsk_past`, `norsk_past_perfekt`, `active`, `datetime`)
VALUES (4, 'trinken', 'drikke', 'drikker', 'drakk', 'har drukket', 1, '2024-10-31 16:37:12');
INSERT INTO `verbs` (`id`, `german`, `norsk`, `norsk_present`, `norsk_past`, `norsk_past_perfekt`, `active`, `datetime`)
VALUES (5, 'sehen', 'se', 'ser', 'så', 'har sett', 0, '2024-10-31 16:37:12');
INSERT INTO `verbs` (`id`, `german`, `norsk`, `norsk_present`, `norsk_past`, `norsk_past_perfekt`, `active`, `datetime`)
VALUES (6, 'kommen', 'komme', 'kommer', 'kom', 'har kommet', 1, '2024-10-31 16:37:12');
INSERT INTO `verbs` (`id`, `german`, `norsk`, `norsk_present`, `norsk_past`, `norsk_past_perfekt`, `active`, `datetime`)
VALUES (7, 'machen', 'gjøre', 'gjør', 'gjorde', 'har gjort', 1, '2024-10-31 16:37:12');
INSERT INTO `verbs` (`id`, `german`, `norsk`, `norsk_present`, `norsk_past`, `norsk_past_perfekt`, `active`, `datetime`)
VALUES (8, 'sprechen', 'snakke', 'snakker', 'snakket', 'har snakket', 1, '2024-10-31 16:37:12');
INSERT INTO `verbs` (`id`, `german`, `norsk`, `norsk_present`, `norsk_past`, `norsk_past_perfekt`, `active`, `datetime`)
VALUES (9, 'lesen', 'lese', 'leser', 'leste', 'har lest', 0, '2024-10-31 16:37:12');
INSERT INTO `verbs` (`id`, `german`, `norsk`, `norsk_present`, `norsk_past`, `norsk_past_perfekt`, `active`, `datetime`)
VALUES (10, 'schreiben', 'skrive', 'skriver', 'skrev', 'har skrevet', 1, '2024-10-31 16:37:12');
INSERT INTO `verbsSuccessCounterToUsers` (`username`, `verbId`, `successCounter`, `timestamp`)
VALUES ('test', 3, 4, '2024-10-31 16:39:43');
INSERT INTO `verbsSuccessCounterToUsers` (`username`, `verbId`, `successCounter`, `timestamp`)
VALUES ('test', 2, 1, '2024-10-31 16:39:52');
INSERT INTO `verbsSuccessCounterToUsers` (`username`, `verbId`, `successCounter`, `timestamp`)
VALUES ('test', 6, 4, '2024-10-31 16:39:52');
INSERT INTO `verbsSuccessCounterToUsers` (`username`, `verbId`, `successCounter`, `timestamp`)
VALUES ('test', 7, 7, '2024-10-31 16:39:52');
INSERT INTO `verbsSuccessCounterToUsers` (`username`, `verbId`, `successCounter`, `timestamp`)
VALUES ('test', 10, 10, '2024-10-31 16:39:52');
SET FOREIGN_KEY_CHECKS = 1;