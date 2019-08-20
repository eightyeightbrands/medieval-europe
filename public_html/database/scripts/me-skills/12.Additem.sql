INSERT INTO `cfgitems` (`id`, `church_id`, `tag`, `parenttag`, `name`, `description`, `price`, `parentcategory`, `category`, `subcategory`, `mindmg`, `maxdmg`, `bluntperc`, `cutperc`, `reach`, `armorpenetration`, `critical`, `text`, `defense`, `part`, `coverage`, `size`, `weight`, `droppable`, `takeable`, `stealable`, `marketsellable`, `structuresellable`, `canbesent`, `destroyontrash`, `trashable`, `canbedonated`, `taxable`, `confiscable`, `colorable`, `crafting_slot`, `craftingenabled`, `car_modifier`, `linked_role`, `spare1`, `spare2`, `spare3`, `spare4`, `spare5`, `spare6`, `spare7`, `wearfactor`) VALUES (NULL, NULL, 'woodensword', 'woodensword', 'items.woodensword_name', 'items.woodensword_description', 0, 'resources', 'resource', 'craftingpart', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 500, 1, 1, 1, 1, 0, 1, 0, 1, 1, 1, 1, 0, 100, 1, 0, NULL, NULL, '216', NULL, NULL, '1', '1', '', NULL);


update cfgitems set craftingenabled = true where tag = 'woodensword';

INSERT INTO `cfgitem_dependencies` (`id`, `cfgitem_id`, `type`, `source_cfgitem_id`, `quantity`) VALUES (NULL, (select id from cfgitems where tag = 'woodensword'), 'item', (select id from cfgitems where tag = 'wood_piece'), 3);

INSERT INTO `structure_types_cfgitems` (`id`, `structure_type_id`, `cfgitem_id`) VALUES (NULL, (select id from structure_types where type = 'carpenter_1'), (select id from cfgitems where tag = 'woodensword') );

INSERT INTO `structure_types_cfgitems` (`id`, `structure_type_id`, `cfgitem_id`) VALUES (NULL, (select id from structure_types where type = 'carpenter_2'), (select id from cfgitems where tag = 'woodensword') );


update cfgitems set 
parentcategory='tools',
category = 'tool',
subcategory='tool' 
where tag = 'woodensword';