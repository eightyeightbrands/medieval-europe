ALTER TABLE `structures`
	ADD COLUMN `name` VARCHAR(128) NULL DEFAULT NULL AFTER `locked`;

ALTER TABLE `ar_structures`
	ADD COLUMN `name` VARCHAR(128) NULL DEFAULT NULL AFTER `locked`;

ALTER TABLE `structures`
	ADD COLUMN `hourlywage` VARCHAR(128) NULL DEFAULT NULL AFTER `name`;

ALTER TABLE `ar_structures`
	ADD COLUMN `hourlywage` VARCHAR(128) NULL DEFAULT NULL AFTER `name`;

update structures set name = attribute6;
