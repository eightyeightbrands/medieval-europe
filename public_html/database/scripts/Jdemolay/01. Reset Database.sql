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
TRUNCATE TABLE character_premiumbonuses;
truncate table character_events;
truncate table character_roles;
truncate table character_sentences;
truncate table character_stats;
truncate table character_positions;
truncate table structure_events ;
truncate table stats_globals;
truncate table kingdomprojects;
truncate table roles_users;
truncate table items;
truncate table laws;
truncate table messages;
truncate table regions_announcements;		

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
truncate table user_referrals;

truncate table ar_character_events;
truncate table ar_items;
truncate table sessions;
truncate table ar_messages;
truncate table ipaddress_proxies;
truncate table ar_structures;
-- reset diplomacy
update diplomacy_relations set type = 'neutral', timestamp = unix_timestamp();
-- reset resources

SELECT * FROM kingdom_forum_replies;

delete from kingdom_forum_replies;
delete from kingdom_forum_topics;
delete from kingdom_forum_boards;

-- reset taxes
update taxes set value = 5;
-- reset region_taxes
update region_taxes set hostile=5, neutral=5, friendly=5,allied=5;
-- reset kingdom_taxes
update kingdom_taxes set hostile=5, neutral=5, friendly=5,allied=5,citizen=5;
truncate table kingdom_titles;
-- structure_options è usata?
truncate table crypto_orders;
truncate table ar_character_titles;
truncate table church_dogmabonuses;
truncate table user_languages;
delete from users;
drop table kingdoms_backup;