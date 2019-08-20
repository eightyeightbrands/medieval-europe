ALTER TABLE `structures`
	CHANGE COLUMN `storage` `customstorage` INT(11) NULL DEFAULT NULL AFTER `history`;
update structure_types set type = 'house_1' where level = 1 and parenttype = 'house';
update structure_types set type = 'house_2' where level = 2 and parenttype = 'house';
update structure_types set type = 'house_3' where level = 3 and parenttype = 'house';
update structure_types set type = 'house_4' where level = 4 and parenttype = 'house';
update structure_types set type = 'house_5' where level = 5 and parenttype = 'house';
update structure_types set type = 'house_6' where level = 6 and parenttype = 'house';

delete from character_actions where action = 'decreaseskills';

update character_actions set param2 = 'logica' where param2 = 'logic' 
and status = 'running';