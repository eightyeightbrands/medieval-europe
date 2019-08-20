update suggestions set status = 'completed' where status = 'funded';
ALTER TABLE `suggestions`
	CHANGE COLUMN `sponsoredamount` `sponsoredamount` INT(11) NOT NULL DEFAULT '0' AFTER `baesianrating`;

ALTER TABLE `suggestions`
	CHANGE COLUMN `quote` `quote` INT(11) NOT NULL DEFAULT '0' AFTER `averagerating`;
