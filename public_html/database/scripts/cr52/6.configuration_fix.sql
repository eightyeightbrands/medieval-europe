INSERT INTO `cfgitem_dependencies` (`id`, `cfgitem_id`, `type`, `source_cfgitem_id`, `quantity`) VALUES (NULL, (select id from cfgitems where tag = 'chainmail_armor_legs'), 'item', (select id from cfgitems where tag = 'coal_piece'), 4);

INSERT INTO `cfgitem_dependencies` (`id`, `cfgitem_id`, `type`, `source_cfgitem_id`, `quantity`) VALUES (NULL, (select id from cfgitems where tag = 'chainmail_armor_legs'), 'item', (select id from cfgitems where tag = 'iron_piece'), 20);

INSERT INTO `cfgitem_dependencies` (`id`, `cfgitem_id`, `type`, `source_cfgitem_id`, `quantity`) VALUES (NULL, (select id from cfgitems where tag = 'chainmail_armor_feet'), 'item', (select id from cfgitems where tag = 'coal_piece'), 2);

INSERT INTO `cfgitem_dependencies` (`id`, `cfgitem_id`, `type`, `source_cfgitem_id`, `quantity`) VALUES (NULL, (select id from cfgitems where tag = 'chainmail_armor_feet'), 'item', (select id from cfgitems where tag = 'iron_piece'), 15);

update cfgitems set coverage = replace( coverage, 'armor', 'torso' );

update cfgitems set defense = 3, craftingenabled = 0 where tag = 'wooden_normanshield_1';

update boardmessages set messageclass = 'duel' where message like '__events.duel%' ;
ALTER TABLE `ar_structures`
	CHANGE COLUMN `size` `size` VARCHAR(10) NULL DEFAULT NULL AFTER `attribute6`;
ALTER TABLE `ar_structures`
	CHANGE COLUMN `silvercoins` `silvercoins` DECIMAL(10,2) NULL DEFAULT NULL AFTER `message`,
	CHANGE COLUMN `doubloons` `doubloons` MEDIUMINT(9) NULL DEFAULT NULL AFTER `silvercoins`;
