/*
 * file: reset_database.sql
 * desc: pulisce tutti i dati dal database meno quelli di configurazione
*/

truncate table electronicpayments;
truncate table admin_bannedips;
truncate table admin_messages;
truncate table battles;
truncate table battle_participants;
delete from ar_character_stats;
delete from character_relationships;
delete from character_titles;
delete from character_stats;		
delete from characters;
delete from ar_characters;
truncate table gamewinners;
truncate table groups;
truncate table group_characters;
truncate table jobs;
truncate table marketingaccounts;
truncate table marketingadmins;
truncate table marketingcampaigns;
truncate table marketingdailystatistics;
truncate table marketingpayouts;
truncate table marketingrequestpayout;
truncate table marketingretention;
truncate table marketingstatistics;
truncate table marketingtracking;
truncate table stats_items;
truncate table structure_grants;
truncate table structure_lentitems;
delete from structure_stats;
truncate table trace_sales;
truncate table users_sharedips;
truncate wardrobe_approvalrequests;
truncate table character_actions;
truncate table character_events;
truncate table character_roles;
truncate table character_sentences;
truncate table character_stats;
truncate table character_positions;
truncate table structure_events ;
truncate table stats_globals;
truncate table kingdomprojects;
truncate table roles_users;

-- items

truncate table items;
truncate table laws;
truncate table messages;
truncate table regions_announcements;		

-- cancella le strutture non di base

delete from structures where structure_type_id in
( select id from structure_types where subtype = 'owner' ); 
update structures set character_id=null;
delete from structure_options where structure_id not in (select id from structures);

update structure_resources set max = 100, current = 100;

truncate table toplistvotes;
truncate table trace_user_logins;
truncate table trace_coins;
truncate table trace_sinks;
truncate table trace_couple_logins;
truncate table character_permanentevents;
truncate table stats_historical;
truncate table boardmessages;
truncate table trace_coinsdist;
truncate table trace_userip_conflicts;
delete from users;
truncate table user_referrals;
delete from character_premiumbonuses;
delete from ipaddress_proxies;
delete from kingdom_forum_topics;
delete from kingdom_forum_boards;



