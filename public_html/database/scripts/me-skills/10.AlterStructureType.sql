ALTER TABLE `structure_types` 	DROP COLUMN `storage`;

update cfgitems set parenttag = 'inkbulb',
name = 'items.inkbulb_name', description = 'items.inkbulb_description' 
where tag = 'inkbulb';
