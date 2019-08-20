update cfgitem_dependencies c set quantity = 1 where c.cfgitem_id = 
( select id from cfgitems where tag = 'woodensword')
and source_cfgitem_id = 
( select id from cfgitems where tag = 'wood_piece');

update cfgitems set spare5 = 3, spare6 = 3, spare2 = 60 where tag = 'woodensword'; 

update cfgitems set spare5 = 20, spare6 = 20, spare2 = 60 where tag = 'paper_piece'; 

update cfgitems set craftingenabled = true where tag = 'furnace';

INSERT INTO `structure_types_cfgitems` (`id`, `structure_type_id`, `cfgitem_id`) VALUES (NULL, (select id from structure_types where type = 'herbalist_1'), (select id from cfgitems where tag = 'writingkit') );
INSERT INTO `structure_types_cfgitems` (`id`, `structure_type_id`, `cfgitem_id`) VALUES (NULL, (select id from structure_types where type = 'herbalist_2'), (select id from cfgitems where tag = 'writingkit') );

INSERT INTO `cfgitem_dependencies` (`id`, `cfgitem_id`, `type`, `source_cfgitem_id`, `quantity`) VALUES (NULL, (select id from cfgitems where tag = 'furnace'), 'item', (select id from cfgitems where tag = 'coal_piece'), 20);


ALTER TABLE `character_stats`
	CHANGE COLUMN `value` `value` DECIMAL(30,2) NOT NULL DEFAULT '0' AFTER `name`;
	
update character_stats set value = stat1, stat1 = null where name = 'skill' and stat1 is not null;
update character_stats set stat1 = null where name = 'skill';

delete from character_stats where param1 like 'battle\_%';

update character_stats set value = 0 where name = 'studiedhours' and param1 = 'battleconst' and value < 0;

update structure_types set name ='structures.terrain_1' where id =1;
update structure_types set name ='structures.tavern_1' where id =2;
update structure_types set name ='structures.house_1' where id =4;
update structure_types set name ='structures.house_2' where id =5;
update structure_types set name ='structures.market_1' where id =6;
update structure_types set name ='structures.royalpalace_1' where id =7;
update structure_types set name ='structures.castle_1' where id =8;
update structure_types set name ='structures.forest' where id =13;
update structure_types set name ='structures.shop_blacksmith_1' where id =14;
update structure_types set name ='structures.shop_carpenter_1' where id =15;
update structure_types set name ='structures.breeding_cow' where id =16;
update structure_types set name ='structures.mine_iron' where id =17;
update structure_types set name ='structures.breeding_sheep' where id =18;
update structure_types set name ='structures.breeding_pig' where id =19;
update structure_types set name ='structures.court_1' where id =21;
update structure_types set name ='structures.barracks_1' where id =22;
update structure_types set name ='structures.shop_chef_1' where id =23;
update structure_types set name ='structures.shop_herbalist_1' where id =24;
update structure_types set name ='structures.battlefield' where id =25;
update structure_types set name ='structures.shop_tailor_1' where id =27;
update structure_types set name ='structures.breeding_silkworm' where id =28;
update structure_types set name ='structures.harbor_1' where id =29;
update structure_types set name ='structures.mine_gold' where id =30;
update structure_types set name ='structures.shop_goldsmith_1' where id =31;
update structure_types set name ='structures.academy_1' where id =32;
update structure_types set name ='structures.buildingsite' where id =33;
update structure_types set name ='structures.trainingground_1' where id =34;
update structure_types set name ='structures.mine_stone' where id =35;
update structure_types set name ='structures.breeding_bee' where id =36;
update structure_types set name ='structures.fish_shoal' where id =37;
update structure_types set name ='structures.mine_clay' where id =38;
update structure_types set name ='structures.saltern' where id =39;
update structure_types set name ='structures.mine_coal' where id =40;
update structure_types set name ='structures.cave_white_sand' where id =41;
update structure_types set name ='structures.dump' where id =42;
update structure_types set name ='structures.shop_potter_1' where id =43;
update structure_types set name ='structures.nativevillage' where id =44;
update structure_types set name ='structures.breeding_cow_region' where id =45;
update structure_types set name ='structures.breeding_sheep_region' where id =46;
update structure_types set name ='structures.breeding_pig_region' where id =47;
update structure_types set name ='structures.breeding_silkworm_region' where id =48;
update structure_types set name ='structures.breeding_bee_region' where id =49;
update structure_types set name ='structures.house_3' where id =50;
update structure_types set name ='structures.house_4' where id =51;
update structure_types set name ='structures.house_5' where id =52;
update structure_types set name ='structures.house_6' where id =53;
update structure_types set name ='structures.religion_1_rome' where id =57;
update structure_types set name ='structures.religion_1_turnu' where id =59;
update structure_types set name ='structures.religion_2_rome' where id =60;
update structure_types set name ='structures.religion_2_turnu' where id =62;
update structure_types set name ='structures.religion_3_rome' where id =63;
update structure_types set name ='structures.religion_3_turnu' where id =65;
update structure_types set name ='structures.religion_4_rome' where id =66;
update structure_types set name ='structures.religion_4_turnu' where id =68;
update structure_types set name ='structures.barracks_2' where id =69;
update structure_types set name ='structures.shop_blacksmith_2' where id =70;
update structure_types set name ='structures.terrain_2' where id =71;
update structure_types set name ='structures.shop_carpenter_2' where id =72;
update structure_types set name ='structures.shop_chef_2' where id =73;
update structure_types set name ='structures.shop_goldsmith_2' where id =74;
update structure_types set name ='structures.shop_herbalist_2' where id =75;
update structure_types set name ='structures.shop_potter_2' where id =76;
update structure_types set name ='structures.shop_tailor_2' where id =77;
update structure_types set name ='structures.religion_1_cairo' where id =78;
update structure_types set name ='structures.religion_2_cairo' where id =79;
update structure_types set name ='structures.religion_3_cairo' where id =80;
update structure_types set name ='structures.religion_4_cairo' where id =81;
update structure_types set name ='structures.well_1' where id =82;
update structure_types set name ='structures.shop_distillery_1' where id =83;
update structure_types set name ='structures.shop_distillery_2' where id =84;
update structure_types set name ='structures.religion_1_kiev' where id =85;
update structure_types set name ='structures.religion_2_kiev' where id =86;
update structure_types set name ='structures.religion_3_kiev' where id =87;
update structure_types set name ='structures.religion_4_kiev' where id =88;
update structure_types set name ='structures.watchtower_1' where id =89;
update structure_types set name ='structures.religion_1_norse' where id =90;
update structure_types set name ='structures.religion_2_norse' where id =91;
update structure_types set name ='structures.religion_3_norse' where id =92;
update structure_types set name ='structures.religion_4_norse' where id =93;
update structure_types set name ='structures.billboard' where id =94;
update structure_types set name ='structures.academy_2' where id =95;
update structure_types set name ='structures.trainingground_2' where id =96;


update structure_types set name = replace(name, 'shop_', ''); 


update cfgitem_dependencies c 
set quantity = 15 where c.cfgitem_id = 
( select id from cfgitems where tag = 'inkbulb')
and source_cfgitem_id = 
( select id from cfgitems where tag = 'mulberry');

update cfgitem_dependencies c 
set quantity = 4 where c.cfgitem_id = 
( select id from cfgitems where tag = 'inkbulb')
and source_cfgitem_id = 
( select id from cfgitems where tag = 'glassbulb');

update cfgitems set spare2 = 120, spare5=4, spare6=4 where tag = 'inkbulb';

update cfgitem_dependencies c 
set quantity = 2 where c.cfgitem_id = 
( select id from cfgitems where tag = 'woodensword')
and source_cfgitem_id = 
( select id from cfgitems where tag = 'wood_piece');

update cfgitems set spare2 = 240, spare5=1, spare6=1 where tag = 'woodensword';