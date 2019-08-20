ALTER TABLE `cfgcourses`
	DROP COLUMN `name`,
	DROP COLUMN `description`,
	DROP COLUMN `minlevel`;

INSERT INTO `cfgcourses` (`id`, `tag`,`structuretype`) VALUES (NULL, 'metallurgy_1','academy_1');
INSERT INTO `cfgcourses` (`id`, `tag`,`structuretype`) VALUES (NULL, 'parry_1','trainingground_1');
drop table cfgcourses;