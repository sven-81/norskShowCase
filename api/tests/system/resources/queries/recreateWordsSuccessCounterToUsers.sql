ALTER TABLE `wordsSuccessCounterToUsers`
	ADD COLUMN `successCounter` MEDIUMINT(9) NOT NULL AFTER `timestamp`;