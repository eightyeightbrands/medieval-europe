select ss.type,count(*) from structures s
left outer join structure_types ss on ss.id=s.structure_type_id
where ss.subtype='other' and ss.parenttype not in ('battlefield','well','dump','fish_shoal')
and region_id in (select id from regions where regions.name !='kingdoms.kingdom-independent')
group by ss.type