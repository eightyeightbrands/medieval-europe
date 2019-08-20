truncate table suggestions;

select * from suggestions;

insert into suggestions ( id, character_id, quote, sponsoredamount, status, discussionurl, title, body, created, oldsuggestion_id  )
( select null, coalesce((select id from characters where name = spare1),1), spare3, spare6, status, spare7, title, message, created, id from boardmessages where category = 'suggestion');

update suggestions set status = 'funded' where sponsoredamount >= quote and quote > 0;
update suggestions set status = 'fundable' where quote < sponsoredamount and quote > 0;
update suggestions set status = 'new' where status = 'published';

select * from character_stats where name like '%suggestion%';

select id, oldsuggestion_id from suggestions;

update character_stats set param1 = '1' where param1='634' and name ='suggestionsponsorship';
update character_stats set param1 = '2' where param1='638' and name ='suggestionsponsorship';
update character_stats set param1 = '3' where param1='639' and name ='suggestionsponsorship';
update character_stats set param1 = '4' where param1='640' and name ='suggestionsponsorship';
update character_stats set param1 = '5' where param1='641' and name ='suggestionsponsorship';
update character_stats set param1 = '6' where param1='642' and name ='suggestionsponsorship';
update character_stats set param1 = '7' where param1='643' and name ='suggestionsponsorship';
update character_stats set param1 = '8' where param1='645' and name ='suggestionsponsorship';
update character_stats set param1 = '9' where param1='774' and name ='suggestionsponsorship';
update character_stats set param1 = '10' where param1='826' and name ='suggestionsponsorship';
update character_stats set param1 = '11' where param1='827' and name ='suggestionsponsorship';
update character_stats set param1 = '12' where param1='1546' and name ='suggestionsponsorship';
update character_stats set param1 = '13' where param1='1549' and name ='suggestionsponsorship';
update character_stats set param1 = '14' where param1='1550' and name ='suggestionsponsorship';
update character_stats set param1 = '15' where param1='1551' and name ='suggestionsponsorship';
update character_stats set param1 = '16' where param1='1775' and name ='suggestionsponsorship';
update character_stats set param1 = '17' where param1='2017' and name ='suggestionsponsorship';
update character_stats set param1 = '18' where param1='12990' and name ='suggestionsponsorship';
update character_stats set param1 = '19' where param1='14325' and name ='suggestionsponsorship';
update character_stats set param1 = '20' where param1='19633' and name ='suggestionsponsorship';
update character_stats set param1 = '21' where param1='19966' and name ='suggestionsponsorship';

delete from character_stats where name = 'suggestionsponsorship' and param1 not in (select id from suggestions);

delete from character_stats where name = 'votedsuggestion';