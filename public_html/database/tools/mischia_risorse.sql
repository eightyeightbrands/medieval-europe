/*applicando le query di prima lancio l'update random sulle risorse di tutte le regioni abilitate (non del regno escluso e non del mare)*/

update structures s set
s.structure_type_id=
(
SELECT id FROM (select  * from structure_types where subtype='other' and parenttype 
not in ('battlefield','well','dump','fish_shoal')) as risorse
ORDER BY rand() LIMIT 1
),
s.size='medium'
where s.structure_type_id in 
(select  id from structure_types where subtype='other' and parenttype not in ('battlefield','well','dump','fish_shoal'))
and region_id in (select id from regions where regions.kingdom_id not in (37,50));

/*cancello tutte le risorse tranne i pesci perchè rimangono uguali
*/
 delete from structure_resources where resource !='fish';
 
 /*inserisco le strutture/risorse nella structure resources*/
 
  insert into structure_resources  
 select NULL, s.id,'salt_heap',100,100,1448231990 from structures s
 where structure_type_id in ( select id from structure_types where type ='saltern');
 
 
  insert into structure_resources  
 select NULL, s.id,'iron_piece',100,100,1448231990 from structures s
 where structure_type_id in ( select id from structure_types where type ='mine_iron');
 
 
  insert into structure_resources  
 select NULL, s.id,'cows',100,100,1448231990 from structures s
 where structure_type_id in ( select id from structure_types where type ='breeding_cow_region');
 
 
  insert into structure_resources  
 select NULL, s.id,'sheeps',100,100,1448231990 from structures s
 where structure_type_id in ( select id from structure_types where type ='breeding_sheep_region');
 
  insert into structure_resources  
 select NULL, s.id,'coal_piece',100,100,1448231990 from structures s
 where structure_type_id in ( select id from structure_types where type ='mine_coal');
 
  insert into structure_resources  
 select NULL, s.id,'stone_piece',100,100,1448231990 from structures s
 where structure_type_id in ( select id from structure_types where type ='mine_stone');
 
  insert into structure_resources  
 select NULL, s.id,'bees',100,100,1448231990 from structures s
 where structure_type_id in ( select id from structure_types where type ='breeding_bee_region');
 
  insert into structure_resources  
 select NULL, s.id,'gold_piece',100,100,1448231990 from structures s
 where structure_type_id in ( select id from structure_types where type ='mine_gold'); 
 
  insert into structure_resources  
 select NULL, s.id,'sand_heap',100,100,1448231990 from structures s
 where structure_type_id in ( select id from structure_types where type ='cave_white_sand');
 
  insert into structure_resources  
 select NULL, s.id,'silkworms',100,100,1448231990 from structures s
 where structure_type_id in ( select id from structure_types where type ='breeding_silkworm_region');
 
 
  insert into structure_resources  
 select NULL, s.id,'medmushroom',100,100,1448231990 from structures s
 where structure_type_id in ( select id from structure_types where type ='forest'); 
 
  insert into structure_resources  
 select NULL, s.id,'mandragora',100,100,1448231990 from structures s
 where structure_type_id in ( select id from structure_types where type ='forest');
 
  insert into structure_resources  
 select NULL, s.id,'wood_piece',100,100,1448231990 from structures s
 where structure_type_id in ( select id from structure_types where type ='forest');
 
 
 
 
 
 



