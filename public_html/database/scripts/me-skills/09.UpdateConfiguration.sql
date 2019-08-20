update cfgitems set tag = 'inkbulb', name = 'inkbulb_name', description = 'inkbulb_description' where tag = 'studentkit';

-- Writing Kit

INSERT INTO `cfgitems` (`id`, `church_id`, `tag`, `parenttag`, `name`, `description`, `price`, `parentcategory`, `category`, `subcategory`, `mindmg`, `maxdmg`, `bluntperc`, `cutperc`, `reach`, `armorpenetration`, `critical`, `text`, `defense`, `part`, `coverage`, `size`, `weight`, `droppable`, `takeable`, `stealable`, `marketsellable`, `structuresellable`, `canbesent`, `destroyontrash`, `trashable`, `canbedonated`, `taxable`, `confiscable`, `colorable`, `crafting_slot`, `craftingenabled`, `car_modifier`, `linked_role`, `spare1`, `spare2`, `spare3`, `spare4`, `spare5`, `spare6`, `spare7`, `wearfactor`) VALUES (NULL, NULL, 'writingkit', 'writingkit', 'items.writingkit_name', 'items.writingkit_description', 0, 'resources', 'resource', 'craftingpart', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 100, 1, 1, 1, 1, 0, 1, 0, 1, 1, 1, 1, 0, 100, 1, 0, NULL, NULL, '216', NULL, NULL, '1', '1', '', NULL);

update cfgitem_dependencies set cfgitem_id = ( select id from cfgitems where tag = 'writingkit') 
where source_cfgitem_id = ( select id from cfgitems where tag = 'chickenfeather');

INSERT INTO `cfgitem_dependencies` (`id`, `cfgitem_id`, `type`, `source_cfgitem_id`, `quantity`) VALUES (NULL, (select id from cfgitems where tag = 'writingkit'), 'item', (select id from cfgitems where tag = 'inkbulb'), 1);

update cfgitems set weight = 50 where tag = 'inkbulb';

update cfgitems set spare2 = 60 where tag = 'writingkit';

update cfgitem_dependencies 
set source_cfgitem_id = ( select id from cfgitems where tag = 'writingkit') 
where cfgitem_id = ( select id from cfgitems where tag = 'holybook')
and source_cfgitem_id = ( select id from cfgitems where tag = 'inkbulb') ;

INSERT INTO `cfgitem_dependencies` (`id`, `cfgitem_id`, `type`, `source_cfgitem_id`, `quantity`) VALUES (NULL, (select id from cfgitems where tag = 'furnace'), 'item', (select id from cfgitems where tag = 'brick'), 50);

INSERT INTO `cfgitem_dependencies` (`id`, `cfgitem_id`, `type`, `source_cfgitem_id`, `quantity`) VALUES (NULL, (select id from cfgitems where tag = 'furnace'), 'item', (select id from cfgitems where tag = 'stone_piece'), 10);

INSERT INTO `cfgitem_dependencies` (`id`, `cfgitem_id`, `type`, `source_cfgitem_id`, `quantity`) VALUES (NULL, (select id from cfgitems where tag = 'furnace'), 'item', (select id from cfgitems where tag = 'sand_heap'), 20);

INSERT INTO `structure_types_cfgitems` (`id`, `structure_type_id`, `cfgitem_id`) VALUES (NULL, (select id from structure_types where type = 'potter_1') , (select id from cfgitems where tag = 'furnace'));

INSERT INTO `structure_types_cfgitems` (`id`, `structure_type_id`, `cfgitem_id`) VALUES (NULL, (select id from structure_types where type = 'potter_2') , (select id from cfgitems where tag = 'furnace'));
	

