update cfgitems set mindmg=7, maxdmg=17, critical='19x3', 
wearfactor=512, requiredstrength=20 where tag ='thepunisher';
update cfgitems set mindmg=7, maxdmg=16, critical='19x2', 
wearfactor=128, requiredstrength=20 where tag ='lockjaw';
update cfgitems set mindmg=7, maxdmg=15, critical='19x2', 
wearfactor=64, requiredstrength=19 where tag ='durlindana';
update cfgitems set mindmg=7, maxdmg=14, critical='19x3', 
wearfactor=32, requiredstrength=18 where tag ='blacksword';
update cfgitems set mindmg=6, maxdmg=13, critical='20x2', 
wearfactor=1, requiredstrength=17 where tag ='greataxe';
update cfgitems set mindmg=5, maxdmg=13, critical='19x3', 
wearfactor=1, requiredstrength=17 where tag ='halberd';
update cfgitems set mindmg=5, maxdmg=13, critical='19x3', 
wearfactor=1, requiredstrength=17 where tag ='fauchard';
update cfgitems set mindmg=4, maxdmg=12, critical='20x2', 
wearfactor=1, requiredstrength=14 where tag ='mace';
update cfgitems set mindmg=4, maxdmg=12, critical='20x2', 
wearfactor=1, requiredstrength=14 where tag ='warhammer';
update cfgitems set mindmg=4, maxdmg=12, critical='20x2', 
wearfactor=1, requiredstrength=14 where tag ='morningstar';

update cfgitems set mindmg=3, maxdmg=10, critical=NULL,
wearfactor=1, requiredstrength=11 where tag ='longsword';
update cfgitems set mindmg=3, maxdmg=10, critical=NULL,
wearfactor=1, requiredstrength=11 where tag ='scimitar';
update cfgitems set mindmg=3, maxdmg=8, critical=NULL,
wearfactor=1, requiredstrength=9 where tag ='short_sword';
update cfgitems set mindmg=2, maxdmg=4, critical=NULL,
wearfactor=1, requiredstrength=5 where tag ='knife';
update cfgitems set mindmg=1, maxdmg=1, critical=NULL,
wearfactor=1, requiredstrength=5 where tag ='wooden sword';

-- Final Armors

update cfgitems set defense = 2, weight=1500, wearfactor=1, requiredstrength=7 where tag ='leather_armor_head';
update cfgitems set defense = 2, weight=680, wearfactor=1, requiredstrength=7 where tag ='leather_armor_boots';
update cfgitems set defense = 2, weight=1361, wearfactor=1, requiredstrength=7 where tag ='leather_armor_legs';
update cfgitems set defense = 2, weight=4082, wearfactor=1, requiredstrength=7 where tag ='leather_armor_body';
update cfgitems set defense = 2, weight=2267, wearfactor=1, requiredstrength=7 where tag ='leather_armor_shield';
update cfgitems set defense = 4, weight=6350, wearfactor=1, requiredstrength=12 where tag ='chainmail_armor_body';
update cfgitems set defense = 4, weight=2721, wearfactor=1, requiredstrength=12 where tag ='chainmail_armor_head';
update cfgitems set defense = 4, weight=6350, wearfactor=1, requiredstrength=12 where tag ='chainmail_armor_legs';
update cfgitems set defense = 4, weight=2721, wearfactor=1, requiredstrength=12 where tag ='chainmail_armor_feet';
update cfgitems set defense = 4, weight=2720, wearfactor=1, requiredstrength=12 where tag ='chainmail_armor_shield';
update cfgitems set defense = 6, weight=4530, wearfactor=1, requiredstrength=17 where tag ='platemail_armor_head';
update cfgitems set defense = 6, weight=2268, wearfactor=1, requiredstrength=17 where tag ='platemail_armor_legs';
update cfgitems set defense = 6, weight=4536, wearfactor=1, requiredstrength=17 where tag ='platemail_armor_feet';
update cfgitems set defense = 6, weight=2268, wearfactor=1, requiredstrength=17 where tag ='platemail_armor_body';
update cfgitems set defense = 6, weight=13607, wearfactor=1, requiredstrength=17 where tag ='platemail_armor_shield';
update cfgitems set defense = 6, weight=6800, wearfactor=1, requiredstrength=17 where tag ='blackset_armor_body';
update cfgitems set defense = 8, weight=11100, wearfactor=32, requiredstrength=18 where tag ='blackset_armor_body';
update cfgitems set defense = 8, weight=1900, wearfactor=32, requiredstrength=18 where tag ='blackset_armor_feet';
update cfgitems set defense = 8, weight=1900, wearfactor=32, requiredstrength=18 where tag ='blackset_armor_head';
update cfgitems set defense = 8, weight=3800, wearfactor=32, requiredstrength=18 where tag ='blackset_armor_legs';
update cfgitems set defense = 8, weight=2300, wearfactor=32, requiredstrength=18 where tag ='blackset_armor_shield';
update cfgitems set defense = 10, weight=7800, wearfactor=64, requiredstrength=19 where tag ='frenchchevalierset_armor_body';
update cfgitems set defense = 10, weight=1320, wearfactor=64, requiredstrength=19 where tag ='frenchchevalierset_armor_feet';
update cfgitems set defense = 10, weight=1320, wearfactor=64, requiredstrength=19 where tag ='frenchchevalierset_armor_head';
update cfgitems set defense = 10, weight=2700, wearfactor=64, requiredstrength=19 where tag ='frenchchevalierset_armor_legs';
update cfgitems set defense = 10, weight=1620, wearfactor=64, requiredstrength=19 where tag ='frenchchevalierset_armor_shield';
update cfgitems set defense = 11, weight=15000, wearfactor=128, requiredstrength=20 where tag ='mountainset_armor_body';
update cfgitems set defense = 11, weight=2500, wearfactor=128, requiredstrength=20 where tag ='mountainset_armor_feet';
update cfgitems set defense = 11, weight=2500, wearfactor=128, requiredstrength=20 where tag ='mountainset_armor_head';
update cfgitems set defense = 11, weight=5200, wearfactor=128, requiredstrength=20 where tag ='mountainset_armor_legs';
update cfgitems set defense = 12, weight=10800, wearfactor=512, requiredstrength=20 where tag ='punisher_armor_body';
update cfgitems set defense = 12, weight=1800, wearfactor=512, requiredstrength=20 where tag ='punisher_armor_feet';
update cfgitems set defense = 12, weight=1800, wearfactor=512, requiredstrength=20 where tag ='punisher_armor_helmet';
update cfgitems set defense = 12, weight=3600, wearfactor=512, requiredstrength=20 where tag ='punisher_armor_legs';
update cfgitems set defense = 12, weight=6000, wearfactor=512, requiredstrength=20 where tag ='punisher_armor_shield';
update cfgitems set defense = 6, weight=4530, wearfactor=1, requiredstrength=12 where tag ='wooden_normanshield_1';
