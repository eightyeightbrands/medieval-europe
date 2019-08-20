delete from cfgitem_dependencies  
where cfgitem_id in (select id from cfgitems where tag like '%blade%');

delete from structure_types_cfgitems  where cfgitem_id in (select id from cfgitems where tag like '%blade%');

delete from cfgitems where tag like '%blade%';

-- 

delete from cfgitem_dependencies  
where cfgitem_id in (select id from cfgitems where tag like '%hardhead%');

delete from structure_types_cfgitems  where cfgitem_id in (select id from cfgitems where tag like '%hardhead%');

delete from cfgitems where tag like '%hardhead%';