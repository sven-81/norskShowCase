ALTER TABLE `users`
    ADD COLUMN `password_hash` VARCHAR(256) NOT NULL AFTER `lastname`,
    ADD INDEX `password_hash` (`password_hash`);
