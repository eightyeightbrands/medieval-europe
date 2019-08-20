delete from boardmessages where category='suggestion';
ALTER TABLE `suggestions`
	ADD COLUMN `reason` VARCHAR(255) NULL DEFAULT 'new' AFTER `discussionurl`;

drop table character_premiumbonuses_bck;