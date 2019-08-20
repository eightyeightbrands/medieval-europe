INSERT INTO `cfgitems` (`id`, `church_id`, `tag`, `parenttag`, `name`, `description`, `price`, `parentcategory`, `category`, `subcategory`, `mindmg`, `maxdmg`, `bluntperc`, `cutperc`, `reach`, `armorpenetration`, `critical`, `text`, `defense`, `part`, `coverage`, `size`, `weight`, `droppable`, `takeable`, `stealable`, `marketsellable`, `structuresellable`, `canbesent`, `destroyontrash`, `trashable`, `canbedonated`, `taxable`, `confiscable`, `colorable`, `crafting_slot`, `craftingenabled`, `car_modifier`, `linked_role`, `spare1`, `spare2`, `spare3`, `spare4`, `spare5`, `spare6`, `spare7`, `wearfactor`) VALUES (NULL, NULL, 'leather_helmet', 'leather_helmet', 'items.leather_helmet_name', 'items.leather_helmet_description', 0, 'armors', 'armor', 'armor', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 2.00, 'head', 'head', NULL, 2268, 1, 1, 1, 1, 0, 1, 0, 1, 1, 1, 1, 0, 100, 1, 0, NULL, NULL, '432', NULL, NULL, '1', '1', '', 1);

update cfgitems set weight = 1500 where tag = 'leather_helmet';

INSERT INTO `cfgitem_dependencies` (`id`, `cfgitem_id`, `type`, `source_cfgitem_id`, `quantity`) VALUES (NULL, (select id from cfgitems where tag = 'leather_helmet'), 'item', (select id from cfgitems where tag = 'coal_piece'), 1);

INSERT INTO `cfgitem_dependencies` (`id`, `cfgitem_id`, `type`, `source_cfgitem_id`, `quantity`) VALUES (NULL, (select id from cfgitems where tag = 'leather_helmet'), 'item', (select id from cfgitems where tag = 'iron_piece'), 1);

INSERT INTO `cfgitem_dependencies` (`id`, `cfgitem_id`, `type`, `source_cfgitem_id`, `quantity`) VALUES (NULL, (select id from cfgitems where tag = 'leather_helmet'), 'item', (select id from cfgitems where tag = 'leather_piece'), 3);

INSERT INTO `structure_types_cfgitems` (`id`, `structure_type_id`, `cfgitem_id`) VALUES (NULL, (select id from structure_types where type = 'tailor_1'), (select id from cfgitems where tag = 'leather_helmet') );

INSERT INTO `structure_types_cfgitems` (`id`, `structure_type_id`, `cfgitem_id`) VALUES (NULL, (select id from structure_types where type = 'tailor_2'), (select id from cfgitems where tag = 'leather_helmet') );

-- sposta leather armor a tailor
update structure_types_cfgitems set structure_type_id = (select id from structure_types where type = 'tailor_1') where id = 184;
update structure_types_cfgitems set structure_type_id = (select id from structure_types where type = 'tailor_2') where id = 203;

-- rinomina leather armor

update cfgitems set tag = 'leather_armor', parenttag = 'leather_armor', name = 'items.leather_armor_name', description = 'items.leather_armor_description' where tag = 'light_leather_armor_1';

update cfgitems set tag = 'leather_boots', parenttag = 'leather_boots', name = 'items.leather_boots_name', description = 'items.leather_boots_description' where tag = 'boots';

update cfgitems set tag = 'leather_armor_body', parenttag = 'leather_armor_body', name = 'items.leather_armor_body_name', description = 'items.leather_armor_body_description' where tag = 'leather_armor';

update cfgitems set tag = 'leather_armor_feet', parenttag = 'leather_armor_feet', name = 'items.leather_armor_feet_name', description = 'items.leather_armor_feet_description' where tag = 'leather_boots';

update cfgitems set tag = 'leather_armor_head', parenttag = 'leather_armor_head', name = 'items.leather_armor_head_name', description = 'items.leather_armor_head_description' where tag = 'leather_helmet';

update cfgitems set tag = 'leather_armor_legs', parenttag = 'leather_armor_legs', name = 'items.leather_armor_legs_name', description = 'items.leather_armor_legs_description' where tag = 'leather_trousers_2';
