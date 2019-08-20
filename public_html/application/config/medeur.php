<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * In quale ambiente siamo?
*/

$config['environment'] = 'live';

/**
 * Nome Server
*/

$config['servername'] = 'Classic';

/*
* Admin email
*/

$config['adminemail'] = 'support@eightyeightbrands.com';

/**
 * Registrazioni abilitate?
*/

$config['registration'] = TRUE;

/*
* Send email
*/

$config['sendnotifications'] = TRUE;

/*
* multilogin_check
*/

$config['multilogin_check'] = TRUE;

/*
* death enabled
*/

$config['death_enabled'] = TRUE;

/*
* onlyadmin can login
*/

$config['loginonlyadmin'] = FALSE;

/*
* External forum, scripts location
*/

$config['officialrpforumurl'] = 'https://rpforum.medieval-europe.eu';

/*
* Support
*/

$config['supporturl'] = 'https://support.medieval-europe.eu';


/*
* messaggi di debug per battaglia
*/

$config['debugbe'] = FALSE;
$config['debugbevideo'] = FALSE;

/*
* proxy test
*/

$config['proxytest'] = FALSE;

/*
* Trace in/out silvercoins/doubloons?
*/

$config['tracesinksdoubloons'] = TRUE;
$config['tracesinkssilvercoins'] = TRUE;
$config['tracesales'] = FALSE;

/*
* Show benchmarks?
*/

$config['displaybenchmark'] = FALSE;

/*
* Delete forum account?
*/

$config['deleteforumaccount'] = TRUE;

/*
* Use memcache?
*/

$config['memcache'] = TRUE;

/*
* Empty the Dump?
*/

$config['emptydump'] = TRUE;

/*
* Server Speed
*/

$config['serverspeed'] = 1;

/*
* Max war length
*/

$config['maxwarlength'] = 21;

/*
* Days needed to pass before declaring a new war
*/

$config['war_newdeclarationcooldown'] = 2;

/*
* Production Factor
*/

$config['productionfactor'] = 100;

/*
Max idle time in seconds (used to evaluate if a player is online)
*/

$config['maxidletime'] = 900;

/*
Max number of shops
*/

$config['maxshops'] = 1;

/*
Max number of terrains
*/

$config['maxterrains'] = 2;

/*
Min days for fight
*/

$config['mindaystofight'] = 7;

/*
* Cooldow to change the same diplomatic relation
*/

$config['diplomacychangecooldown'] = 15;

/*
Giorni minimi di cittadinanza per dichiarare rivolta
*/

$config['revolt_declarerevoltdayslimit'] = 30;

/*
Giorni minimi di cittadinanza per schierarsi in attacco
*/

$config['revolt_attackerdayslimit'] = 30;

/*
Giorni minimi di cittadinanza per schierarsi in difesa
*/

$config['revolt_defenderdayslimit'] = 30;

/*
Ore che passano dalla dichiarazione alla creazione del campo di battaglia
*/

$config['revolt_battlefieldcreationtime'] = 48;

/*
Ore che passano dalla creazione del campo di battaglia al primo round
*/

$config['revolt_firstbattleroundtime'] = 12;

/*
* Giorni che devono passare prima che si possa dichiarare una nuova rivolta
*/

$config['revolt_cooldown'] = 2;

/*
Ore che passano dal round alla distruzione del campo di battaglia
*/

$config['battlefielddestroytime'] = 18;

/*
Ore che passano tra un round e un altro
*/

$config['nextroundtime'] = 8;

/*
Min days for church role level 1
*/

$config['churchlevel1minage'] = 0;

/*
Min days for church role level 2
*/

$config['churchlevel2minage'] = 60;

/*
Min days for church role level 3
*/

$config['churchlevel3minage'] = 30;

/*
Min days for church role level 4
*/

$config['churchlevel4minage'] = 7;

/*
Min days for King role
*/

$config['kingminage'] = 90;

/*
Min days for Vassal role
*/

$config['vassalminage'] = 60;

/*
Min days for judge role
*/

$config['judgeminage'] = 30;

/*
Min days for guardcaptainminage role
*/

$config['guardcaptainminage'] = 7;

/*
Min days for towerguardianminage role
*/

$config['towerguardianminage'] = 7;

/*
Min days for academydirectorminage role
*/

$config['academydirectorminage'] = 30;

/*
Min days for drillmasterminage role
*/

$config['drillmasterminage'] = 30;

/*
Min days for newbiedays
*/

$config['newbiedays'] = 30;

/*
Min days for revolt
*/
$config['revoltminimumage'] = 10;

/*
Giorni di cooldown tra una guerra e l'altra
*/

$config['war_newdeclarationcooldown'] = 3;

/*
Giorni di cooldown prima che gli attacchi abbiano
inizio
*/

$config['war_cooldownbeforeattacks'] = 2;

/*
Native revolt interval
*/

$config['nativerevoltinterval'] = 7;

/* Consume rate for items */

$config['consume_verylow'] = 0.12;
$config['consume_low'] = 0.25;
$config['consume_medium'] = 0.5;
$config['consume_high'] = 1;
$config['consume_veryhigh'] = 2;

/*
Min days for newbiedays
*/

$config['revoltminimumage'] = 30;

/*
Name of server
*/

$config['servername'] = 'Classic';

/* superrewards keys */

$config['sw_apphash'] = '';
$config['sw_postbackkey'] = '';

/* gourl keys */

$config['gourl_publickey'] = '';
$config['gourl_privatekey'] = '';

/* facebook keys */

$config['facebook_app_id'] = '';
$config['facebook_app_secret'] = '';
$config['facebook_callback'] = 'https://medieval-europe.eu/facebook/loginfromgame.php';
$config['facebook_canvassurl'] = '';

/*
* Mostra Banner Evento?
*/

$config['displayeventbanner'] = FALSE;

/*
* Costi per diventare Re.
*/

$config['kingcostxregion'] = 250;
$config['kingcostfixed'] = 1500;
