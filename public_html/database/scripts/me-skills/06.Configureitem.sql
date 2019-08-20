-- piuma di pollo
INSERT INTO `cfgitems` (`id`, `church_id`, `tag`, `parenttag`, `name`, `description`, `price`, `parentcategory`, `category`, `subcategory`, `mindmg`, `maxdmg`, `bluntperc`, `cutperc`, `reach`, `armorpenetration`, `critical`, `text`, `defense`, `part`, `coverage`, `size`, `weight`, `droppable`, `takeable`, `stealable`, `marketsellable`, `structuresellable`, `canbesent`, `destroyontrash`, `trashable`, `canbedonated`, `taxable`, `confiscable`, `colorable`, `crafting_slot`, `craftingenabled`, `car_modifier`, `linked_role`, `spare1`, `spare2`, `spare3`, `spare4`, `spare5`, `spare6`, `spare7`, `wearfactor`) VALUES (NULL, NULL, 'chickenfeather', 'chickenfeather', 'items.chickenfeather_name', 'items.chickenfeather_description', 0, 'resources', 'resource', 'craftingpart', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 10, 1, 1, 1, 1, 0, 1, 0, 1, 1, 1, 1, 0, 100, 1, 0, NULL, NULL, '216', NULL, NULL, '1', '1', '', NULL);


update cfgitems set craftingenabled = false where tag = 'chickenfeather';
update cfgitems set spare1 = null, spare2 = null, spare3 = null, spare4 = null, spare5 = null, spare6 = null,
spare7 = null where tag = 'chickenfeather';

-- kit studente


update cfgitems set tag = 'studentkit', name = 'studentkit_name', description = 'studentkit_description' where tag = 'inkbulb';

INSERT INTO `cfgitem_dependencies` (`id`, `cfgitem_id`, `type`, `source_cfgitem_id`, `quantity`) VALUES (NULL, (select id from cfgitems where tag = 'studentkit'), 'item', (select id from cfgitems where tag = 'chickenfeather'), 1);

update cfgitems set parentcategory='tools', 
name= 'items.studentkit_name', description = 'items.studentkit_description', 
parenttag='studentkit', weight = 0.05, part='right_hand' where tag = 'studentkit';