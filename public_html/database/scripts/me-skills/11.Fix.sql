update cfgitems set parentcategory = 'scrolls' 
where tag in (
'waxseal', 'paper_piece'
);

update cfgitems set 
parentcategory = 'structuretool', 
category = 'structuretool' where tag 
in 
('furnace', 'oven', 'anvil', 'potter_wheel', 'woodenbarrel', 'distiller', 'grapepress' );