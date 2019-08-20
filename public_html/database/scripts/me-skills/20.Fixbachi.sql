update cfgkingdomprojects set tag = 'castle_1' where tag = 'castle';
update cfgkingdomprojects set tag = 'court_1' where tag = 'court';
update cfgkingdomprojects set tag = 'market_1' where tag = 'market';
update cfgkingdomprojects set tag = 'tavern_1' where tag = 'tavern';
update cfgkingdomprojects set tag = 'harbor_1' where tag = 'harbor';
update cfgitems set part ='right_hand' where tag = 'woodensword';
update cfgitems set parentcategory = 'tools', category='tool', subcategory='tool', part ='right_hand' where tag = 'writingkit';
