ALTER TABLE `structures`
	ADD COLUMN `status` VARCHAR(50) NULL DEFAULT NULL AFTER `state`;
ALTER TABLE `ar_structures`
	ADD COLUMN `status` VARCHAR(50) NULL DEFAULT NULL AFTER `state`;
delete from structures where structure_type_id not in (select id from structure_types);
ALTER TABLE `structures`
	ADD CONSTRAINT `FK_structures_structure_types` FOREIGN KEY (`structure_type_id`) REFERENCES `structure_types` (`id`);
