delete from structure_types_cfgitems where cfgitem_id in
(select id from cfgitems where tag like '%_blade');

delete from cfgitems where tag like '%_blade';