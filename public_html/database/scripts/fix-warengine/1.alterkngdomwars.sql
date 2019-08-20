ALTER TABLE `kingdom_wars`
	ADD COLUMN `role` VARCHAR(50) NULL AFTER `target_kingdom_id`;
ALTER TABLE `kingdom_wars`
	DROP COLUMN `role`;
ALTER TABLE `kingdom_wars_allies`
	ADD COLUMN `role` VARCHAR(50) NULL AFTER `kingdom_id`;
	