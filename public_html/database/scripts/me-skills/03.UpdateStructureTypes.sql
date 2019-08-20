update structure_types set type = 'tavern_1' where parenttype = 'tavern';
update structure_types set type = 'court_1' where parenttype = 'court';

ALTER TABLE `character_sentences`
	CHANGE COLUMN `character_id` `character_id` INT(11) NULL DEFAULT NULL AFTER `id`,
	CHANGE COLUMN `issued_by` `issued_by` INT(11) NULL DEFAULT NULL AFTER `character_id`,
	CHANGE COLUMN `issuedate` `issuedate` INT(11) NULL DEFAULT NULL AFTER `issued_by`,
	CHANGE COLUMN `text` `text` VARCHAR(255) NULL DEFAULT NULL AFTER `issuedate`,
	CHANGE COLUMN `status` `status` VARCHAR(25) NULL DEFAULT NULL AFTER `text`,
	CHANGE COLUMN `prison_id` `prison_id` INT(11) NULL DEFAULT NULL AFTER `imprisonment_hours_given`;

ALTER TABLE `structures`
	CHANGE COLUMN `image` `image` VARCHAR(255) NULL DEFAULT NULL AFTER `description`;

update structure_types set type = 'royalpalace_1' where type = 'royalpalace';	