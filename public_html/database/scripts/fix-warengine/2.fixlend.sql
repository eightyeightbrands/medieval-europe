ALTER TABLE `structure_lentitems`
	ADD COLUMN `returnedtime` INT(10) NULL AFTER `deliverytime`,
	DROP COLUMN `item_id`;
ALTER TABLE `ar_structures`
	CHANGE COLUMN `size` `size` VARCHAR(10) NULL DEFAULT NULL AFTER `attribute6`;
ALTER TABLE `ar_structures`
	CHANGE COLUMN `silvercoins` `silvercoins` DECIMAL(10,2) NULL DEFAULT NULL AFTER `message`,
	CHANGE COLUMN `doubloons` `doubloons` MEDIUMINT(9) NULL DEFAULT NULL AFTER `silvercoins`;
