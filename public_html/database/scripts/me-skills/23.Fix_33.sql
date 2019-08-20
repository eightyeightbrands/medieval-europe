delete from cfgitem_dependencies  
where cfgitem_id = (select id from cfgitems where tag = 'sturdyworkbench');

delete from structure_types_cfgitems  where  cfgitem_id = (select id from cfgitems where tag = 'sturdyworkbench');

delete from cfgitems where tag = 'sturdyworkbench';
