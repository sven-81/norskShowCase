ALTER TABLE `verbsSuccessCounterToUsers`
	ADD COLUMN `successCounter` MEDIUMINT(9) NOT NULL AFTER `timestamp`;