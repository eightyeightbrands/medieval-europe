CREATE TABLE `ar_character_premiumbonuses` (
	`id` INT(11) NOT NULL,
	`user_id` INT(11) UNSIGNED NOT NULL,
	`targetuser_id` INT(11) UNSIGNED NULL DEFAULT NULL,
	`targetcharname` VARCHAR(50) NULL DEFAULT NULL,
	`character_id` INT(11) NOT NULL,
	`structure_id` INT(11) NULL DEFAULT NULL,
	`cfgpremiumbonus_id` TINYINT(4) NOT NULL,
	`cfgpremiumbonus_cut_id` SMALLINT(6) NULL DEFAULT NULL,
	`starttime` INT(11) NOT NULL,
	`endtime` INT(11) NOT NULL,
	`doubloons` INT(11) NOT NULL,
	`param1` VARCHAR(256) NULL DEFAULT NULL,
	`param2` VARCHAR(256) NULL DEFAULT NULL,
	PRIMARY KEY (`id`),
	INDEX `cb_character_id` (`character_id`),
	INDEX `cfgpremiumbonus_id` (`cfgpremiumbonus_id`),
	INDEX `cfgpremiumbonus_cut_id` (`cfgpremiumbonus_cut_id`),
	INDEX `user_id` (`user_id`),
	INDEX `targetuser_id` (`targetuser_id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
;
