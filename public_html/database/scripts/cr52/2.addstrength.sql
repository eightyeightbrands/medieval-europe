ALTER TABLE `cfgitems`
	ADD COLUMN `requiredstrength` TINYINT NULL DEFAULT NULL AFTER `description`;
	
update cfgitems set parenttag = 'leather_armor' where tag like 'leather_armor%';

update cfgitems set requiredstrength = 7 where parenttag in ( 'leather_armor' );
	