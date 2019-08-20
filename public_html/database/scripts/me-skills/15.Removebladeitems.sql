INSERT INTO `cfgitem_dependencies` (`id`, `cfgitem_id`, `type`, `source_cfgitem_id`, `quantity`) VALUES (NULL, (select id from cfgitems where tag = 'pickaxe'), 'item', (select id from cfgitems where tag = 'iron_piece'), 8);

delete from cfgitem_dependencies where source_cfgitem_id = (select id from cfgitems where tag = 'pickaxe_hardhead');
delete from cfgitem_dependencies where cfgitem_id = (select id from cfgitems where tag = 'pickaxe_hardhead');

INSERT INTO `cfgitem_dependencies` (`id`, `cfgitem_id`, `type`, `source_cfgitem_id`, `quantity`) VALUES (NULL, (select id from cfgitems where tag = 'handaxe'), 'item', (select id from cfgitems where tag = 'iron_piece'), 2);

delete from cfgitem_dependencies where source_cfgitem_id = (select id from cfgitems where tag = 'handaxe_blade');
delete from cfgitem_dependencies where cfgitem_id = (select id from cfgitems where tag = 'handaxe_blade');

INSERT INTO `cfgitem_dependencies` (`id`, `cfgitem_id`, `type`, `source_cfgitem_id`, `quantity`) VALUES (NULL, (select id from cfgitems where tag = 'hoe'), 'item', (select id from cfgitems where tag = 'iron_piece'), 4);

delete from cfgitem_dependencies where source_cfgitem_id = (select id from cfgitems where tag = 'hoe_blade');
delete from cfgitem_dependencies where cfgitem_id = (select id from cfgitems where tag = 'hoe_blade');

INSERT INTO `cfgitem_dependencies` (`id`, `cfgitem_id`, `type`, `source_cfgitem_id`, `quantity`) VALUES (NULL, (select id from cfgitems where tag = 'knife'), 'item', (select id from cfgitems where tag = 'iron_piece'), 4);

delete from cfgitem_dependencies where source_cfgitem_id = (select id from cfgitems where tag = 'knife_blade');
delete from cfgitem_dependencies where cfgitem_id = (select id from cfgitems where tag = 'knife_blade');





