
CREATE TABLE `suggestions` (
	`id` INT NOT NULL AUTO_INCREMENT,
	`author` VARCHAR(128) NOT NULL,
	`rating` DECIMAL(3,2) NOT NULL DEFAULT '0',
	`votes` INT NOT NULL DEFAULT '0',
	`quote` INT NULL DEFAULT '0',
	`sponsoramount` INT NULL,
	`status` VARCHAR(50) NOT NULL DEFAULT 'new',
	`title` VARCHAR(50) NOT NULL,
	`body` TEXT NOT NULL,
	INDEX `Indice 1` (`id`)
)
COLLATE='latin1_swedish_ci'
ENGINE=InnoDB
;

ALTER TABLE `suggestions`
	ALTER `author` DROP DEFAULT;
ALTER TABLE `suggestions`
	CHANGE COLUMN `author` `character_id` INT(11) NOT NULL AFTER `id`;
ALTER TABLE `suggestions`
	ALTER `title` DROP DEFAULT;
ALTER TABLE `suggestions`
	CHANGE COLUMN `sponsoramount` `sponsoredamount` INT(11) NULL DEFAULT NULL AFTER `quote`,
	ADD COLUMN `discussionurl` VARCHAR(50) NULL DEFAULT 'new' AFTER `status`;
ALTER TABLE `suggestions`
	ADD COLUMN `created` INT NULL AFTER `body`;
	ALTER TABLE `suggestions`
	CHANGE COLUMN `discussionurl` `discussionurl` VARCHAR(255) NULL DEFAULT 'new' AFTER `status`;
ALTER TABLE `suggestions`
	ADD COLUMN `detailsurl` VARCHAR(255) NOT NULL AFTER `discussionurl`;
ALTER TABLE `suggestions`
	ALTER `detailsurl` DROP DEFAULT;
ALTER TABLE `suggestions`
	CHANGE COLUMN `detailsurl` `detailsurl` VARCHAR(255) NULL AFTER `discussionurl`;
ALTER TABLE `suggestions`
	ADD COLUMN `oldsuggestion_id` INT NULL AFTER `created`;
ALTER TABLE `suggestions`
	ADD COLUMN `baesianrating` DECIMAL(10,2) NULL DEFAULT '0' AFTER `quote`;
ALTER TABLE `suggestions`
	ADD COLUMN `totalrating` INT(11) NOT NULL DEFAULT '0'  AFTER `character_id`,
	CHANGE COLUMN `rating` `averagerating` DECIMAL(3,2) NOT NULL DEFAULT '0.00' AFTER `votes`;
