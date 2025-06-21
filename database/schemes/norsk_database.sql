-- --------------------------------------------------------
-- Host:                         localhost
-- Server-Version:               10.11.9-MariaDB-ubu2204 - mariadb.org binary distribution
-- Server-Betriebssystem:        debian-linux-gnu
-- HeidiSQL Version:             12.7.0.6850
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT = @@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE = @@TIME_ZONE */;
/*!40103 SET TIME_ZONE = '+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS = @@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS = 0 */;
/*!40101 SET @OLD_SQL_MODE = @@SQL_MODE, SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES = @@SQL_NOTES, SQL_NOTES = 0 */;


-- Exportiere Datenbank-Struktur für myDatabase
CREATE DATABASE IF NOT EXISTS `myDatabase` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci */;
USE `myDatabase`;

-- Exportiere Struktur von Tabelle myDatabase.users
CREATE TABLE IF NOT EXISTS `users`
(
    `username`      varchar(30)             NOT NULL,
    `firstname`     varchar(30)             NOT NULL,
    `lastname`      varchar(30)             NOT NULL,
    `password_hash` varchar(256)            NOT NULL,
    `salt`          varchar(256)            NOT NULL,
    `role`          enum ('user','manager') NOT NULL DEFAULT 'user',
    `active`        tinyint(4)              NOT NULL DEFAULT 0,
    PRIMARY KEY (`username`),
    KEY `password_hash` (`password_hash`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_general_ci;

-- Daten-Export vom Benutzer nicht ausgewählt

-- Exportiere Struktur von Tabelle myDatabase.verbs
CREATE TABLE IF NOT EXISTS `verbs`
(
    `id`                 int(9)       NOT NULL AUTO_INCREMENT,
    `german`             varchar(255) NOT NULL,
    `norsk`              varchar(255) NOT NULL,
    `norsk_present`      varchar(255) NOT NULL,
    `norsk_past`         varchar(255) NOT NULL,
    `norsk_past_perfekt` varchar(255) NOT NULL,
    `active`             tinyint(1)   NOT NULL DEFAULT 1,
    `datetime`           datetime     NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb3
  COLLATE = utf8mb3_unicode_ci;

-- Daten-Export vom Benutzer nicht ausgewählt

-- Exportiere Struktur von Tabelle myDatabase.verbsSuccessCounterToUsers
CREATE TABLE IF NOT EXISTS `verbsSuccessCounterToUsers`
(
    `username`       varchar(30)  NOT NULL DEFAULT '0',
    `verbId`         int(9)       NOT NULL DEFAULT 0,
    `successCounter` mediumint(9) NOT NULL DEFAULT 0,
    `timestamp`      datetime     NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`username`, `verbId`) USING BTREE,
    KEY `successFactor` (`successCounter`) USING BTREE,
    KEY `FK_verbs` (`verbId`) USING BTREE,
    CONSTRAINT `verbsSuccessCounterToUsers_ibfk_1` FOREIGN KEY (`username`) REFERENCES `users` (`username`) ON DELETE NO ACTION ON UPDATE NO ACTION,
    CONSTRAINT `verbsSuccessCounterToUsers_ibfk_2` FOREIGN KEY (`verbId`) REFERENCES `verbs` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_general_ci;

-- Daten-Export vom Benutzer nicht ausgewählt

-- Exportiere Struktur von Tabelle myDatabase.words
CREATE TABLE IF NOT EXISTS `words`
(
    `id`       int(9)       NOT NULL AUTO_INCREMENT,
    `german`   varchar(255) NOT NULL,
    `norsk`    varchar(255) NOT NULL,
    `active`   tinyint(1)   NOT NULL DEFAULT 1,
    `datetime` datetime     NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb3
  COLLATE = utf8mb3_unicode_ci;

-- Daten-Export vom Benutzer nicht ausgewählt

-- Exportiere Struktur von Tabelle myDatabase.wordsSuccessCounterToUsers
CREATE TABLE IF NOT EXISTS `wordsSuccessCounterToUsers`
(
    `username`       varchar(30)  NOT NULL DEFAULT '0',
    `wordId`         int(9)       NOT NULL DEFAULT 0,
    `successCounter` mediumint(9) NOT NULL DEFAULT 0,
    `timestamp`      datetime     NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`username`, `wordId`) USING BTREE,
    KEY `successFactor` (`successCounter`) USING BTREE,
    KEY `FK_words` (`wordId`) USING BTREE,
    CONSTRAINT `wordsSuccessCounterToUsers_ibfk_1` FOREIGN KEY (`username`) REFERENCES `users` (`username`) ON DELETE NO ACTION ON UPDATE NO ACTION,
    CONSTRAINT `wordsSuccessCounterToUsers_ibfk_2` FOREIGN KEY (`wordId`) REFERENCES `words` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_general_ci;

-- Daten-Export vom Benutzer nicht ausgewählt

/*!40103 SET TIME_ZONE = IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE = IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS = IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT = @OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES = IFNULL(@OLD_SQL_NOTES, 1) */;
