update cfgitems set price =0, tag = 'platemail_armor_body', parenttag = 'platemail_armor', name = 'items.platemail_armor_body_name', description = 'items.platemail_armor_body_description' where tag = 'plate_armor';

update cfgitems set price =0, tag = 'platemail_armor_head', parenttag = 'platemail_armor', name = 'items.platemail_armor_head_name', description = 'items.platemail_armor_head_description' where tag = 'iron_helmet_2';

update cfgitems set price =0, tag = 'platemail_armor_legs', parenttag = 'platemail_armor', name = 'items.platemail_armor_legs_name', description = 'items.platemail_armor_legs_description' where tag = 'iron_legs_armor_1';

update cfgitems set price =0, tag = 'platemail_armor_feet', parenttag = 'platemail_armor', name = 'items.platemail_armor_feet_name', description = 'items.platemail_armor_feet_description' where tag = 'iron_shoes_armor_1';

update cfgitems set price =0, tag = 'platemail_armor_shield', parenttag = 'platemail_armor', name = 'items.platemail_armor_shield_name', description = 'items.platemail_armor_shield_description' where tag = 'iron_normanshield_1';

update cfgitems set defense = 8, requiredstrength = 17 where parenttag = 'platemail_armor';

update cfgitems set price =0, tag = 'leather_armor_shield', parenttag = 'leather_armor', name = 'items.leather_armor_shield_name', description = 'items.leather_armor_shield_description' where tag = 'wooden_shield_1';

update cfgitems set price = 0, defense = 2, requiredstrength = 7 where parenttag = 'leather_armor' ;

update cfgitems set price = 0, tag = 'chainmail_armor_shield', parenttag = 'chainmail_armor', name = 'items.chainmail_armor_shield_name', description = 'items.chainmail_armor_shield_description' where tag = 'iron_shield_1';

update cfgitems set price = 0, defense = 2, requiredstrength = 7 where parenttag = 'leather_armor' ;

update cfgitems set price = 0, defense = 4, requiredstrength = 12 where parenttag = 'chainmail_armor' ;

update cfgitems set price = 0, defense = 6, requiredstrength = 17 where parenttag = 'chainmail_armor' ;

update cfgitems set price = 0, defense = 8, requiredstrength = 18 where parenttag = 'blackset_armor' ;

update cfgitems set price = 0, defense = 10, requiredstrength = 18 where parenttag = 'frenchchevalierset_armor' ;

update cfgitems set price = 0, defense = 11, requiredstrength = 20 where parenttag = 'mountainset_armor' ;

update cfgitems set price = 0, defense = 12, requiredstrength = 20 where parenttag = 'punisher_armor' ;

update cfgitems set parentcategory='weapons', category = 'weapon', subcategory = 'weapon', mindmg = 1, maxdmg = 1, bluntperc = 80, cutperc = 20, reach = 1, armorpenetration = 30, text = 'global.cuts', size = 'S', wearfactor = 1 where tag = 'woodensword';

update cfgitems set parentcategory = 'weapons' where tag = 'knife';

update cfgitems set critical = NULL where tag = 'short_sword';

update cfgitems set text = 'global.slashes' where parentcategory = 'weapons' and text is NULL;

-- remove items from weapons

update cfgitems set category = 'tool', subcategory = 'tool', mindmg = 0, maxdmg = 0, bluntperc = 0, cutperc = 0, reach = 0, armorpenetration = 0, critical = NULL, text = NULL where tag in ('pastoral_rome', 'scepter_kiev', 'scepter_norse', 'hoe', 'handaxe', 'pickaxe', 'shovel', 'hammer', 'mysticrod_turnu');

update cfgitems set defense = NULL ,price = 0 where parentcategory = 'weapons';

update cfgitems set spare4 = NULL where parentcategory = 'weapons';

-- imposta richiesta forza

update cfgitems set requiredstrength = 20 where tag in ('greataxe', 'halberd', 'fauchard', 'thepunisher', 'lockjaw');
update cfgitems set requiredstrength = 18 where tag in ('blacksword', 'durlindana');
update cfgitems set requiredstrength = 16 where tag in ('mace', 'warhammer', 'morningstar');
update cfgitems set requiredstrength = 14 where tag in ('longsword', 'scimitar');
update cfgitems set requiredstrength = 10 where tag in ('short_sword');
update cfgitems set requiredstrength = 5 where tag in ('knife', 'woodensword');

update cfgitems set critical = replace(critical, '-', 'x');

update cfgitems set mindmg=7, maxdmg=18, critical='19x3', requiredstrength=20 where tag ='greataxe';
update cfgitems set mindmg=7, maxdmg=18, critical='19x3', requiredstrength=20 where tag ='lockjaw';
update cfgitems set mindmg=7, maxdmg=16, critical='16x3', requiredstrength=19 where tag ='halberd';
update cfgitems set mindmg=7, maxdmg=16, critical='20x2', requiredstrength=19 where tag ='fauchard';
update cfgitems set mindmg=7, maxdmg=17, critical='20x2', requiredstrength=20 where tag ='thepunisher';
update cfgitems set mindmg=7, maxdmg=15, critical='18x3', requiredstrength=18 where tag ='durlindana';
update cfgitems set mindmg=5, maxdmg=12, critical='17x3', requiredstrength=14 where tag ='mace';
update cfgitems set mindmg=5, maxdmg=12, critical='19x2', requiredstrength=14 where tag ='warhammer';
update cfgitems set mindmg=5, maxdmg=12, critical='19x2', requiredstrength=14 where tag ='morningstar';
update cfgitems set mindmg=7, maxdmg=15, critical='19x3', requiredstrength=18 where tag ='blacksword';
update cfgitems set mindmg=3, maxdmg=10, critical=NULL, requiredstrength=11 where tag ='longsword';
update cfgitems set mindmg=3, maxdmg=10, critical=NULL, requiredstrength=11 where tag ='scimitar';
update cfgitems set mindmg=3, maxdmg=8, critical=NULL, requiredstrength=9 where tag ='short_sword';
update cfgitems set mindmg=2, maxdmg=4, critical=NULL, requiredstrength=5 where tag ='knife';









