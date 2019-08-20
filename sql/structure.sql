-- Generation Time: Aug 20, 2019 at 01:39 PM
-- Server version: 5.7.27
-- PHP Version: 7.2.7

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `medieval_prod`
--
CREATE DATABASE IF NOT EXISTS `medieval_prod` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE `medieval_prod`;

-- --------------------------------------------------------

--
-- Table structure for table `admin_bannedips`
--

CREATE TABLE `admin_bannedips` (
  `id` int(11) NOT NULL,
  `ipaddress` varchar(15) NOT NULL,
  `status` varchar(25) NOT NULL,
  `notes` varchar(512) DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created` varchar(25) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `admin_messages`
--

CREATE TABLE `admin_messages` (
  `id` int(10) UNSIGNED NOT NULL,
  `summary` varchar(255) DEFAULT NULL,
  `message` text NOT NULL,
  `read` int(11) NOT NULL,
  `timestamp` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `ar_characters`
--

CREATE TABLE `ar_characters` (
  `id` int(11) NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL,
  `tutor_id` int(11) DEFAULT NULL,
  `region_id` int(11) NOT NULL,
  `position_id` int(11) NOT NULL,
  `name` char(40) NOT NULL,
  `sex` char(1) NOT NULL,
  `str` smallint(6) NOT NULL,
  `intel` smallint(6) NOT NULL,
  `dex` smallint(6) NOT NULL,
  `cost` smallint(6) NOT NULL,
  `car` smallint(6) NOT NULL,
  `glut` smallint(6) NOT NULL,
  `energy` smallint(6) NOT NULL,
  `health` smallint(6) NOT NULL,
  `description` varchar(2048) DEFAULT NULL,
  `eventlastread` int(11) NOT NULL DEFAULT '0',
  `lastactiontime` int(11) NOT NULL,
  `status` varchar(50) DEFAULT NULL,
  `slogan` varchar(45) DEFAULT NULL,
  `notes` varchar(2048) DEFAULT NULL,
  `history` text,
  `score` smallint(6) DEFAULT '0',
  `rpforumregistered` tinyint(1) NOT NULL DEFAULT '0',
  `birthdate` int(11) DEFAULT NULL,
  `deathdate` int(11) DEFAULT NULL,
  `birth_region_id` smallint(6) DEFAULT NULL,
  `death_region_id` smallint(6) DEFAULT NULL,
  `church_id` tinyint(3) UNSIGNED NOT NULL,
  `signature` varchar(2048) DEFAULT NULL,
  `type` varchar(3) NOT NULL DEFAULT 'pc',
  `npctag` varchar(25) DEFAULT NULL,
  `silvercoins` decimal(10,2) DEFAULT '0.00',
  `doubloons` mediumint(9) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `ar_character_events`
--

CREATE TABLE `ar_character_events` (
  `id` int(11) NOT NULL,
  `character_id` int(11) NOT NULL,
  `type` varchar(25) NOT NULL,
  `description` varchar(512) NOT NULL,
  `timestamp` int(11) NOT NULL,
  `eventclass` varchar(25) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `ar_character_premiumbonuses`
--

CREATE TABLE `ar_character_premiumbonuses` (
  `id` int(11) NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `targetuser_id` int(11) UNSIGNED DEFAULT NULL,
  `targetcharname` varchar(50) DEFAULT NULL,
  `character_id` int(11) NOT NULL,
  `structure_id` int(11) DEFAULT NULL,
  `cfgpremiumbonus_id` tinyint(4) NOT NULL,
  `cfgpremiumbonus_cut_id` smallint(6) DEFAULT NULL,
  `starttime` int(11) NOT NULL,
  `endtime` int(11) NOT NULL,
  `doubloons` int(11) NOT NULL,
  `param1` varchar(256) DEFAULT NULL,
  `param2` varchar(256) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `ar_character_stats`
--

CREATE TABLE `ar_character_stats` (
  `id` int(11) NOT NULL DEFAULT '0',
  `character_id` int(11) NOT NULL,
  `name` varchar(25) CHARACTER SET utf8 NOT NULL,
  `value` int(11) NOT NULL DEFAULT '0',
  `param1` varchar(25) CHARACTER SET utf8 NOT NULL,
  `param2` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `stat1` varchar(255) DEFAULT NULL,
  `stat2` varchar(255) DEFAULT NULL,
  `spare1` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `spare2` varchar(255) DEFAULT NULL,
  `spare3` varchar(255) DEFAULT NULL,
  `spare4` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `ar_character_titles`
--

CREATE TABLE `ar_character_titles` (
  `id` int(11) NOT NULL,
  `cfgachievement_id` int(11) DEFAULT NULL,
  `character_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(45) NOT NULL,
  `stars` tinyint(4) NOT NULL,
  `timestamp` timestamp NULL DEFAULT NULL,
  `position` tinyint(3) UNSIGNED NOT NULL,
  `current` varchar(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `ar_items`
--

CREATE TABLE `ar_items` (
  `id` mediumint(9) NOT NULL,
  `cfgitem_id` mediumint(9) NOT NULL,
  `character_id` int(11) DEFAULT NULL,
  `region_id` int(11) DEFAULT NULL,
  `structure_id` int(11) DEFAULT NULL,
  `npc_id` int(11) DEFAULT NULL,
  `seller_id` int(11) DEFAULT NULL,
  `lend_id` int(50) DEFAULT NULL,
  `status` varchar(25) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'New',
  `recipient_id` int(11) DEFAULT NULL,
  `equipped` varchar(45) COLLATE utf8_unicode_ci DEFAULT 'unequipped',
  `price` decimal(10,2) UNSIGNED DEFAULT NULL,
  `mindmg` smallint(5) UNSIGNED DEFAULT NULL,
  `maxdmg` smallint(5) UNSIGNED DEFAULT NULL,
  `persistent` tinyint(1) NOT NULL DEFAULT '0',
  `defense` tinyint(4) DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT '1',
  `quality` decimal(5,2) NOT NULL DEFAULT '100.00',
  `salepostdate` int(11) DEFAULT NULL,
  `tax_citizen` tinyint(4) DEFAULT NULL,
  `tax_neutral` tinyint(4) DEFAULT NULL,
  `tax_friendly` tinyint(4) DEFAULT NULL,
  `tax_allied` tinyint(4) DEFAULT NULL,
  `sendorder` varchar(25) COLLATE utf8_unicode_ci DEFAULT NULL,
  `color` varchar(11) COLLATE utf8_unicode_ci DEFAULT NULL,
  `hexcolor` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `locked` tinyint(1) NOT NULL DEFAULT '0',
  `param1` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `param2` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `param3` text COLLATE utf8_unicode_ci,
  `createddate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ar_messages`
--

CREATE TABLE `ar_messages` (
  `id` int(11) NOT NULL,
  `char_id` int(11) NOT NULL,
  `date` int(11) NOT NULL,
  `fromchar_id` int(11) NOT NULL,
  `tochar_id` int(11) NOT NULL,
  `type` varchar(50) NOT NULL,
  `subject` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `body` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `isread` tinyint(1) NOT NULL DEFAULT '0',
  `archived` char(1) NOT NULL DEFAULT 'N',
  `param1` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `ar_structures`
--

CREATE TABLE `ar_structures` (
  `id` int(11) NOT NULL,
  `parent_structure_id` int(11) DEFAULT NULL,
  `structure_type_id` int(11) NOT NULL,
  `region_id` int(11) NOT NULL,
  `character_id` int(11) DEFAULT NULL,
  `state` decimal(10,2) NOT NULL DEFAULT '100.00',
  `status` varchar(50) DEFAULT NULL,
  `start` int(11) DEFAULT NULL,
  `end` int(11) DEFAULT NULL,
  `history` text,
  `storage` int(11) DEFAULT NULL,
  `locked` tinyint(1) DEFAULT '0',
  `name` varchar(128) DEFAULT NULL,
  `hourlywage` varchar(128) DEFAULT NULL,
  `attribute1` varchar(255) DEFAULT NULL,
  `attribute2` varchar(255) DEFAULT NULL,
  `attribute3` varchar(255) DEFAULT NULL,
  `attribute4` varchar(255) DEFAULT NULL,
  `attribute5` varchar(255) DEFAULT NULL,
  `attribute6` varchar(255) DEFAULT NULL,
  `size` varchar(10) DEFAULT NULL,
  `description` varchar(1024) DEFAULT NULL,
  `image` varbinary(50) DEFAULT NULL,
  `message` varchar(1024) DEFAULT NULL,
  `silvercoins` decimal(10,2) DEFAULT NULL,
  `doubloons` mediumint(9) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `battles`
--

CREATE TABLE `battles` (
  `id` int(11) NOT NULL,
  `kingdomwar_id` int(11) DEFAULT NULL,
  `source_character_id` int(11) DEFAULT NULL,
  `dest_character_id` int(11) DEFAULT NULL,
  `source_region_id` int(11) DEFAULT NULL,
  `dest_region_id` int(11) DEFAULT NULL,
  `battlefield_id` int(11) DEFAULT NULL,
  `type` varchar(25) CHARACTER SET latin1 DEFAULT NULL,
  `status` varchar(25) CHARACTER SET latin1 DEFAULT NULL,
  `timestamp` int(11) DEFAULT NULL,
  `attacker_wins` int(11) DEFAULT NULL,
  `defender_wins` int(11) DEFAULT NULL,
  `kingcandidate` int(11) DEFAULT NULL,
  `raidedcoins` int(11) DEFAULT NULL,
  `maxattackers` smallint(6) DEFAULT NULL,
  `param1` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `battle_participants`
--

CREATE TABLE `battle_participants` (
  `id` int(11) NOT NULL,
  `battle_id` int(11) NOT NULL,
  `character_id` int(11) NOT NULL,
  `faction` varchar(25) NOT NULL,
  `status` varchar(45) NOT NULL,
  `categorization` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `battle_reports`
--

CREATE TABLE `battle_reports` (
  `battle_id` int(11) DEFAULT NULL,
  `report1` mediumtext,
  `report2` mediumtext,
  `report3` mediumtext,
  `report4` mediumtext,
  `report5` mediumtext
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `blockedemailproviders`
--

CREATE TABLE `blockedemailproviders` (
  `id` int(11) NOT NULL,
  `domain` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `boardmessages`
--

CREATE TABLE `boardmessages` (
  `id` int(10) NOT NULL,
  `kingdom_id` int(10) NOT NULL,
  `character_id` int(10) NOT NULL,
  `category` varchar(50) NOT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'published',
  `validity` int(11) NOT NULL,
  `starpoints` smallint(6) NOT NULL DEFAULT '0',
  `visibility` varchar(50) NOT NULL DEFAULT 'kingdom',
  `title` varchar(125) DEFAULT NULL,
  `updated` int(11) DEFAULT NULL,
  `created` int(11) NOT NULL,
  `message` mediumtext NOT NULL,
  `messageclass` varchar(50) DEFAULT NULL,
  `readtimes` int(10) DEFAULT NULL,
  `spare1` varchar(255) DEFAULT NULL,
  `spare2` varchar(255) DEFAULT NULL,
  `spare3` varchar(255) DEFAULT NULL,
  `spare4` varchar(255) DEFAULT NULL,
  `spare5` varchar(255) DEFAULT NULL,
  `spare6` varchar(255) DEFAULT NULL,
  `spare7` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `cfgachievements`
--

CREATE TABLE `cfgachievements` (
  `id` int(11) NOT NULL,
  `type` varchar(50) NOT NULL,
  `tag` varchar(50) NOT NULL,
  `level` int(11) NOT NULL,
  `score` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `cfgbadwords`
--

CREATE TABLE `cfgbadwords` (
  `id` int(11) NOT NULL,
  `word` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `cfgcountrycodes`
--

CREATE TABLE `cfgcountrycodes` (
  `id` int(11) NOT NULL,
  `country` varchar(100) CHARACTER SET latin1 NOT NULL,
  `code` varchar(2) CHARACTER SET latin1 NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `cfgdogmabonuses`
--

CREATE TABLE `cfgdogmabonuses` (
  `id` int(11) NOT NULL,
  `dogma` varchar(25) NOT NULL,
  `level` tinyint(4) NOT NULL,
  `bonus` varchar(60) DEFAULT NULL,
  `malus_church_id` int(11) DEFAULT NULL,
  `url` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `cfggameevents`
--

CREATE TABLE `cfggameevents` (
  `id` int(11) NOT NULL,
  `tag` varchar(50) NOT NULL DEFAULT '0',
  `name` varchar(50) NOT NULL,
  `description` varchar(255) NOT NULL,
  `rulesurl` varchar(512) NOT NULL,
  `subscriptionstartdate` int(11) NOT NULL,
  `subscriptionenddate` int(11) NOT NULL,
  `silvercoins` smallint(6) NOT NULL,
  `doubloons` smallint(6) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `cfgitems`
--

CREATE TABLE `cfgitems` (
  `id` int(9) NOT NULL,
  `church_id` tinyint(4) DEFAULT NULL,
  `tag` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `parenttag` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `requiredstrength` tinyint(4) DEFAULT NULL,
  `price` int(11) NOT NULL,
  `parentcategory` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `category` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  `subcategory` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `mindmg` smallint(5) UNSIGNED DEFAULT NULL,
  `maxdmg` smallint(5) UNSIGNED DEFAULT NULL,
  `bluntperc` tinyint(4) DEFAULT NULL,
  `cutperc` tinyint(4) DEFAULT NULL,
  `reach` tinyint(4) DEFAULT NULL,
  `armorpenetration` tinyint(4) DEFAULT NULL,
  `critical` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `text` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `defense` decimal(10,2) DEFAULT NULL,
  `part` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `coverage` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `size` varchar(1) COLLATE utf8_unicode_ci DEFAULT NULL,
  `weight` int(6) NOT NULL,
  `droppable` tinyint(1) NOT NULL DEFAULT '1',
  `takeable` tinyint(1) NOT NULL,
  `stealable` tinyint(1) NOT NULL DEFAULT '1',
  `marketsellable` tinyint(1) NOT NULL,
  `structuresellable` tinyint(1) NOT NULL,
  `canbesent` tinyint(1) NOT NULL DEFAULT '1',
  `destroyontrash` tinyint(4) NOT NULL DEFAULT '0',
  `trashable` tinyint(4) NOT NULL DEFAULT '1',
  `canbedonated` tinyint(4) NOT NULL DEFAULT '1',
  `taxable` tinyint(1) NOT NULL DEFAULT '1',
  `confiscable` tinyint(1) NOT NULL DEFAULT '1',
  `colorable` tinyint(4) NOT NULL DEFAULT '0',
  `crafting_slot` tinyint(4) NOT NULL DEFAULT '100',
  `craftingenabled` tinyint(1) NOT NULL DEFAULT '1',
  `car_modifier` int(11) NOT NULL DEFAULT '0',
  `linked_role` varchar(25) COLLATE utf8_unicode_ci DEFAULT NULL,
  `spare1` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `spare2` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `spare3` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `spare4` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `spare5` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `spare6` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `spare7` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `wearfactor` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cfgitem_dependencies`
--

CREATE TABLE `cfgitem_dependencies` (
  `id` int(11) NOT NULL,
  `cfgitem_id` int(11) NOT NULL,
  `type` varchar(25) NOT NULL,
  `source_cfgitem_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `cfgkingdomprojects`
--

CREATE TABLE `cfgkingdomprojects` (
  `id` int(11) NOT NULL,
  `tag` varchar(25) NOT NULL,
  `type` varchar(25) DEFAULT NULL,
  `name` varchar(255) CHARACTER SET latin1 NOT NULL,
  `description` varchar(255) CHARACTER SET latin1 NOT NULL,
  `owner` varchar(25) NOT NULL,
  `required_hours` smallint(6) NOT NULL,
  `required_structure_type_id` int(11) DEFAULT NULL,
  `produced_structure_type_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `cfgkingdomproject_dependencies`
--

CREATE TABLE `cfgkingdomproject_dependencies` (
  `id` int(11) NOT NULL,
  `cfgkingdomproject_id` int(11) NOT NULL,
  `cfgitem_id` int(11) NOT NULL,
  `quantity` smallint(6) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `cfgmodules`
--

CREATE TABLE `cfgmodules` (
  `id` int(11) NOT NULL,
  `module` varchar(50) NOT NULL,
  `status` varchar(50) NOT NULL,
  `description` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `cfgpremiumbonuses`
--

CREATE TABLE `cfgpremiumbonuses` (
  `id` tinyint(4) NOT NULL,
  `name` varchar(50) DEFAULT NULL,
  `cutunit` varchar(25) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `cfgpremiumbonuses_cuts`
--

CREATE TABLE `cfgpremiumbonuses_cuts` (
  `id` smallint(6) NOT NULL,
  `cfgpremiumbonus_id` tinyint(4) NOT NULL,
  `cut` int(11) NOT NULL,
  `price` smallint(6) NOT NULL,
  `enddate` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `cfgpremiumbonuses_promos`
--

CREATE TABLE `cfgpremiumbonuses_promos` (
  `id` smallint(6) NOT NULL,
  `cfgpremiumbonus_id` tinyint(4) NOT NULL,
  `name` varchar(50) NOT NULL,
  `discount` tinyint(4) NOT NULL,
  `startdate` datetime NOT NULL,
  `enddate` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `cfgquests`
--

CREATE TABLE `cfgquests` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `path` varchar(50) NOT NULL,
  `dependencies` varchar(50) DEFAULT NULL,
  `author_id` int(11) DEFAULT NULL,
  `final` varchar(1) NOT NULL,
  `sortorder` smallint(6) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `cfgquest_events`
--

CREATE TABLE `cfgquest_events` (
  `id` int(11) NOT NULL,
  `cfgquest_id` int(11) DEFAULT NULL,
  `event` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `cfgtoplists`
--

CREATE TABLE `cfgtoplists` (
  `id` int(11) NOT NULL,
  `username` varchar(25) NOT NULL,
  `name` varchar(50) NOT NULL,
  `hasrewardsystem` tinyint(1) NOT NULL DEFAULT '1',
  `url` varchar(255) NOT NULL,
  `refererurl` varchar(255) NOT NULL,
  `target` smallint(6) NOT NULL,
  `reward` varchar(25) DEFAULT NULL,
  `status` varchar(25) NOT NULL,
  `showtoplist` char(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `cfgwardrobeitems`
--

CREATE TABLE `cfgwardrobeitems` (
  `id` int(11) NOT NULL,
  `tag` varchar(50) DEFAULT NULL,
  `name` varchar(50) DEFAULT NULL,
  `author` varchar(50) DEFAULT NULL,
  `previewfilepath` varchar(255) NOT NULL,
  `cfgpremiumbonus_id` tinyint(4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `characters`
--

CREATE TABLE `characters` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `tutor_id` int(11) DEFAULT NULL,
  `region_id` int(11) NOT NULL,
  `position_id` int(11) NOT NULL,
  `name` char(40) NOT NULL,
  `sex` char(1) CHARACTER SET latin1 NOT NULL,
  `str` smallint(6) NOT NULL,
  `intel` smallint(6) NOT NULL,
  `dex` smallint(6) NOT NULL,
  `cost` smallint(6) NOT NULL,
  `car` smallint(6) NOT NULL,
  `glut` smallint(6) NOT NULL,
  `energy` smallint(6) NOT NULL,
  `health` smallint(6) NOT NULL,
  `description` varchar(2048) DEFAULT NULL,
  `eventlastread` int(11) NOT NULL DEFAULT '0',
  `lastactiontime` int(11) DEFAULT NULL,
  `status` varchar(50) CHARACTER SET latin1 DEFAULT NULL,
  `slogan` varchar(45) DEFAULT NULL,
  `notes` varchar(2048) CHARACTER SET latin1 DEFAULT NULL,
  `history` text,
  `score` smallint(6) DEFAULT '0',
  `rpforumregistered` tinyint(1) NOT NULL DEFAULT '0',
  `birthdate` int(11) DEFAULT NULL,
  `deathdate` int(11) DEFAULT NULL,
  `birth_region_id` smallint(6) DEFAULT NULL,
  `death_region_id` smallint(6) DEFAULT NULL,
  `church_id` tinyint(3) UNSIGNED DEFAULT NULL,
  `signature` varchar(2048) DEFAULT NULL,
  `type` varchar(3) NOT NULL DEFAULT 'pc',
  `npctag` varchar(25) DEFAULT NULL,
  `silvercoins` decimal(10,2) DEFAULT NULL,
  `doubloons` mediumint(9) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `character_actions`
--

CREATE TABLE `character_actions` (
  `id` int(11) NOT NULL,
  `character_id` int(11) NOT NULL,
  `structure_id` int(11) DEFAULT NULL,
  `keylock` varchar(50) DEFAULT NULL,
  `action` varchar(25) NOT NULL,
  `blocking_flag` tinyint(1) NOT NULL DEFAULT '1',
  `cycle_flag` tinyint(1) NOT NULL DEFAULT '0',
  `status` varchar(25) NOT NULL,
  `starttime` int(11) NOT NULL,
  `endtime` int(11) NOT NULL,
  `param1` varchar(255) DEFAULT NULL,
  `param2` varchar(255) DEFAULT NULL,
  `param3` varchar(255) DEFAULT NULL,
  `param4` varchar(255) DEFAULT NULL,
  `param5` text
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `character_events`
--

CREATE TABLE `character_events` (
  `id` int(11) NOT NULL,
  `character_id` int(11) NOT NULL,
  `type` varchar(25) NOT NULL,
  `description` varchar(512) NOT NULL,
  `timestamp` int(11) NOT NULL,
  `eventclass` varchar(25) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `character_permanentevents`
--

CREATE TABLE `character_permanentevents` (
  `id` int(11) NOT NULL,
  `character_id` int(11) NOT NULL,
  `type` varchar(25) NOT NULL,
  `description` varchar(512) NOT NULL,
  `timestamp` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `character_premiumbonuses`
--

CREATE TABLE `character_premiumbonuses` (
  `id` int(11) NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `targetuser_id` int(11) UNSIGNED DEFAULT NULL,
  `targetcharname` varchar(50) DEFAULT NULL,
  `character_id` int(11) NOT NULL,
  `structure_id` int(11) DEFAULT NULL,
  `cfgpremiumbonus_id` tinyint(4) NOT NULL,
  `cfgpremiumbonus_cut_id` smallint(6) DEFAULT NULL,
  `starttime` int(11) NOT NULL,
  `endtime` int(11) NOT NULL,
  `doubloons` int(11) NOT NULL,
  `param1` varchar(256) DEFAULT NULL,
  `param2` varchar(256) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `character_relationships`
--

CREATE TABLE `character_relationships` (
  `id` int(11) NOT NULL,
  `sourcechar_id` int(11) NOT NULL,
  `targetchar_id` int(11) NOT NULL,
  `type` varchar(50) NOT NULL,
  `timestamp` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `character_roles`
--

CREATE TABLE `character_roles` (
  `id` int(11) NOT NULL,
  `character_id` int(11) NOT NULL,
  `tag` varchar(25) NOT NULL,
  `begin` int(11) DEFAULT NULL,
  `end` int(11) DEFAULT NULL,
  `kingdom_id` int(11) NOT NULL,
  `region_id` int(11) NOT NULL,
  `structure_id` int(11) DEFAULT NULL,
  `current` tinyint(1) NOT NULL DEFAULT '0',
  `church_id` tinyint(3) UNSIGNED DEFAULT NULL,
  `place` varchar(50) DEFAULT NULL,
  `gdr` tinyint(4) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `character_sentences`
--

CREATE TABLE `character_sentences` (
  `id` int(11) NOT NULL,
  `character_id` int(11) DEFAULT NULL,
  `issued_by` int(11) DEFAULT NULL,
  `issuedate` int(11) DEFAULT NULL,
  `text` varchar(255) DEFAULT NULL,
  `status` varchar(25) DEFAULT NULL,
  `structure_id` int(11) DEFAULT NULL,
  `imprisonment_start` int(11) DEFAULT NULL,
  `imprisonment_end` int(11) DEFAULT NULL,
  `arrested_by` int(11) DEFAULT NULL,
  `free_reason` varchar(250) DEFAULT NULL,
  `cancelreason` varchar(255) DEFAULT NULL,
  `trialurl` varchar(255) DEFAULT NULL,
  `imprisonment_hours_given` tinyint(3) UNSIGNED DEFAULT NULL,
  `prison_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `character_stats`
--

CREATE TABLE `character_stats` (
  `id` int(11) NOT NULL,
  `character_id` int(11) NOT NULL,
  `name` varchar(25) NOT NULL,
  `value` decimal(30,2) NOT NULL DEFAULT '0.00',
  `param1` varchar(255) NOT NULL,
  `param2` varchar(255) DEFAULT NULL,
  `stat1` varchar(255) CHARACTER SET latin1 DEFAULT NULL,
  `stat2` varchar(255) DEFAULT NULL,
  `spare1` varchar(255) DEFAULT NULL,
  `spare2` varchar(255) DEFAULT NULL,
  `spare3` varchar(255) DEFAULT NULL,
  `spare4` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `character_titles`
--

CREATE TABLE `character_titles` (
  `id` int(11) NOT NULL,
  `cfgachievement_id` int(11) NOT NULL,
  `character_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(45) NOT NULL,
  `stars` tinyint(4) NOT NULL,
  `timestamp` timestamp NULL DEFAULT NULL,
  `position` tinyint(3) UNSIGNED NOT NULL,
  `current` varchar(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `churches`
--

CREATE TABLE `churches` (
  `id` tinyint(3) UNSIGNED NOT NULL,
  `religion_id` tinyint(3) UNSIGNED NOT NULL,
  `name` varchar(45) NOT NULL,
  `holytexturl` varchar(128) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `church_dogmabonuses`
--

CREATE TABLE `church_dogmabonuses` (
  `id` int(10) UNSIGNED NOT NULL,
  `church_id` tinyint(3) UNSIGNED NOT NULL,
  `cfgdogmabonus_id` int(11) NOT NULL,
  `timestamp` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `crowdflower_conversions`
--

CREATE TABLE `crowdflower_conversions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `status` varchar(50) NOT NULL,
  `amount` int(11) DEFAULT NULL,
  `adjusted_amount` int(11) DEFAULT NULL,
  `job_title` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `crypto_orders`
--

CREATE TABLE `crypto_orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `character_id` int(11) NOT NULL,
  `product` varchar(50) NOT NULL,
  `status` varchar(50) NOT NULL,
  `timestamp` datetime NOT NULL,
  `currency` varchar(3) NOT NULL,
  `quantity` mediumint(8) UNSIGNED NOT NULL,
  `usdchange` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `crypto_payments`
--

CREATE TABLE `crypto_payments` (
  `paymentID` int(11) UNSIGNED NOT NULL,
  `boxID` int(11) UNSIGNED NOT NULL DEFAULT '0',
  `boxType` enum('paymentbox','captchabox') NOT NULL,
  `orderID` varchar(50) NOT NULL DEFAULT '',
  `userID` varchar(50) NOT NULL DEFAULT '',
  `countryID` varchar(3) NOT NULL DEFAULT '',
  `coinLabel` varchar(6) NOT NULL DEFAULT '',
  `amount` double(20,8) NOT NULL DEFAULT '0.00000000',
  `amountUSD` double(20,8) NOT NULL DEFAULT '0.00000000',
  `unrecognised` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  `addr` varchar(34) NOT NULL DEFAULT '',
  `txID` char(64) NOT NULL DEFAULT '',
  `txDate` datetime DEFAULT NULL,
  `txConfirmed` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  `txCheckDate` datetime DEFAULT NULL,
  `processed` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  `processedDate` datetime DEFAULT NULL,
  `recordCreated` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Table structure for table `diplomacy_proposals`
--

CREATE TABLE `diplomacy_proposals` (
  `id` int(11) NOT NULL,
  `diplomacy_relation_id` int(11) NOT NULL,
  `sourcekingdom_id` int(11) NOT NULL,
  `targetkingdom_id` int(11) NOT NULL,
  `diplomacyproposal` varchar(50) NOT NULL,
  `status` varchar(50) NOT NULL,
  `timestamp` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `diplomacy_relations`
--

CREATE TABLE `diplomacy_relations` (
  `id` int(10) NOT NULL,
  `sourcekingdom_id` int(10) DEFAULT NULL,
  `targetkingdom_id` int(10) DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  `description` text,
  `timestamp` int(11) DEFAULT NULL,
  `signedby` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `electronicpayments`
--

CREATE TABLE `electronicpayments` (
  `id` int(11) NOT NULL,
  `txn_id` varchar(255) NOT NULL,
  `user_id` int(11) NOT NULL,
  `camp_id` smallint(6) DEFAULT NULL,
  `item_name` varchar(255) NOT NULL,
  `quantity` int(11) NOT NULL,
  `status` varchar(25) NOT NULL,
  `grossamount` varchar(255) DEFAULT NULL,
  `netamount` varchar(255) DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `transaction_date` datetime DEFAULT NULL,
  `spare1` varchar(255) DEFAULT NULL,
  `spare2` varchar(255) DEFAULT NULL,
  `currency` varchar(3) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `events_randomextractions`
--

CREATE TABLE `events_randomextractions` (
  `id` int(11) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'available',
  `character_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `facebook_inviterequests`
--

CREATE TABLE `facebook_inviterequests` (
  `id` int(11) NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `request_id` varchar(50) NOT NULL,
  `friend_id` varchar(50) NOT NULL,
  `status` varchar(50) NOT NULL,
  `timestamp` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `fundedprojects`
--

CREATE TABLE `fundedprojects` (
  `id` int(11) NOT NULL,
  `project_name` varchar(50) NOT NULL,
  `project_link` varchar(150) NOT NULL,
  `reached_amount` int(11) NOT NULL,
  `total_amount` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `gameevent_subscriptions`
--

CREATE TABLE `gameevent_subscriptions` (
  `id` int(11) NOT NULL,
  `cfggameevent_id` int(11) NOT NULL,
  `character_id` int(11) NOT NULL,
  `kingdom_id` int(11) NOT NULL,
  `doubloons` smallint(6) NOT NULL DEFAULT '0',
  `silvercoins` smallint(6) NOT NULL DEFAULT '0',
  `timestamp` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `games`
--

CREATE TABLE `games` (
  `id` tinyint(3) UNSIGNED NOT NULL,
  `name` varchar(45) NOT NULL,
  `param1` varchar(225) NOT NULL,
  `lastbettime` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `gamewinners`
--

CREATE TABLE `gamewinners` (
  `id` int(10) NOT NULL,
  `game` varchar(25) CHARACTER SET latin1 DEFAULT '0',
  `winner` varchar(255) CHARACTER SET latin1 DEFAULT '0',
  `amount` smallint(6) DEFAULT '0',
  `region_id` int(11) DEFAULT '0',
  `windate` int(11) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `groups`
--

CREATE TABLE `groups` (
  `id` int(11) NOT NULL,
  `character_id` int(11) NOT NULL,
  `name` varchar(60) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `type` varchar(20) CHARACTER SET latin1 NOT NULL,
  `classification` varchar(20) CHARACTER SET latin1 NOT NULL,
  `secret` tinyint(1) NOT NULL,
  `date` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `group_characters`
--

CREATE TABLE `group_characters` (
  `id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  `character_id` int(11) NOT NULL,
  `joined` tinyint(1) NOT NULL,
  `date` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `ipaddress_proxies`
--

CREATE TABLE `ipaddress_proxies` (
  `id` int(11) NOT NULL,
  `ipaddress` varchar(15) NOT NULL,
  `score` decimal(5,4) NOT NULL,
  `timestamp` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `ipaddress_proxy_calls`
--

CREATE TABLE `ipaddress_proxy_calls` (
  `id` int(11) NOT NULL,
  `calls` int(11) NOT NULL,
  `date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `items`
--

CREATE TABLE `items` (
  `id` int(11) UNSIGNED NOT NULL,
  `cfgitem_id` mediumint(9) NOT NULL,
  `character_id` int(11) DEFAULT NULL,
  `region_id` int(11) DEFAULT NULL,
  `structure_id` int(11) DEFAULT NULL,
  `npc_id` int(11) DEFAULT NULL,
  `seller_id` int(11) DEFAULT NULL,
  `lend_id` int(50) DEFAULT NULL,
  `status` varchar(25) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'New',
  `recipient_id` int(11) DEFAULT NULL,
  `equipped` varchar(45) COLLATE utf8_unicode_ci DEFAULT 'unequipped',
  `price` decimal(10,2) UNSIGNED DEFAULT NULL,
  `mindmg` smallint(5) UNSIGNED DEFAULT NULL,
  `maxdmg` smallint(5) UNSIGNED DEFAULT NULL,
  `persistent` tinyint(1) NOT NULL DEFAULT '0',
  `defense` tinyint(4) DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT '1',
  `quality` decimal(5,2) NOT NULL DEFAULT '100.00',
  `salepostdate` int(11) DEFAULT NULL,
  `tax_citizen` tinyint(4) DEFAULT NULL,
  `tax_neutral` tinyint(4) DEFAULT NULL,
  `tax_friendly` tinyint(4) DEFAULT NULL,
  `tax_allied` tinyint(4) DEFAULT NULL,
  `sendorder` varchar(25) COLLATE utf8_unicode_ci DEFAULT NULL,
  `color` varchar(11) COLLATE utf8_unicode_ci DEFAULT NULL,
  `hexcolor` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `locked` tinyint(1) NOT NULL DEFAULT '0',
  `param1` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `param2` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `param3` text COLLATE utf8_unicode_ci,
  `createddate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` int(10) NOT NULL,
  `character_id` int(10) DEFAULT NULL,
  `employer_id` int(10) DEFAULT NULL,
  `status` varchar(25) DEFAULT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `wage` varchar(255) DEFAULT NULL,
  `hourlywage` decimal(10,2) DEFAULT '0.00',
  `boardmessage_id` int(10) DEFAULT NULL,
  `structure_id` int(10) DEFAULT NULL,
  `expiredate` int(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `kingdomprojects`
--

CREATE TABLE `kingdomprojects` (
  `id` int(11) NOT NULL,
  `cfgkingdomproject_id` int(11) NOT NULL,
  `structure_id` int(11) NOT NULL,
  `status` varchar(50) NOT NULL,
  `start` int(11) NOT NULL,
  `end` int(11) DEFAULT NULL,
  `workedhours` smallint(6) NOT NULL DEFAULT '0',
  `hourlywage` tinyint(4) NOT NULL DEFAULT '1',
  `startedby` varchar(255) NOT NULL,
  `region_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `kingdoms`
--

CREATE TABLE `kingdoms` (
  `id` int(11) NOT NULL,
  `name` char(50) NOT NULL,
  `image` varchar(40) NOT NULL,
  `status` varchar(50) NOT NULL,
  `title` varchar(50) NOT NULL,
  `slogan` varchar(255) DEFAULT '',
  `color` varchar(12) NOT NULL,
  `language1` varchar(25) DEFAULT NULL,
  `language2` varchar(25) DEFAULT NULL,
  `lastattacked` int(11) DEFAULT NULL,
  `activityscore` decimal(10,5) DEFAULT NULL,
  `forumurl` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `kingdoms_history`
--

CREATE TABLE `kingdoms_history` (
  `id` int(11) NOT NULL,
  `kingdom_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `image` varchar(50) NOT NULL,
  `begin` int(11) NOT NULL,
  `end` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Stand-in structure for view `kingdoms_v`
-- (See below for the actual view)
--
CREATE TABLE `kingdoms_v` (
`id` int(11)
,`name` char(50)
,`image` varchar(40)
,`status` varchar(50)
,`title` varchar(50)
,`slogan` varchar(255)
,`color` varchar(12)
,`language1` varchar(25)
,`language2` varchar(25)
,`lastattacked` int(11)
,`activityscore` decimal(10,5)
,`forumurl` varchar(255)
);

-- --------------------------------------------------------

--
-- Table structure for table `kingdom_forum_boards`
--

CREATE TABLE `kingdom_forum_boards` (
  `id` int(11) NOT NULL,
  `kingdom_id` int(11) DEFAULT NULL,
  `name` varchar(128) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created` datetime NOT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'new',
  `updated` datetime NOT NULL,
  `author` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `kingdom_forum_replies`
--

CREATE TABLE `kingdom_forum_replies` (
  `id` int(11) NOT NULL,
  `kingdom_forum_topic_id` int(11) DEFAULT NULL,
  `body` text,
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  `author` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `kingdom_forum_topics`
--

CREATE TABLE `kingdom_forum_topics` (
  `id` int(11) NOT NULL,
  `kingdom_forum_board_id` int(11) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'new',
  `body` longtext,
  `sticky` varchar(1) DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  `author` int(11) DEFAULT NULL,
  `views` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `kingdom_nobletitles`
--

CREATE TABLE `kingdom_nobletitles` (
  `id` int(11) NOT NULL,
  `kingdom_id` int(11) NOT NULL,
  `title` varchar(25) NOT NULL,
  `customisedtitle_m` varchar(25) NOT NULL,
  `customisedtitle_f` varchar(25) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `kingdom_taxes`
--

CREATE TABLE `kingdom_taxes` (
  `id` int(10) NOT NULL,
  `kingdom_id` int(10) NOT NULL DEFAULT '0',
  `name` varchar(50) DEFAULT NULL,
  `hostile` tinyint(4) NOT NULL DEFAULT '0',
  `neutral` tinyint(4) NOT NULL DEFAULT '0',
  `friendly` tinyint(4) NOT NULL DEFAULT '0',
  `allied` tinyint(4) NOT NULL DEFAULT '0',
  `citizen` tinyint(4) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `kingdom_titles`
--

CREATE TABLE `kingdom_titles` (
  `id` int(11) NOT NULL,
  `cfgachievement_id` int(11) NOT NULL,
  `kingdom_id` int(11) NOT NULL,
  `stars` tinyint(3) UNSIGNED NOT NULL,
  `timestamp` timestamp NULL DEFAULT NULL,
  `position` tinyint(3) UNSIGNED NOT NULL,
  `current` varchar(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `kingdom_wars`
--

CREATE TABLE `kingdom_wars` (
  `id` int(11) NOT NULL,
  `source_kingdom_id` int(11) NOT NULL,
  `target_kingdom_id` int(11) NOT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'running',
  `start` int(11) NOT NULL,
  `end` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `kingdom_wars_allies`
--

CREATE TABLE `kingdom_wars_allies` (
  `id` int(11) NOT NULL,
  `kingdom_war_id` int(11) NOT NULL,
  `kingdom_id` int(11) NOT NULL,
  `role` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `laws`
--

CREATE TABLE `laws` (
  `id` int(11) NOT NULL,
  `kingdom_id` tinyint(4) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text NOT NULL,
  `createdby` varchar(250) DEFAULT NULL,
  `signature` varchar(2048) DEFAULT NULL,
  `timestamp` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `marketingaccounts`
--

CREATE TABLE `marketingaccounts` (
  `id` int(11) NOT NULL,
  `domainName` varchar(50) NOT NULL,
  `contactName` varchar(25) NOT NULL,
  `email` varchar(128) NOT NULL,
  `paypal` varchar(128) NOT NULL,
  `type` int(11) NOT NULL,
  `password` varchar(500) NOT NULL,
  `lastLogin` int(10) NOT NULL,
  `lastIP` varchar(48) NOT NULL,
  `randomString` text NOT NULL,
  `registerIP` varchar(48) NOT NULL,
  `registerTime` int(10) NOT NULL,
  `registerDate` date NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `marketingcampaigns`
--

CREATE TABLE `marketingcampaigns` (
  `id` int(11) NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `campName` varchar(50) NOT NULL,
  `revShare` int(11) NOT NULL,
  `CPLCost` float NOT NULL,
  `landURL` varchar(255) NOT NULL,
  `registerURL` varchar(255) NOT NULL,
  `timeb` int(10) NOT NULL,
  `dateb` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `marketingdailystatistics`
--

CREATE TABLE `marketingdailystatistics` (
  `dailyStatisticsID` int(11) NOT NULL,
  `campaignID` int(11) NOT NULL,
  `visitors` int(11) NOT NULL,
  `registers` int(11) NOT NULL,
  `active` int(11) NOT NULL,
  `supporters` int(11) NOT NULL,
  `totalIncome` float NOT NULL,
  `totalTransactions` int(11) NOT NULL,
  `timeb` int(10) NOT NULL,
  `dateb` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `marketingretention`
--

CREATE TABLE `marketingretention` (
  `id` int(11) NOT NULL,
  `timestamp` date DEFAULT NULL,
  `day0` smallint(6) DEFAULT '0',
  `day1` smallint(6) DEFAULT '0',
  `day7` smallint(6) DEFAULT '0',
  `day14` smallint(6) DEFAULT '0',
  `day30` smallint(6) DEFAULT '0',
  `day90` smallint(6) DEFAULT '0',
  `day180` smallint(6) DEFAULT '0',
  `day360` smallint(6) DEFAULT '0',
  `day720` smallint(6) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `marketingstatistics`
--

CREATE TABLE `marketingstatistics` (
  `id` mediumint(9) NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `name` varchar(50) NOT NULL,
  `type` tinyint(4) NOT NULL,
  `campaignID` int(11) NOT NULL,
  `visitors` int(11) NOT NULL,
  `registers` int(11) NOT NULL,
  `activeplayers` int(11) NOT NULL,
  `revenueSharing` int(11) NOT NULL,
  `payRequest` tinyint(4) NOT NULL,
  `payOuts` float NOT NULL,
  `totalIncome` float NOT NULL,
  `totalTransactions` int(11) NOT NULL,
  `totalPayUsers` int(11) NOT NULL,
  `balanceToBePaid` float NOT NULL,
  `timeb` int(10) NOT NULL,
  `dateb` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `marketingtracking`
--

CREATE TABLE `marketingtracking` (
  `campID` int(11) NOT NULL,
  `trackingType` varchar(25) NOT NULL,
  `ip` varchar(48) NOT NULL,
  `userID` bigint(20) DEFAULT NULL,
  `timeb` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `char_id` int(11) NOT NULL,
  `date` int(11) NOT NULL,
  `fromchar_id` int(11) NOT NULL,
  `tochar_id` int(11) NOT NULL,
  `type` varchar(50) NOT NULL,
  `subject` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `body` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `isread` tinyint(1) NOT NULL DEFAULT '0',
  `archived` char(1) NOT NULL DEFAULT 'N',
  `param1` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `regions`
--

CREATE TABLE `regions` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `type` varchar(10) NOT NULL,
  `geography` varchar(15) NOT NULL,
  `clima` varchar(10) NOT NULL,
  `kingdom_id` int(11) NOT NULL,
  `capital` tinyint(1) NOT NULL,
  `updatemap` tinyint(1) NOT NULL DEFAULT '0',
  `coords` varchar(10) NOT NULL,
  `island` tinyint(1) NOT NULL,
  `lastconqueredbynatives` int(11) DEFAULT NULL,
  `canbeconquered` int(11) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `regions_announcements`
--

CREATE TABLE `regions_announcements` (
  `id` int(11) NOT NULL,
  `region_id` int(11) DEFAULT NULL,
  `character_id` int(11) NOT NULL,
  `type` varchar(25) NOT NULL,
  `title` varchar(60) NOT NULL,
  `text` longtext NOT NULL,
  `signature` varchar(255) NOT NULL,
  `timestamp` int(11) NOT NULL,
  `subtype` varchar(45) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `regions_paths`
--

CREATE TABLE `regions_paths` (
  `id` int(11) NOT NULL,
  `region_id` int(11) NOT NULL,
  `destination` int(11) NOT NULL,
  `type` varchar(10) NOT NULL,
  `time` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `regions_paths_fasttracksroutes`
--

CREATE TABLE `regions_paths_fasttracksroutes` (
  `regions_path_id` int(11) NOT NULL,
  `region_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `region_taxes`
--

CREATE TABLE `region_taxes` (
  `id` int(10) NOT NULL,
  `region_id` int(10) NOT NULL DEFAULT '0',
  `name` varchar(50) DEFAULT NULL,
  `param1` varchar(50) DEFAULT NULL,
  `hostile` tinyint(4) NOT NULL DEFAULT '0',
  `neutral` tinyint(4) NOT NULL DEFAULT '0',
  `friendly` tinyint(4) NOT NULL DEFAULT '0',
  `allied` tinyint(4) NOT NULL DEFAULT '0',
  `citizen` tinyint(4) NOT NULL,
  `timestamp` int(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `religions`
--

CREATE TABLE `religions` (
  `id` tinyint(3) UNSIGNED NOT NULL,
  `name` varchar(45) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(11) UNSIGNED NOT NULL,
  `name` varchar(32) NOT NULL,
  `description` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `roles_users`
--

CREATE TABLE `roles_users` (
  `user_id` int(10) UNSIGNED NOT NULL,
  `role_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `session_id` varchar(127) NOT NULL,
  `last_activity` int(10) UNSIGNED NOT NULL,
  `data` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `stats_globals`
--

CREATE TABLE `stats_globals` (
  `id` int(11) NOT NULL,
  `stats_id` int(11) NOT NULL,
  `stats_label` varchar(50) NOT NULL,
  `target` varchar(50) DEFAULT NULL,
  `prevposition` int(11) NOT NULL,
  `position` int(11) NOT NULL,
  `type` varchar(30) NOT NULL,
  `value` varchar(50) NOT NULL,
  `entity` varchar(50) DEFAULT NULL,
  `param1` varchar(255) NOT NULL,
  `param2` varchar(255) NOT NULL,
  `extractiontime` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `stats_historical`
--

CREATE TABLE `stats_historical` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `period` int(11) NOT NULL,
  `kingdom` varchar(255) DEFAULT NULL,
  `region` varchar(255) DEFAULT NULL,
  `kingdom_id` smallint(6) DEFAULT NULL,
  `region_id` smallint(6) DEFAULT NULL,
  `param1` varchar(255) DEFAULT NULL,
  `param2` varchar(255) DEFAULT NULL,
  `param3` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `stats_items`
--

CREATE TABLE `stats_items` (
  `id` int(10) UNSIGNED NOT NULL,
  `cfgitem_id` int(10) UNSIGNED NOT NULL,
  `avg_sold_price` varchar(45) NOT NULL,
  `total` int(10) UNSIGNED NOT NULL,
  `avg_price` varchar(45) NOT NULL,
  `timestamp` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `structures`
--

CREATE TABLE `structures` (
  `id` int(11) NOT NULL,
  `parent_structure_id` int(11) DEFAULT NULL,
  `structure_type_id` int(11) NOT NULL,
  `region_id` int(11) NOT NULL,
  `character_id` int(11) DEFAULT NULL,
  `state` decimal(10,2) NOT NULL DEFAULT '100.00',
  `status` varchar(50) DEFAULT NULL,
  `start` int(11) DEFAULT NULL,
  `end` int(11) DEFAULT NULL,
  `history` text,
  `customstorage` int(11) DEFAULT NULL,
  `locked` tinyint(1) DEFAULT '0',
  `name` varchar(128) DEFAULT NULL,
  `hourlywage` varchar(128) DEFAULT NULL,
  `attribute1` varchar(255) DEFAULT NULL,
  `attribute2` varchar(255) DEFAULT NULL,
  `attribute3` varchar(255) DEFAULT NULL,
  `attribute4` varchar(255) DEFAULT NULL,
  `attribute5` varchar(255) DEFAULT NULL,
  `attribute6` varchar(255) DEFAULT NULL,
  `size` varchar(10) DEFAULT NULL,
  `description` varchar(1024) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `message` varchar(1024) DEFAULT NULL,
  `silvercoins` decimal(10,2) DEFAULT NULL,
  `doubloons` mediumint(9) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Table structure for table `structure_events`
--

CREATE TABLE `structure_events` (
  `id` int(11) NOT NULL,
  `structure_id` int(11) NOT NULL,
  `type` varchar(25) NOT NULL,
  `description` varchar(512) NOT NULL,
  `timestamp` int(11) NOT NULL,
  `eventclass` varchar(25) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `structure_grants`
--

CREATE TABLE `structure_grants` (
  `id` int(10) NOT NULL,
  `structure_id` int(10) DEFAULT NULL,
  `character_id` int(10) DEFAULT NULL,
  `job_id` int(11) DEFAULT NULL,
  `grant` varchar(50) DEFAULT NULL,
  `expiredate` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `structure_lentitems`
--

CREATE TABLE `structure_lentitems` (
  `id` int(10) NOT NULL,
  `structure_id` int(10) NOT NULL,
  `lender` varchar(128) COLLATE utf8_bin NOT NULL,
  `target_id` int(10) NOT NULL,
  `lendtime` int(10) NOT NULL,
  `deliverytime` int(10) DEFAULT NULL,
  `returnedtime` int(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `structure_options`
--

CREATE TABLE `structure_options` (
  `id` int(10) UNSIGNED NOT NULL,
  `structure_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(45) NOT NULL,
  `value` varchar(512) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `structure_resources`
--

CREATE TABLE `structure_resources` (
  `id` int(10) UNSIGNED NOT NULL,
  `structure_id` int(10) UNSIGNED NOT NULL,
  `resource` varchar(55) NOT NULL,
  `max` int(10) UNSIGNED NOT NULL,
  `current` int(10) UNSIGNED NOT NULL,
  `next_recharge` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `structure_stats`
--

CREATE TABLE `structure_stats` (
  `id` int(11) UNSIGNED NOT NULL,
  `structure_id` int(11) UNSIGNED NOT NULL,
  `name` varchar(50) NOT NULL,
  `value` int(45) NOT NULL,
  `searchparam1` varchar(255) DEFAULT NULL,
  `searchparam2` varchar(255) DEFAULT NULL,
  `spare1` varchar(255) DEFAULT NULL,
  `spare2` varchar(255) DEFAULT NULL,
  `spare3` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `structure_types`
--

CREATE TABLE `structure_types` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `parenttype` varchar(50) NOT NULL,
  `supertype` varchar(50) NOT NULL,
  `type` varchar(50) NOT NULL,
  `subtype` varchar(25) NOT NULL,
  `church_id` tinyint(3) UNSIGNED NOT NULL,
  `associated_role_tag` varchar(25) DEFAULT NULL,
  `sortorder` tinyint(4) NOT NULL,
  `associated_role_description` varchar(25) DEFAULT NULL,
  `level` tinyint(4) NOT NULL DEFAULT '1',
  `restlevel` decimal(3,1) DEFAULT NULL,
  `image` varchar(50) NOT NULL,
  `price` int(11) DEFAULT NULL,
  `buyable` tinyint(1) NOT NULL DEFAULT '0',
  `sellable` tinyint(1) NOT NULL DEFAULT '0',
  `attribute1` varchar(255) DEFAULT NULL,
  `attribute2` varchar(255) DEFAULT NULL,
  `attribute3` varchar(255) DEFAULT NULL,
  `attribute4` varchar(255) DEFAULT NULL,
  `attribute5` varchar(255) DEFAULT NULL,
  `attribute6` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `structure_types_cfgitems`
--

CREATE TABLE `structure_types_cfgitems` (
  `id` int(10) NOT NULL,
  `structure_type_id` int(10) NOT NULL DEFAULT '0',
  `cfgitem_id` int(10) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `suggestions`
--

CREATE TABLE `suggestions` (
  `id` int(11) NOT NULL,
  `character_id` int(11) NOT NULL,
  `totalrating` int(11) NOT NULL DEFAULT '0',
  `votes` int(11) NOT NULL DEFAULT '0',
  `averagerating` decimal(3,2) NOT NULL DEFAULT '0.00',
  `quote` int(11) NOT NULL DEFAULT '0',
  `baesianrating` decimal(10,2) DEFAULT '0.00',
  `sponsoredamount` int(11) NOT NULL DEFAULT '0',
  `status` varchar(50) NOT NULL DEFAULT 'new',
  `discussionurl` varchar(255) DEFAULT 'new',
  `reason` varchar(255) DEFAULT 'new',
  `detailsurl` varchar(255) DEFAULT NULL,
  `title` varchar(50) NOT NULL,
  `body` text NOT NULL,
  `created` int(11) DEFAULT NULL,
  `oldsuggestion_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `taxes`
--

CREATE TABLE `taxes` (
  `id` int(11) NOT NULL,
  `tag` varchar(25) NOT NULL,
  `type` varchar(25) NOT NULL,
  `region_id` int(11) DEFAULT NULL,
  `kingdom_id` int(11) DEFAULT NULL,
  `name` varchar(50) NOT NULL,
  `description` varchar(250) NOT NULL,
  `value` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `toplistvotes`
--

CREATE TABLE `toplistvotes` (
  `id` int(11) NOT NULL,
  `character_id` int(11) NOT NULL,
  `cfgtoplist_id` int(11) NOT NULL,
  `vkey` varchar(25) NOT NULL,
  `status` varchar(25) NOT NULL,
  `ip` varchar(15) NOT NULL,
  `urlcalled` varchar(255) NOT NULL,
  `timestamp` int(11) NOT NULL,
  `rewardgiven` tinyint(1) NOT NULL,
  `note` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `trace_coins`
--

CREATE TABLE `trace_coins` (
  `id` int(10) NOT NULL,
  `character_id` int(10) DEFAULT '0',
  `amount` int(10) DEFAULT '0',
  `reason` varchar(25) DEFAULT '0',
  `timestamp` int(11) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `trace_coinsdist`
--

CREATE TABLE `trace_coinsdist` (
  `id` int(10) UNSIGNED NOT NULL,
  `character_id` int(11) UNSIGNED NOT NULL,
  `name` varchar(45) NOT NULL,
  `tag` varchar(45) NOT NULL,
  `amount` int(11) UNSIGNED NOT NULL,
  `timestamp` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `trace_couple_logins`
--

CREATE TABLE `trace_couple_logins` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id_1` int(10) UNSIGNED NOT NULL,
  `user_id_2` int(10) UNSIGNED NOT NULL,
  `score` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `trace_sales`
--

CREATE TABLE `trace_sales` (
  `id` int(11) NOT NULL,
  `cfgitem_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT '0',
  `totalprice` decimal(10,2) NOT NULL DEFAULT '0.00',
  `timestamp` varchar(6) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `trace_sinks`
--

CREATE TABLE `trace_sinks` (
  `id` int(10) UNSIGNED NOT NULL,
  `type` varchar(45) NOT NULL,
  `character_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `reason` varchar(45) NOT NULL,
  `timestamp` datetime DEFAULT NULL,
  `source` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `trace_userip_conflicts`
--

CREATE TABLE `trace_userip_conflicts` (
  `ipaddress` varchar(15) NOT NULL,
  `username_1` varchar(50) NOT NULL,
  `username_2` varchar(50) NOT NULL,
  `counter` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `trace_user_logins`
--

CREATE TABLE `trace_user_logins` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(11) NOT NULL,
  `ipaddress` varchar(15) NOT NULL,
  `logincookie` varchar(50) DEFAULT NULL,
  `logintime` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) UNSIGNED NOT NULL,
  `external_id` varchar(256) DEFAULT NULL,
  `fb_id` varchar(256) DEFAULT NULL,
  `idnet_id` varchar(50) DEFAULT NULL,
  `tutor_id` int(11) UNSIGNED DEFAULT NULL,
  `email` varchar(127) NOT NULL,
  `username` varchar(32) NOT NULL DEFAULT '',
  `password` char(50) NOT NULL,
  `status` varchar(20) NOT NULL,
  `gender` varchar(1) DEFAULT NULL,
  `activationtoken` varchar(25) NOT NULL,
  `birthday` date DEFAULT NULL,
  `receiveigmessagesonemail` varchar(1) NOT NULL DEFAULT 'N',
  `reason` varchar(50) DEFAULT NULL,
  `logins` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `last_login` int(10) UNSIGNED DEFAULT NULL,
  `created` int(10) UNSIGNED NOT NULL,
  `ipaddress` varchar(15) DEFAULT NULL,
  `nationality` varchar(2) DEFAULT '--',
  `language` varchar(25) DEFAULT NULL,
  `multi_status` varchar(25) DEFAULT NULL,
  `multi_note` varchar(512) DEFAULT NULL,
  `doubloons` int(11) NOT NULL DEFAULT '0',
  `gracedate` int(10) UNSIGNED DEFAULT NULL,
  `bandate` int(11) DEFAULT NULL,
  `newsletter` varchar(1) DEFAULT NULL,
  `referrersite` varchar(50) DEFAULT NULL,
  `tutorialmode` varchar(1) DEFAULT NULL,
  `sleepafteraction` varchar(1) NOT NULL DEFAULT 'N',
  `hidemaxstatsbadges` varchar(1) NOT NULL DEFAULT 'Y',
  `disablesleepafteraction` varchar(1) NOT NULL DEFAULT 'N',
  `availableregfunctions` varchar(1) NOT NULL DEFAULT 'N',
  `maxglut` tinyint(4) NOT NULL DEFAULT '50',
  `camp_id` int(11) DEFAULT NULL,
  `ex_data` varchar(50) DEFAULT NULL,
  `domainname` varchar(50) DEFAULT NULL,
  `supporter` tinyint(4) DEFAULT '0',
  `showlanguages` varchar(1) NOT NULL DEFAULT 'N',
  `proxywarningdate` int(11) DEFAULT NULL,
  `proxycheckdisabled` varchar(1) NOT NULL DEFAULT 'N'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `users_sharedips`
--

CREATE TABLE `users_sharedips` (
  `id` int(10) UNSIGNED NOT NULL,
  `username_1` varchar(45) NOT NULL,
  `username_2` varchar(45) NOT NULL,
  `notes` varchar(45) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `user_languages`
--

CREATE TABLE `user_languages` (
  `id` int(11) NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `position` tinyint(4) NOT NULL,
  `language` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `user_referrals`
--

CREATE TABLE `user_referrals` (
  `id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) NOT NULL,
  `referred_id` int(11) NOT NULL,
  `coins` decimal(10,2) NOT NULL,
  `doubloons` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `user_tokens`
--

CREATE TABLE `user_tokens` (
  `id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `user_agent` varchar(40) NOT NULL,
  `token` varchar(32) NOT NULL,
  `created` int(10) UNSIGNED NOT NULL,
  `expires` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `wardrobe_approvalrequests`
--

CREATE TABLE `wardrobe_approvalrequests` (
  `id` int(10) NOT NULL,
  `character_id` int(10) NOT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'new',
  `reason` varchar(512) DEFAULT NULL,
  `created` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure for view `kingdoms_v`
--
DROP TABLE IF EXISTS `kingdoms_v`;

CREATE ALGORITHM=UNDEFINED DEFINER=`medievaleurope`@`localhost` SQL SECURITY DEFINER VIEW `kingdoms_v`  AS  select `kingdoms`.`id` AS `id`,`kingdoms`.`name` AS `name`,`kingdoms`.`image` AS `image`,`kingdoms`.`status` AS `status`,`kingdoms`.`title` AS `title`,`kingdoms`.`slogan` AS `slogan`,`kingdoms`.`color` AS `color`,`kingdoms`.`language1` AS `language1`,`kingdoms`.`language2` AS `language2`,`kingdoms`.`lastattacked` AS `lastattacked`,`kingdoms`.`activityscore` AS `activityscore`,`kingdoms`.`forumurl` AS `forumurl` from `kingdoms` where (`kingdoms`.`status` <> 'deleted') ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_bannedips`
--
ALTER TABLE `admin_bannedips`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ipaddress` (`ipaddress`),
  ADD KEY `admin_bannedips_ipaddress_ix` (`ipaddress`);

--
-- Indexes for table `admin_messages`
--
ALTER TABLE `admin_messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ar_characters`
--
ALTER TABLE `ar_characters`
  ADD KEY `ar_character_id` (`id`),
  ADD KEY `ar_charname` (`name`);

--
-- Indexes for table `ar_character_events`
--
ALTER TABLE `ar_character_events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ar_events_character_id` (`character_id`);

--
-- Indexes for table `ar_character_premiumbonuses`
--
ALTER TABLE `ar_character_premiumbonuses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cb_character_id` (`character_id`),
  ADD KEY `cfgpremiumbonus_id` (`cfgpremiumbonus_id`),
  ADD KEY `cfgpremiumbonus_cut_id` (`cfgpremiumbonus_cut_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `targetuser_id` (`targetuser_id`);

--
-- Indexes for table `ar_character_stats`
--
ALTER TABLE `ar_character_stats`
  ADD PRIMARY KEY (`id`),
  ADD KEY `Index 2` (`character_id`);

--
-- Indexes for table `ar_character_titles`
--
ALTER TABLE `ar_character_titles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ar_character_titles_unique` (`character_id`,`stars`,`name`),
  ADD KEY `ar_ct_character_id` (`character_id`);

--
-- Indexes for table `ar_items`
--
ALTER TABLE `ar_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ar_items_character_id` (`character_id`),
  ADD KEY `ar_items_structure_id` (`structure_id`);

--
-- Indexes for table `ar_messages`
--
ALTER TABLE `ar_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ar_messages_fromchar_id` (`fromchar_id`),
  ADD KEY `ar_messages_tochar_id` (`tochar_id`),
  ADD KEY `ar_messages_char_id` (`char_id`);

--
-- Indexes for table `ar_structures`
--
ALTER TABLE `ar_structures`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ar_structures_node_id` (`region_id`),
  ADD KEY `ar_structures_character_id` (`character_id`);

--
-- Indexes for table `battles`
--
ALTER TABLE `battles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK_battles_dest_regions` (`dest_region_id`);

--
-- Indexes for table `battle_participants`
--
ALTER TABLE `battle_participants`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `battle_reports`
--
ALTER TABLE `battle_reports`
  ADD KEY `Indice 1` (`battle_id`);

--
-- Indexes for table `blockedemailproviders`
--
ALTER TABLE `blockedemailproviders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `boardmessages`
--
ALTER TABLE `boardmessages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `Index 2` (`character_id`),
  ADD KEY `Index 3` (`kingdom_id`),
  ADD KEY `boardmessage_category` (`category`);

--
-- Indexes for table `cfgachievements`
--
ALTER TABLE `cfgachievements`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cfgbadwords`
--
ALTER TABLE `cfgbadwords`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cfgcountrycodes`
--
ALTER TABLE `cfgcountrycodes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cfgdogmabonuses`
--
ALTER TABLE `cfgdogmabonuses`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cfggameevents`
--
ALTER TABLE `cfggameevents`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cfgitems`
--
ALTER TABLE `cfgitems`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cfgitems_tag_unq` (`tag`);

--
-- Indexes for table `cfgitem_dependencies`
--
ALTER TABLE `cfgitem_dependencies`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UNIQ_CFGD` (`cfgitem_id`,`source_cfgitem_id`),
  ADD KEY `CID_SOURCE_CFGITEM_ID` (`source_cfgitem_id`);

--
-- Indexes for table `cfgkingdomprojects`
--
ALTER TABLE `cfgkingdomprojects`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tag` (`tag`);

--
-- Indexes for table `cfgkingdomproject_dependencies`
--
ALTER TABLE `cfgkingdomproject_dependencies`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `Index 3` (`cfgitem_id`,`cfgkingdomproject_id`),
  ADD KEY `ckd_cfk_id` (`cfgkingdomproject_id`);

--
-- Indexes for table `cfgmodules`
--
ALTER TABLE `cfgmodules`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cfgpremiumbonuses`
--
ALTER TABLE `cfgpremiumbonuses`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cfgpremiumbonuses_cuts`
--
ALTER TABLE `cfgpremiumbonuses_cuts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cfgpremiumbonus_id` (`cfgpremiumbonus_id`);

--
-- Indexes for table `cfgpremiumbonuses_promos`
--
ALTER TABLE `cfgpremiumbonuses_promos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK_cfgpremiumbonuses_promos_cfgpremiumbonuses` (`cfgpremiumbonus_id`);

--
-- Indexes for table `cfgquests`
--
ALTER TABLE `cfgquests`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cfgquest_events`
--
ALTER TABLE `cfgquest_events`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cfgquest_event_unq` (`cfgquest_id`,`event`);

--
-- Indexes for table `cfgtoplists`
--
ALTER TABLE `cfgtoplists`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cfgwardrobeitems`
--
ALTER TABLE `cfgwardrobeitems`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `CFGWARDROBEITEMS_U` (`tag`),
  ADD KEY `cfgpremiumbonus_id` (`cfgpremiumbonus_id`);

--
-- Indexes for table `characters`
--
ALTER TABLE `characters`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `characters_name_unq` (`name`),
  ADD KEY `character_userid` (`user_id`),
  ADD KEY `Index 4` (`region_id`),
  ADD KEY `character_churchid` (`church_id`);

--
-- Indexes for table `character_actions`
--
ALTER TABLE `character_actions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ca_character_id` (`character_id`),
  ADD KEY `idx_keylock` (`keylock`);

--
-- Indexes for table `character_events`
--
ALTER TABLE `character_events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `EVENTS_CHARACTER_ID` (`character_id`);

--
-- Indexes for table `character_permanentevents`
--
ALTER TABLE `character_permanentevents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `EVENTS_CHARACTER_ID` (`character_id`);

--
-- Indexes for table `character_premiumbonuses`
--
ALTER TABLE `character_premiumbonuses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cb_character_id` (`character_id`),
  ADD KEY `cfgpremiumbonus_id` (`cfgpremiumbonus_id`),
  ADD KEY `cfgpremiumbonus_cut_id` (`cfgpremiumbonus_cut_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `targetuser_id` (`targetuser_id`);

--
-- Indexes for table `character_relationships`
--
ALTER TABLE `character_relationships`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `character_roles`
--
ALTER TABLE `character_roles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cr_character_id` (`character_id`);

--
-- Indexes for table `character_sentences`
--
ALTER TABLE `character_sentences`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `character_stats`
--
ALTER TABLE `character_stats`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cs_character_id` (`character_id`);

--
-- Indexes for table `character_titles`
--
ALTER TABLE `character_titles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique` (`character_id`,`stars`,`name`),
  ADD KEY `ct_character_id` (`character_id`),
  ADD KEY `FK_character_titles_cfgachievements` (`cfgachievement_id`);

--
-- Indexes for table `churches`
--
ALTER TABLE `churches`
  ADD PRIMARY KEY (`id`),
  ADD KEY `church_religionid` (`religion_id`);

--
-- Indexes for table `church_dogmabonuses`
--
ALTER TABLE `church_dogmabonuses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UQ_church_dogmabonuses` (`church_id`,`cfgdogmabonus_id`),
  ADD KEY `FK_church_dogmabonuses_cfgdogmabonuses` (`cfgdogmabonus_id`);

--
-- Indexes for table `crowdflower_conversions`
--
ALTER TABLE `crowdflower_conversions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `crypto_orders`
--
ALTER TABLE `crypto_orders`
  ADD KEY `Indice 1` (`id`);

--
-- Indexes for table `crypto_payments`
--
ALTER TABLE `crypto_payments`
  ADD PRIMARY KEY (`paymentID`),
  ADD KEY `boxID` (`boxID`),
  ADD KEY `boxType` (`boxType`),
  ADD KEY `userID` (`userID`),
  ADD KEY `countryID` (`countryID`),
  ADD KEY `orderID` (`orderID`),
  ADD KEY `amount` (`amount`),
  ADD KEY `amountUSD` (`amountUSD`),
  ADD KEY `coinLabel` (`coinLabel`),
  ADD KEY `unrecognised` (`unrecognised`),
  ADD KEY `addr` (`addr`),
  ADD KEY `txID` (`txID`),
  ADD KEY `txDate` (`txDate`),
  ADD KEY `txConfirmed` (`txConfirmed`),
  ADD KEY `txCheckDate` (`txCheckDate`),
  ADD KEY `processed` (`processed`),
  ADD KEY `processedDate` (`processedDate`),
  ADD KEY `recordCreated` (`recordCreated`),
  ADD KEY `key1` (`boxID`,`orderID`),
  ADD KEY `key2` (`boxID`,`orderID`,`userID`),
  ADD KEY `key3` (`boxID`,`orderID`,`userID`,`txID`);

--
-- Indexes for table `diplomacy_proposals`
--
ALTER TABLE `diplomacy_proposals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK__kingdoms_source` (`sourcekingdom_id`),
  ADD KEY `FK__kingdoms_target` (`targetkingdom_id`);

--
-- Indexes for table `diplomacy_relations`
--
ALTER TABLE `diplomacy_relations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `diplomacy_FK1` (`sourcekingdom_id`),
  ADD KEY `diplomacy_FK2` (`targetkingdom_id`),
  ADD KEY `diplomacy_FK3` (`sourcekingdom_id`);

--
-- Indexes for table `electronicpayments`
--
ALTER TABLE `electronicpayments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `U_ELECTRONICPAYMENTS` (`txn_id`,`item_name`),
  ADD KEY `IX_CAMPID` (`camp_id`);

--
-- Indexes for table `events_randomextractions`
--
ALTER TABLE `events_randomextractions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `facebook_inviterequests`
--
ALTER TABLE `facebook_inviterequests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK_facebook_inviterequests_users` (`user_id`);

--
-- Indexes for table `fundedprojects`
--
ALTER TABLE `fundedprojects`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `gameevent_subscriptions`
--
ALTER TABLE `gameevent_subscriptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK__cfggameevents` (`cfggameevent_id`);

--
-- Indexes for table `games`
--
ALTER TABLE `games`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `gamewinners`
--
ALTER TABLE `gamewinners`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `groups`
--
ALTER TABLE `groups`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `group_characters`
--
ALTER TABLE `group_characters`
  ADD PRIMARY KEY (`id`),
  ADD KEY `gc_character_id` (`character_id`),
  ADD KEY `gc_group_id` (`group_id`);

--
-- Indexes for table `ipaddress_proxies`
--
ALTER TABLE `ipaddress_proxies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ipaddress_index` (`ipaddress`);

--
-- Indexes for table `ipaddress_proxy_calls`
--
ALTER TABLE `ipaddress_proxy_calls`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `items`
--
ALTER TABLE `items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ITEMS_CHARACTERID` (`character_id`),
  ADD KEY `items_structure_id` (`structure_id`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kingdomprojects`
--
ALTER TABLE `kingdomprojects`
  ADD PRIMARY KEY (`id`),
  ADD KEY `kp_structure_id` (`structure_id`),
  ADD KEY `FK_kingdomprojects_regions` (`region_id`);

--
-- Indexes for table `kingdoms`
--
ALTER TABLE `kingdoms`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kingdoms_history`
--
ALTER TABLE `kingdoms_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK_kingdoms_history_kingdoms` (`kingdom_id`);

--
-- Indexes for table `kingdom_forum_boards`
--
ALTER TABLE `kingdom_forum_boards`
  ADD PRIMARY KEY (`id`),
  ADD KEY `kingdom_id` (`kingdom_id`);

--
-- Indexes for table `kingdom_forum_replies`
--
ALTER TABLE `kingdom_forum_replies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `kingdom_forum_topic_id` (`kingdom_forum_topic_id`);

--
-- Indexes for table `kingdom_forum_topics`
--
ALTER TABLE `kingdom_forum_topics`
  ADD PRIMARY KEY (`id`),
  ADD KEY `kingdom_forum_topics_ibfk_1` (`kingdom_forum_board_id`);

--
-- Indexes for table `kingdom_nobletitles`
--
ALTER TABLE `kingdom_nobletitles`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kingdom_taxes`
--
ALTER TABLE `kingdom_taxes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK__kingdoms` (`kingdom_id`);

--
-- Indexes for table `kingdom_titles`
--
ALTER TABLE `kingdom_titles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kt_unique` (`kingdom_id`,`cfgachievement_id`),
  ADD KEY `kt_kingdom_id` (`kingdom_id`),
  ADD KEY `FK_kingdom_titles_cfgachievements` (`cfgachievement_id`);

--
-- Indexes for table `kingdom_wars`
--
ALTER TABLE `kingdom_wars`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK_kingdom_wars_kingdoms` (`source_kingdom_id`),
  ADD KEY `FK_kingdom_wars_kingdoms_2` (`target_kingdom_id`);

--
-- Indexes for table `kingdom_wars_allies`
--
ALTER TABLE `kingdom_wars_allies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK_kingdom_war_id` (`kingdom_war_id`),
  ADD KEY `FK_kingdoms_id` (`kingdom_id`);

--
-- Indexes for table `laws`
--
ALTER TABLE `laws`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `marketingaccounts`
--
ALTER TABLE `marketingaccounts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `domainName` (`domainName`);

--
-- Indexes for table `marketingcampaigns`
--
ALTER TABLE `marketingcampaigns`
  ADD PRIMARY KEY (`id`),
  ADD KEY `campID` (`id`,`landURL`,`registerURL`),
  ADD KEY `FK_marketingcampaigns_users` (`user_id`);

--
-- Indexes for table `marketingdailystatistics`
--
ALTER TABLE `marketingdailystatistics`
  ADD PRIMARY KEY (`dailyStatisticsID`),
  ADD KEY `FK_marketingdailystatistics_marketingcampaigns` (`campaignID`);

--
-- Indexes for table `marketingretention`
--
ALTER TABLE `marketingretention`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `marketingstatistics`
--
ALTER TABLE `marketingstatistics`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK_marketingstatistics_users` (`user_id`),
  ADD KEY `FK_marketingstatistics_marketingcampaigns` (`campaignID`);

--
-- Indexes for table `marketingtracking`
--
ALTER TABLE `marketingtracking`
  ADD PRIMARY KEY (`trackingType`,`campID`,`ip`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `MESSAGES_FROMCHAR_ID` (`fromchar_id`),
  ADD KEY `MESSAGES_TOCHAR_ID` (`tochar_id`),
  ADD KEY `messages_char_id` (`char_id`);

--
-- Indexes for table `regions`
--
ALTER TABLE `regions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `regions_announcements`
--
ALTER TABLE `regions_announcements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `na_node_id` (`region_id`);

--
-- Indexes for table `regions_paths`
--
ALTER TABLE `regions_paths`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `regions_paths_fasttracksroutes`
--
ALTER TABLE `regions_paths_fasttracksroutes`
  ADD PRIMARY KEY (`region_id`,`regions_path_id`),
  ADD KEY `FK__fasttrackroutes_regions_paths` (`regions_path_id`);

--
-- Indexes for table `region_taxes`
--
ALTER TABLE `region_taxes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK__regions` (`region_id`);

--
-- Indexes for table `religions`
--
ALTER TABLE `religions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_name` (`name`);

--
-- Indexes for table `roles_users`
--
ALTER TABLE `roles_users`
  ADD PRIMARY KEY (`user_id`,`role_id`),
  ADD KEY `fk_role_id` (`role_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`session_id`);

--
-- Indexes for table `stats_globals`
--
ALTER TABLE `stats_globals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `stats_statsid` (`stats_id`);

--
-- Indexes for table `stats_historical`
--
ALTER TABLE `stats_historical`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `stats_items`
--
ALTER TABLE `stats_items`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `structures`
--
ALTER TABLE `structures`
  ADD PRIMARY KEY (`id`),
  ADD KEY `STRUCTURES_NODE_ID` (`region_id`),
  ADD KEY `STRUCTURES_CHARACTER_ID` (`character_id`),
  ADD KEY `FK_structures_structure_types` (`structure_type_id`);

--
-- Indexes for table `structure_events`
--
ALTER TABLE `structure_events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `EVENTS_STRUCTURE_ID` (`structure_id`);

--
-- Indexes for table `structure_grants`
--
ALTER TABLE `structure_grants`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `structure_lentitems`
--
ALTER TABLE `structure_lentitems`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `structure_options`
--
ALTER TABLE `structure_options`
  ADD PRIMARY KEY (`id`),
  ADD KEY `Index_2` (`structure_id`);

--
-- Indexes for table `structure_resources`
--
ALTER TABLE `structure_resources`
  ADD PRIMARY KEY (`id`),
  ADD KEY `Index_2` (`structure_id`);

--
-- Indexes for table `structure_stats`
--
ALTER TABLE `structure_stats`
  ADD PRIMARY KEY (`id`),
  ADD KEY `Index_2` (`structure_id`);

--
-- Indexes for table `structure_types`
--
ALTER TABLE `structure_types`
  ADD PRIMARY KEY (`id`),
  ADD KEY `st_type` (`type`);

--
-- Indexes for table `structure_types_cfgitems`
--
ALTER TABLE `structure_types_cfgitems`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `U_structure_types_structure_cfgitem` (`cfgitem_id`,`structure_type_id`),
  ADD KEY `FK_structure_types_cfgitems_structure_types` (`structure_type_id`);

--
-- Indexes for table `suggestions`
--
ALTER TABLE `suggestions`
  ADD KEY `Indice 1` (`id`);

--
-- Indexes for table `taxes`
--
ALTER TABLE `taxes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `t_kingdom_id` (`kingdom_id`),
  ADD KEY `t_node_id` (`region_id`);

--
-- Indexes for table `toplistvotes`
--
ALTER TABLE `toplistvotes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `toplistvotes_character_id` (`character_id`),
  ADD KEY `toplistvotes_cfgtoplist_id` (`cfgtoplist_id`),
  ADD KEY `Index 4` (`vkey`);

--
-- Indexes for table `trace_coins`
--
ALTER TABLE `trace_coins`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `trace_coinsdist`
--
ALTER TABLE `trace_coinsdist`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `trace_couple_logins`
--
ALTER TABLE `trace_couple_logins`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `trace_sales`
--
ALTER TABLE `trace_sales`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `trace_sinks`
--
ALTER TABLE `trace_sinks`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `trace_userip_conflicts`
--
ALTER TABLE `trace_userip_conflicts`
  ADD PRIMARY KEY (`username_1`,`username_2`,`ipaddress`);

--
-- Indexes for table `trace_user_logins`
--
ALTER TABLE `trace_user_logins`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tul_user_id` (`user_id`),
  ADD KEY `tul_ipaddress` (`ipaddress`),
  ADD KEY `tul_logincookie` (`logincookie`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_username` (`username`),
  ADD UNIQUE KEY `uniq_email` (`email`),
  ADD KEY `u_ipaddress` (`ipaddress`);

--
-- Indexes for table `users_sharedips`
--
ALTER TABLE `users_sharedips`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user_languages`
--
ALTER TABLE `user_languages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK__users` (`user_id`);

--
-- Indexes for table `user_referrals`
--
ALTER TABLE `user_referrals`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user_tokens`
--
ALTER TABLE `user_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_token` (`token`),
  ADD KEY `fk_user_id` (`user_id`);

--
-- Indexes for table `wardrobe_approvalrequests`
--
ALTER TABLE `wardrobe_approvalrequests`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_bannedips`
--
ALTER TABLE `admin_bannedips`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `admin_messages`
--
ALTER TABLE `admin_messages`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ar_character_events`
--
ALTER TABLE `ar_character_events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ar_character_titles`
--
ALTER TABLE `ar_character_titles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ar_items`
--
ALTER TABLE `ar_items`
  MODIFY `id` mediumint(9) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ar_messages`
--
ALTER TABLE `ar_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ar_structures`
--
ALTER TABLE `ar_structures`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `battles`
--
ALTER TABLE `battles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `battle_participants`
--
ALTER TABLE `battle_participants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `blockedemailproviders`
--
ALTER TABLE `blockedemailproviders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `boardmessages`
--
ALTER TABLE `boardmessages`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cfgachievements`
--
ALTER TABLE `cfgachievements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cfgbadwords`
--
ALTER TABLE `cfgbadwords`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cfgcountrycodes`
--
ALTER TABLE `cfgcountrycodes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cfgdogmabonuses`
--
ALTER TABLE `cfgdogmabonuses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cfggameevents`
--
ALTER TABLE `cfggameevents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cfgitems`
--
ALTER TABLE `cfgitems`
  MODIFY `id` int(9) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cfgitem_dependencies`
--
ALTER TABLE `cfgitem_dependencies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cfgkingdomprojects`
--
ALTER TABLE `cfgkingdomprojects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cfgkingdomproject_dependencies`
--
ALTER TABLE `cfgkingdomproject_dependencies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cfgmodules`
--
ALTER TABLE `cfgmodules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cfgpremiumbonuses`
--
ALTER TABLE `cfgpremiumbonuses`
  MODIFY `id` tinyint(4) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cfgpremiumbonuses_cuts`
--
ALTER TABLE `cfgpremiumbonuses_cuts`
  MODIFY `id` smallint(6) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cfgpremiumbonuses_promos`
--
ALTER TABLE `cfgpremiumbonuses_promos`
  MODIFY `id` smallint(6) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cfgquests`
--
ALTER TABLE `cfgquests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cfgquest_events`
--
ALTER TABLE `cfgquest_events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cfgtoplists`
--
ALTER TABLE `cfgtoplists`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cfgwardrobeitems`
--
ALTER TABLE `cfgwardrobeitems`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `characters`
--
ALTER TABLE `characters`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `character_actions`
--
ALTER TABLE `character_actions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `character_events`
--
ALTER TABLE `character_events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `character_permanentevents`
--
ALTER TABLE `character_permanentevents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `character_premiumbonuses`
--
ALTER TABLE `character_premiumbonuses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `character_relationships`
--
ALTER TABLE `character_relationships`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `character_roles`
--
ALTER TABLE `character_roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `character_sentences`
--
ALTER TABLE `character_sentences`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `character_stats`
--
ALTER TABLE `character_stats`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `character_titles`
--
ALTER TABLE `character_titles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `churches`
--
ALTER TABLE `churches`
  MODIFY `id` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `church_dogmabonuses`
--
ALTER TABLE `church_dogmabonuses`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `crowdflower_conversions`
--
ALTER TABLE `crowdflower_conversions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `crypto_orders`
--
ALTER TABLE `crypto_orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `crypto_payments`
--
ALTER TABLE `crypto_payments`
  MODIFY `paymentID` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `diplomacy_proposals`
--
ALTER TABLE `diplomacy_proposals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `diplomacy_relations`
--
ALTER TABLE `diplomacy_relations`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `electronicpayments`
--
ALTER TABLE `electronicpayments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `facebook_inviterequests`
--
ALTER TABLE `facebook_inviterequests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `fundedprojects`
--
ALTER TABLE `fundedprojects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `gameevent_subscriptions`
--
ALTER TABLE `gameevent_subscriptions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `games`
--
ALTER TABLE `games`
  MODIFY `id` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `gamewinners`
--
ALTER TABLE `gamewinners`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `groups`
--
ALTER TABLE `groups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `group_characters`
--
ALTER TABLE `group_characters`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ipaddress_proxies`
--
ALTER TABLE `ipaddress_proxies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ipaddress_proxy_calls`
--
ALTER TABLE `ipaddress_proxy_calls`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `items`
--
ALTER TABLE `items`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kingdomprojects`
--
ALTER TABLE `kingdomprojects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kingdoms`
--
ALTER TABLE `kingdoms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kingdoms_history`
--
ALTER TABLE `kingdoms_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kingdom_forum_boards`
--
ALTER TABLE `kingdom_forum_boards`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kingdom_forum_replies`
--
ALTER TABLE `kingdom_forum_replies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kingdom_forum_topics`
--
ALTER TABLE `kingdom_forum_topics`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kingdom_nobletitles`
--
ALTER TABLE `kingdom_nobletitles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kingdom_taxes`
--
ALTER TABLE `kingdom_taxes`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kingdom_titles`
--
ALTER TABLE `kingdom_titles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kingdom_wars`
--
ALTER TABLE `kingdom_wars`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kingdom_wars_allies`
--
ALTER TABLE `kingdom_wars_allies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `laws`
--
ALTER TABLE `laws`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `marketingaccounts`
--
ALTER TABLE `marketingaccounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `marketingcampaigns`
--
ALTER TABLE `marketingcampaigns`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `marketingdailystatistics`
--
ALTER TABLE `marketingdailystatistics`
  MODIFY `dailyStatisticsID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `marketingretention`
--
ALTER TABLE `marketingretention`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `marketingstatistics`
--
ALTER TABLE `marketingstatistics`
  MODIFY `id` mediumint(9) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `regions`
--
ALTER TABLE `regions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `regions_announcements`
--
ALTER TABLE `regions_announcements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `regions_paths`
--
ALTER TABLE `regions_paths`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `region_taxes`
--
ALTER TABLE `region_taxes`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `religions`
--
ALTER TABLE `religions`
  MODIFY `id` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `stats_globals`
--
ALTER TABLE `stats_globals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `stats_historical`
--
ALTER TABLE `stats_historical`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `stats_items`
--
ALTER TABLE `stats_items`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `structures`
--
ALTER TABLE `structures`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `structure_events`
--
ALTER TABLE `structure_events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `structure_grants`
--
ALTER TABLE `structure_grants`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `structure_lentitems`
--
ALTER TABLE `structure_lentitems`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `structure_options`
--
ALTER TABLE `structure_options`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `structure_resources`
--
ALTER TABLE `structure_resources`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `structure_stats`
--
ALTER TABLE `structure_stats`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `structure_types`
--
ALTER TABLE `structure_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `structure_types_cfgitems`
--
ALTER TABLE `structure_types_cfgitems`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `suggestions`
--
ALTER TABLE `suggestions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `taxes`
--
ALTER TABLE `taxes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `toplistvotes`
--
ALTER TABLE `toplistvotes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `trace_coins`
--
ALTER TABLE `trace_coins`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `trace_coinsdist`
--
ALTER TABLE `trace_coinsdist`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `trace_couple_logins`
--
ALTER TABLE `trace_couple_logins`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `trace_sales`
--
ALTER TABLE `trace_sales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `trace_sinks`
--
ALTER TABLE `trace_sinks`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `trace_user_logins`
--
ALTER TABLE `trace_user_logins`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users_sharedips`
--
ALTER TABLE `users_sharedips`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_languages`
--
ALTER TABLE `user_languages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_referrals`
--
ALTER TABLE `user_referrals`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_tokens`
--
ALTER TABLE `user_tokens`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `wardrobe_approvalrequests`
--
ALTER TABLE `wardrobe_approvalrequests`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `battles`
--
ALTER TABLE `battles`
  ADD CONSTRAINT `FK_battles_dest_regions` FOREIGN KEY (`dest_region_id`) REFERENCES `regions` (`id`);

--
-- Constraints for table `cfgpremiumbonuses_cuts`
--
ALTER TABLE `cfgpremiumbonuses_cuts`
  ADD CONSTRAINT `cfgpremiumbonuses_cuts_ibfk_1` FOREIGN KEY (`cfgpremiumbonus_id`) REFERENCES `cfgpremiumbonuses` (`id`);

--
-- Constraints for table `cfgpremiumbonuses_promos`
--
ALTER TABLE `cfgpremiumbonuses_promos`
  ADD CONSTRAINT `FK_cfgpremiumbonuses_promos_cfgpremiumbonuses` FOREIGN KEY (`cfgpremiumbonus_id`) REFERENCES `cfgpremiumbonuses` (`id`);

--
-- Constraints for table `cfgquest_events`
--
ALTER TABLE `cfgquest_events`
  ADD CONSTRAINT `FK_cfgquest_events_cfgquests` FOREIGN KEY (`cfgquest_id`) REFERENCES `cfgquests` (`id`);

--
-- Constraints for table `cfgwardrobeitems`
--
ALTER TABLE `cfgwardrobeitems`
  ADD CONSTRAINT `cfgwardrobeitems_ibfk_1` FOREIGN KEY (`cfgpremiumbonus_id`) REFERENCES `cfgpremiumbonuses` (`id`);

--
-- Constraints for table `character_premiumbonuses`
--
ALTER TABLE `character_premiumbonuses`
  ADD CONSTRAINT `character_premiumbonuses_ibfk_1` FOREIGN KEY (`cfgpremiumbonus_id`) REFERENCES `cfgpremiumbonuses` (`id`),
  ADD CONSTRAINT `character_premiumbonuses_ibfk_2` FOREIGN KEY (`cfgpremiumbonus_cut_id`) REFERENCES `cfgpremiumbonuses_cuts` (`id`),
  ADD CONSTRAINT `character_premiumbonuses_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `character_premiumbonuses_ibfk_4` FOREIGN KEY (`targetuser_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `character_stats`
--
ALTER TABLE `character_stats`
  ADD CONSTRAINT `FK_character_id` FOREIGN KEY (`character_id`) REFERENCES `characters` (`id`);

--
-- Constraints for table `character_titles`
--
ALTER TABLE `character_titles`
  ADD CONSTRAINT `FK_character_titles_cfgachievements` FOREIGN KEY (`cfgachievement_id`) REFERENCES `cfgachievements` (`id`);

--
-- Constraints for table `church_dogmabonuses`
--
ALTER TABLE `church_dogmabonuses`
  ADD CONSTRAINT `FK_church_dogmabonuses_cfgdogmabonuses` FOREIGN KEY (`cfgdogmabonus_id`) REFERENCES `cfgdogmabonuses` (`id`),
  ADD CONSTRAINT `FK_church_dogmabonuses_churches` FOREIGN KEY (`church_id`) REFERENCES `churches` (`id`);

--
-- Constraints for table `diplomacy_proposals`
--
ALTER TABLE `diplomacy_proposals`
  ADD CONSTRAINT `FK__kingdoms_source` FOREIGN KEY (`sourcekingdom_id`) REFERENCES `kingdoms` (`id`),
  ADD CONSTRAINT `FK__kingdoms_target` FOREIGN KEY (`targetkingdom_id`) REFERENCES `kingdoms` (`id`);

--
-- Constraints for table `facebook_inviterequests`
--
ALTER TABLE `facebook_inviterequests`
  ADD CONSTRAINT `FK_facebook_inviterequests_users` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `gameevent_subscriptions`
--
ALTER TABLE `gameevent_subscriptions`
  ADD CONSTRAINT `FK__cfggameevents` FOREIGN KEY (`cfggameevent_id`) REFERENCES `cfggameevents` (`id`);

--
-- Constraints for table `kingdomprojects`
--
ALTER TABLE `kingdomprojects`
  ADD CONSTRAINT `FK_kingdomprojects_regions` FOREIGN KEY (`region_id`) REFERENCES `regions` (`id`),
  ADD CONSTRAINT `FK_kingdomprojects_structures` FOREIGN KEY (`structure_id`) REFERENCES `structures` (`id`);

--
-- Constraints for table `kingdoms_history`
--
ALTER TABLE `kingdoms_history`
  ADD CONSTRAINT `FK_kingdoms_history_kingdoms` FOREIGN KEY (`kingdom_id`) REFERENCES `kingdoms` (`id`);

--
-- Constraints for table `kingdom_forum_boards`
--
ALTER TABLE `kingdom_forum_boards`
  ADD CONSTRAINT `kingdom_forum_boards_ibfk_1` FOREIGN KEY (`kingdom_id`) REFERENCES `kingdoms` (`id`);

--
-- Constraints for table `kingdom_forum_replies`
--
ALTER TABLE `kingdom_forum_replies`
  ADD CONSTRAINT `kingdom_forum_replies_ibfk_1` FOREIGN KEY (`kingdom_forum_topic_id`) REFERENCES `kingdom_forum_topics` (`id`);

--
-- Constraints for table `kingdom_forum_topics`
--
ALTER TABLE `kingdom_forum_topics`
  ADD CONSTRAINT `kingdom_forum_topics_ibfk_1` FOREIGN KEY (`kingdom_forum_board_id`) REFERENCES `kingdom_forum_boards` (`id`);

--
-- Constraints for table `kingdom_nobletitles`
--
ALTER TABLE `kingdom_nobletitles`
  ADD CONSTRAINT `fk_kingdoms` FOREIGN KEY (`id`) REFERENCES `kingdoms` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `kingdom_taxes`
--
ALTER TABLE `kingdom_taxes`
  ADD CONSTRAINT `FK__kingdoms` FOREIGN KEY (`kingdom_id`) REFERENCES `kingdoms` (`id`);

--
-- Constraints for table `kingdom_titles`
--
ALTER TABLE `kingdom_titles`
  ADD CONSTRAINT `FK_kingdom_titles_cfgachievements` FOREIGN KEY (`cfgachievement_id`) REFERENCES `cfgachievements` (`id`),
  ADD CONSTRAINT `FK_kingdom_titles_kingdoms` FOREIGN KEY (`kingdom_id`) REFERENCES `kingdoms` (`id`);

--
-- Constraints for table `kingdom_wars`
--
ALTER TABLE `kingdom_wars`
  ADD CONSTRAINT `FK_kingdom_wars_kingdoms` FOREIGN KEY (`source_kingdom_id`) REFERENCES `kingdoms` (`id`),
  ADD CONSTRAINT `FK_kingdom_wars_kingdoms_2` FOREIGN KEY (`target_kingdom_id`) REFERENCES `kingdoms` (`id`);

--
-- Constraints for table `kingdom_wars_allies`
--
ALTER TABLE `kingdom_wars_allies`
  ADD CONSTRAINT `FK_kingdom_war_id` FOREIGN KEY (`kingdom_war_id`) REFERENCES `kingdom_wars` (`id`),
  ADD CONSTRAINT `FK_kingdoms_id` FOREIGN KEY (`kingdom_id`) REFERENCES `kingdoms` (`id`);

--
-- Constraints for table `marketingcampaigns`
--
ALTER TABLE `marketingcampaigns`
  ADD CONSTRAINT `FK_marketingcampaigns_users` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `marketingdailystatistics`
--
ALTER TABLE `marketingdailystatistics`
  ADD CONSTRAINT `FK_marketingdailystatistics_marketingcampaigns` FOREIGN KEY (`campaignID`) REFERENCES `marketingcampaigns` (`id`);

--
-- Constraints for table `marketingstatistics`
--
ALTER TABLE `marketingstatistics`
  ADD CONSTRAINT `FK_marketingstatistics_marketingcampaigns` FOREIGN KEY (`campaignID`) REFERENCES `marketingcampaigns` (`id`),
  ADD CONSTRAINT `FK_marketingstatistics_users` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `regions_paths_fasttracksroutes`
--
ALTER TABLE `regions_paths_fasttracksroutes`
  ADD CONSTRAINT `FK__fasttrackroutes_regions` FOREIGN KEY (`region_id`) REFERENCES `regions` (`id`),
  ADD CONSTRAINT `FK__fasttrackroutes_regions_paths` FOREIGN KEY (`regions_path_id`) REFERENCES `regions_paths` (`id`);

--
-- Constraints for table `region_taxes`
--
ALTER TABLE `region_taxes`
  ADD CONSTRAINT `FK__regions` FOREIGN KEY (`region_id`) REFERENCES `regions` (`id`);

--
-- Constraints for table `structures`
--
ALTER TABLE `structures`
  ADD CONSTRAINT `FK_structures_structure_types` FOREIGN KEY (`structure_type_id`) REFERENCES `structure_types` (`id`);

--
-- Constraints for table `structure_types_cfgitems`
--
ALTER TABLE `structure_types_cfgitems`
  ADD CONSTRAINT `FK_structure_types_cfgitems_cfgitems` FOREIGN KEY (`cfgitem_id`) REFERENCES `cfgitems` (`id`),
  ADD CONSTRAINT `FK_structure_types_cfgitems_structure_types` FOREIGN KEY (`structure_type_id`) REFERENCES `structure_types` (`id`);

--
-- Constraints for table `user_languages`
--
ALTER TABLE `user_languages`
  ADD CONSTRAINT `FK__users` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
