<?php
define ( 'SYSPATH', 1 );
include dirname(__FILE__) . '/../../../scripts/libs/KLogger.php';
include dirname(__FILE__) . "/../../../application/config/database.php";

mysql_connect( 'localhost', $config['default']['connection']['user'], $config['default']['connection']['pass'] ) or die('error: cannot connect to database');
mysql_select_db( $config['default']['connection']['database'] );
$log = new KLogger('reset_server.log', 'debug');

$log->LogDebug('--- start ---');
$log->LogDebug('Connecting to jdemolay db...');

try {
	
	mysql_query("set autocommit = 0");
	mysql_query("start transaction");
	mysql_query("begin");
		
	//Recupero vecchie capitali
	
	$log->LogDebug('-> Recovering old capitals...');

	$urbino = mysql_query ("SELECT * FROM kingdoms WHERE name like '%urbino%'") or die (mysql_error());
	if (mysql_num_rows($urbino)==0)
		mysql_query("INSERT INTO `kingdoms` (`id`, `name`, `image`, `status`, `title`, `slogan`, `color`, `language1`, `language2`, `lastattacked`, `activityscore`, `forumurl`) VALUES (NULL, 'kingdoms.duchy-urbino', 'duchy-urbino', '', 'global.title_grandduke', '', '#7bc7ff', '', '', NULL, 0.00000, NULL);
	") or die ( mysql_error());
	
	mysql_query("update regions set capital=1, kingdom_id = ( select id from kingdoms where name ='kingdoms.duchy-normandia') where name = 'regions.avranches'") or die( mysql_error());	
	mysql_query("update regions set capital=1, kingdom_id = ( select id from kingdoms where name ='kingdoms.kingdom-serbia') where name = 'regions.beograde'") or die( mysql_error());
	mysql_query("update regions set capital=1, kingdom_id = ( select id from kingdoms where name ='kingdoms.empire-byzantine') where name = 'regions.konstantinoupolis'") or die( mysql_error());
	mysql_query("update regions set capital=1, kingdom_id = ( select id from kingdoms where name ='kingdoms.duchy-aquitania') where name = 'regions.bordeaux'") or die( mysql_error());
	mysql_query("update regions set capital=1, kingdom_id = ( select id from kingdoms where name ='kingdoms.duchy-sassonia') where name = 'regions.bremen'") or die( mysql_error());
	mysql_query("update regions set capital=1, kingdom_id = ( select id from kingdoms where name ='kingdoms.county-fiandre') where name = 'regions.brugge'") or die( mysql_error());
	mysql_query("update regions set capital=1, kingdom_id = ( select id from kingdoms where name ='kingdoms.kingdom-castiglia') where name = 'regions.burgos'") or die( mysql_error());
	mysql_query("update regions set capital=1, kingdom_id = ( select id from kingdoms where name ='kingdoms.kingdom-ottoman') where name = 'regions.bursa'") or die( mysql_error());
	mysql_query("update regions set capital=1, kingdom_id = ( select id from kingdoms where name ='kingdoms.kingdom-sardegna') where name = 'regions.cagliari'") or die( mysql_error());
	mysql_query("update regions set capital=1, kingdom_id = ( select id from kingdoms where name ='kingdoms.kingdom-mamluk') where name = 'regions.cairo'") or die( mysql_error());
	mysql_query("update regions set capital=1, kingdom_id = ( select id from kingdoms where name ='kingdoms.kingdom-cyrene') where name = 'regions.derne'") or die( mysql_error());
	mysql_query("update regions set capital=1, kingdom_id = ( select id from kingdoms where name ='kingdoms.kingdom-bulgaria') where name = 'regions.tyrnovo'") or die( mysql_error());
	mysql_query("update regions set capital=1, kingdom_id = ( select id from kingdoms where name ='kingdoms.kingdom-irlanda') where name = 'regions.dublin'") or die( mysql_error());
	mysql_query("update regions set capital=1, kingdom_id = ( select id from kingdoms where name ='kingdoms.kingdom-albania') where name = 'regions.dyrrachion'") or die( mysql_error());
	mysql_query("update regions set capital=1, kingdom_id = ( select id from kingdoms where name ='kingdoms.duchy-ferrara') where name = 'regions.ferrara'") or die( mysql_error());
	mysql_query("update regions set capital=1, kingdom_id = ( select id from kingdoms where name ='kingdoms.republic-firenze') where name = 'regions.firenze'") or die( mysql_error());
	mysql_query("update regions set capital=1, kingdom_id = ( select id from kingdoms where name ='kingdoms.republic-genova') where name = 'regions.genoa'") or die( mysql_error());
	mysql_query("update regions set capital=1, kingdom_id = ( select id from kingdoms where name ='kingdoms.sultanate-granada') where name = 'regions.granada'") or die( mysql_error());
	mysql_query("update regions set capital=1, kingdom_id = ( select id from kingdoms where name ='kingdoms.principality-galles') where name = 'regions.gwynnedd'") or die( mysql_error());
	mysql_query("update regions set capital=1, kingdom_id = ( select id from kingdoms where name ='kingdoms.kingdom-seljuq') where name = 'regions.ikonion'") or die( mysql_error());
	mysql_query("update regions set capital=1, kingdom_id = ( select id from kingdoms where name ='kingdoms.kingdom-jerusalem') where name = 'regions.jerusalem'") or die( mysql_error());
	mysql_query("update regions set capital=1, kingdom_id = ( select id from kingdoms where name ='kingdoms.principality-kiev') where name = 'regions.kiev'") or die( mysql_error());
	mysql_query("update regions set capital=1, kingdom_id = ( select id from kingdoms where name ='kingdoms.kingdom-prussia') where name = 'regions.konigsberg'") or die( mysql_error());
	mysql_query("update regions set capital=1, kingdom_id = ( select id from kingdoms where name ='kingdoms.kingdom-polonia') where name = 'regions.krakowskie'") or die( mysql_error());
	mysql_query("update regions set capital=1, kingdom_id = ( select id from kingdoms where name ='kingdoms.kingdom-portogallo') where name = 'regions.lisboa'") or die( mysql_error());
	mysql_query("update regions set capital=1, kingdom_id = ( select id from kingdoms where name ='kingdoms.kingdom-inghilterra') where name = 'regions.london'") or die( mysql_error());
	mysql_query("update regions set capital=1, kingdom_id = ( select id from kingdoms where name ='kingdoms.kingdom-scozia') where name = 'regions.lothian'") or die( mysql_error());
	mysql_query("update regions set capital=1, kingdom_id = ( select id from kingdoms where name ='kingdoms.duchy-milano') where name = 'regions.lombardia'") or die( mysql_error());
	mysql_query("update regions set capital=1, kingdom_id = ( select id from kingdoms where name ='kingdoms.kingdom-napoli') where name = 'regions.napoli'") or die( mysql_error());
	mysql_query("update regions set capital=1, kingdom_id = ( select id from kingdoms where name ='kingdoms.duchy-baviera') where name = 'regions.oberbayern'") or die( mysql_error());
	mysql_query("update regions set capital=1, kingdom_id = ( select id from kingdoms where name ='kingdoms.kingdom-sweden') where name = 'regions.ostergot-land'") or die( mysql_error());
	mysql_query("update regions set capital=1, kingdom_id = ( select id from kingdoms where name ='kingdoms.kingdom-sicilia') where name = 'regions.palermo'") or die( mysql_error());
	mysql_query("update regions set capital=1, kingdom_id = ( select id from kingdoms where name ='kingdoms.kingdom-francia') where name = 'regions.ile-de-france'") or die( mysql_error());
	mysql_query("update regions set capital=1, kingdom_id = ( select id from kingdoms where name ='kingdoms.kingdom-ungheria') where name = 'regions.pest'") or die( mysql_error());
	mysql_query("update regions set capital=1, kingdom_id = ( select id from kingdoms where name ='kingdoms.kingdom-boemia') where name = 'regions.praha'") or die( mysql_error());
	mysql_query("update regions set capital=1, kingdom_id = ( select id from kingdoms where name ='kingdoms.republic-roma') where name = 'regions.roma'") or die( mysql_error());
	mysql_query("update regions set capital=1, kingdom_id = ( select id from kingdoms where name ='kingdoms.duchy-savoia') where name = 'regions.savoy'") or die( mysql_error());
	mysql_query("update regions set capital=1, kingdom_id = ( select id from kingdoms where name ='kingdoms.republic-siena') where name = 'regions.siena'") or die( mysql_error());
	mysql_query("update regions set capital=1, kingdom_id = ( select id from kingdoms where name ='kingdoms.kingdom-danimarca') where name = 'regions.sjaelland'") or die( mysql_error());
	mysql_query("update regions set capital=1, kingdom_id = ( select id from kingdoms where name ='kingdoms.principality-valacchia') where name = 'regions.turnu'") or die( mysql_error());
	mysql_query("update regions set capital=1, kingdom_id = ( select id from kingdoms where name ='kingdoms.duchy-urbino') where name = 'regions.urbino'") or die( mysql_error());
	mysql_query("update regions set capital=1, kingdom_id = ( select id from kingdoms where name ='kingdoms.kingdom-aragona') where name = 'regions.valencia'") or die( mysql_error());
	mysql_query("update regions set capital=1, kingdom_id = ( select id from kingdoms where name ='kingdoms.republic-venezia') where name = 'regions.venezia'") or die( mysql_error());
	mysql_query("update regions set capital=1, kingdom_id = ( select id from kingdoms where name ='kingdoms.grand-duchy-lithuania') where name = 'regions.vilnius'") or die( mysql_error());

	$log->LogDebug('-> Removing unwanted Kingdoms...');
	
	// Rimozione vecchi regni mergiati e relative regioni
	
	mysql_query("
	update regions 
	set capital = false, 
	status = 'disabled',
	kingdom_id = 37 
	where kingdom_id in
	(select id from kingdoms where name in
	(
		'kingdoms.duchy-sassonia',
		'kingdoms.county-fiandre',
		'kingdoms.kingdom-irlanda',
		'kingdoms.grand-duchy-lithuania',
		'kingdoms.kingdom-sweden',
		'kingdoms.kingdom-prussia',
		'kingdoms.kingdom-danimarca',
		'kingdoms.kingdom-ottoman',
		'kingdoms.kingdom-seljuq',
		'kingdoms.kingdom-jerusalem',
		'kingdoms.kingdom-italy',
		'kingdoms.kingdom-sardegna',
		'kingdoms.kingdom-napoli',
		'kingdoms.republic-siena',
		'kingdoms.despotate-odrin',
		'kingdoms.kingdom-mamluk',
		'kingdoms.kingdom-cyrene',
		'kingdoms.kingdom-portogallo',
		'kingdoms.kingdom-inghilterra',
		'kingdoms.principality-galles',
		'kingdoms.kingdom-scozia',
		'kingdoms.kingdom-sweden'
	)); ") or die( mysql_error());
	
	mysql_query("update kingdoms 
	set status = 'deleted'
	where name in
	(
	'kingdoms.duchy-sassonia',
	'kingdoms.county-fiandre',
	'kingdoms.kingdom-irlanda',
	'kingdoms.grand-duchy-lithuania',
	'kingdoms.kingdom-sweden',
	'kingdoms.kingdom-prussia',
	'kingdoms.kingdom-danimarca',
	'kingdoms.kingdom-ottoman',
	'kingdoms.kingdom-seljuq',
	'kingdoms.kingdom-jerusalem',
	'kingdoms.kingdom-italy',
	'kingdoms.kingdom-sardegna',
	'kingdoms.kingdom-napoli',
	'kingdoms.republic-siena',
	'kingdoms.despotate-odrin',
	'kingdoms.kingdom-mamluk',
	'kingdoms.kingdom-cyrene',
	'kingdoms.kingdom-portogallo',
	'kingdoms.kingdom-inghilterra',
	'kingdoms.principality-galles',
	'kingdoms.kingdom-scozia',
	'kingdoms.kingdom-sweden'
	);") or die( mysql_error());

	mysql_query("
	update regions 
	set status='disabled', kingdom_id = 37 
	where name in 
	(
	'regions.forcalquier',
	'regions.provence',
	'regions.venaissin',
	'regions.mainz',
	'regions.pfalz',
	'regions.nordgau',
	'regions.thouars',
	'regions.auvergne',
	'regions.forez',
	'regions.viviers',
	'regions.gevaudan',
	'regions.rouergue',
	'regions.toulouse',
	'regions.carcassonne',
	'regions.montpellier',
	'regions.narbonne',
	'regions.foix',
	'regions.rosello',
	'regions.urgell',
	'regions.empuries',
	'regions.lieida',
	'regions.barcelona',
	'regions.tarragona',
	'regions.compostela',
	'regions.santiago',
	'regions.el-bierzo',
	'regions.porto',
	'regions.braganza',
	'regions.zamora',
	'regions.coimbra',
	'regions.castelo branco',
	'regions.salamanca',
	'regions.lisboa',
	'regions.aicacer-do-sal',
	'regions.silves',
	'regions.faro',
	'regions.niebla',
	'regions.aracena',
	'regions.cadiz',
	'regions.sevilla',
	'regions.algeciras',
	'regions.malaga',
	'regions.evora',
	'regions.alcantara',
	'regions.plasencia',
	'regions.mertola',
	'regions.caceres',
	'regions.badajoz',
	'regions.lion',
	'regions.denthlivre',
	'regions.cornouaille',
	'regions.nantes',
	'regions.vannes',
	'regions.brugge',
	'regions.gent',
	'regions.liege',
	'regions.luxembourg',
	'regions.andernach',
	'regions.julich',
	'regions.loon',
	'regions.breda',
	'regions.zeeland',
	'regions.cornwall',
	'regions.exeter',
	'regions.devon',
	'regions.dorset',
	'regions.sommerset',
	'regions.hampshire',
	'regions.sussex',
	'regions.kent',
	'regions.surrey',
	'regions.salisbury',
	'regions.bristol',
	'regions.oxford',
	'regions.essex',
	'regions.norfolk',
	'regions.suffolk',
	'regions.lincoln',
	'regions.northampton',
	'regions.warwick',
	'regions.hereford',
	'regions.gwent',
	'regions.london',
	'regions.leicester',
	'regions.derby',
	'regions.lancaster',
	'regions.york',
	'regions.chester',
	'regions.powys',
	'regions.gwynnedd',
	'regions.glamorgan',
	'regions.dyfed',
	'regions.perfed-dwald',
	'regions.glocester',
	'regions.shrewsbury',
	'regions.westmorland',
	'regions.durkham',
	'regions.cumberland',
	'regions.north-cumberland',
	'regions.birwick',
	'regions.galloway',
	'regions.carrick',
	'regions.argyll',
	'regions.atholl',
	'regions.angus',
	'regions.moray',
	'regions.mar ',
	'regions.buchan',
	'regions.ross',
	'regions.sutherland',
	'regions.fife',
	'regions.caithness',
	'regions.strathclyde',
	'regions.lothian',
	'regions.desmumu',
	'regions.urmumu',
	'regions.tuadmumu',
	'regions.laigin',
	'regions.galway',
	'regions.mide',
	'regions.osraigh',
	'regions.dublin',
	'regions.ulaid',
	'regions.tir-loghain',
	'regions.tir-connah',
	'regions.mayd',
	'regions.ziigo',
	'regions.bremen',
	'regions.celle',
	'regions.lueneburg',
	'regions.altmark',
	'regions.anhalt',
	'regions.leipzig',
	'regions.thueringen',
	'regions.weimar',
	'regions.nirsau',
	'regions.koln',
	'regions.goettingen',
	'regions.kleve',
	'regions.munster',
	'regions.braunschweig',
	'regions.oldenburg',
	'regions.osnabrueck',
	'regions.ostfriesland',
	'regions.frisia',
	'regions.gelre',
	'regions.westfriesland',
	'regions.holland',
	'regions.utrecht',
	'regions.jylland',
	'regions.slesvig',
	'regions.fyn',
	'regions.sjaelland',
	'regions.holstein',
	'regions.hamburg',
	'regions.luebeck',
	'regions.mecklenburg',
	'regions.rosrock',
	'regions.werle',
	'regions.wolgagt',
	'regions.brandenburg',
	'regions.lausitz',
	'regions.bergenshus',
	'regions.oppland',
	'regions.varmland',
	'regions.dalarna',
	'regions.gastrikland',
	'regions.uppland',
	'regions.soderman-land',
	'regions.ostergot-land',
	'regions.smaland',
	'regions.kalmarian',
	'regions.skanf',
	'regions.finnveden',
	'regions.halland',
	'regions.vastergotland',
	'regions.viken',
	'regions.daz',
	'regions.akershus',
	'regions.vestiold',
	'regions.agder',
	'regions.rogaland',
	'regions.telemark',
	'regions.narke',
	'regions.stettin',
	'regions.slupsk',
	'regions.danzig',
	'regions.lubusz',
	'regions.gnieznienskie',
	'regions.kujawy',
	'regions.kaliskie',
	'regions.wielkopolska',
	'regions.baden',
	'regions.fursten-berg',
	'regions.leiningen',
	'regions.franken',
	'regions.wurttemberg',
	'regions.ansbach',
	'regions.korsun',
	'regions.oleshye',
	'regions.lower-dniepr',
	'regions.crimea',
	'regions.theodosia',
	'regions.korchev',
	'regions.yalta',
	'regions.peresechen',
	'regions.olvia',
	'regions.belgoroc',
	'regions.naxos',
	'regions.rhodos',
	'regions.kafia',
	'regions.chadax',
	'regions.limisol',
	'regions.famagusta',
	'regions.ibiza',
	'regions.gotland',
	'regions.chelminskie',
	'regions.konigsberg',
	'regions.sambia',
	'regions.memel',
	'regions.kurs',
	'regions.zimigalians',
	'regions.vilnius',
	'regions.scalovia',
	'regions.galindia',
	'regions.mazowsze',
	'regions.yatviagi',
	'regions.sudovia',
	'regions.auksmayts',
	'regions.polotsk',
	'regions.minsk',
	'regions.podlasie',
	'regions.lublin',
	'regions.berestye',
	'regions.pinsk',
	'regions.turov',
	'regions.lyubech',
	'regions.chernigov',
	'regions.pereslavl',
	'regions.chortitza',
	'regions.lukomorie',
	'regions.lower-don',
	'regions.sirte',
	'regions.tini',
	'regions.zanara',
	'regions.bengasi',
	'regions.derne',
	'regions.ugrela',
	'regions.trobuch',
	'regions.cherfus',
	'regions.cazales',
	'regions.berton',
	'regions.nitriota',
	'regions.alexandria',
	'regions.cairo',
	'regions.damietta',
	'regions.suez',
	'regions.aggara',
	'regions.gazza',
	'regions.negev',
	'regions.edom',
	'regions.jerusalem',
	'regions.jaffa',
	'regions.moab',
	'regions.caesarea',
	'regions.golan',
	'regions.acre',
	'regions.tyrus',
	'regions.tortosa',
	'regions.antiochia',
	'regions.iskenderon',
	'regions.sis',
	'regions.amasya',
	'regions.sinope',
	'regions.kostamonou',
	'regions.ankara',
	'regions.yozgat',
	'regions.ikonion',
	'regions.adana',
	'regions.tarsos',
	'regions.dorylaion',
	'regions.nikomadeia',
	'regions.nicea',
	'regions.afyon',
	'regions.attaleia',
	'regions.myra',
	'regions.kutahya',
	'regions.smyrna',
	'regions.abydos',
	'regions.bursa')") or die (mysql_error());
	
	// Resetto tutte le regioni a indipendenti
	
	$log->LogDebug('Resetting independent regions...');

	mysql_query( "update regions set kingdom_id = 37 where capital = false") or die (mysql_error());

	$log->LogDebug('Reset Kingdoms...');
	
	//restore all kingdoms

	mysql_query( "update kingdoms set slogan='', language1 = '',
	language2='', lastattacked=null, activityscore=0, forumurl=null") or die (mysql_error()); 

	mysql_query( "delete from kingdoms_history") or die (mysql_error()); 

	// Rimuovo tutte le strutture
	
	mysql_query( "truncate table structures") or die (mysql_error()); 
	$log->LogDebug('-> Fixing diplomacy...');
	
	// Fix Capitals
	
	$log->LogDebug('Fixing capitals...');
	
	$capitals = mysql_query("select * from regions where capital = true") or die(mysql_error());
	while ( $row = mysql_fetch_assoc( $capitals ) ) 
	{
			$log->LogDebug("Processing capital : {$row['name']}");
		
		// pal. reale
		mysql_query("insert into structures (
		id, parent_structure_id, structure_type_id, region_id, character_id, size) values (
		null, null, (select id from structure_types where type = 'royalpalace'),
		{$row['id']}, NULL, 'small')") or die(mysql_error());
		
		$royalpalaceid = mysql_insert_id();
		
		// castello
		mysql_query("insert into structures (
		id, parent_structure_id, structure_type_id, region_id, character_id, size) values (
		null, {$royalpalaceid}, (select id from structure_types where type = 'castle'),
		{$row['id']}, NULL, 'small')") or die(mysql_error());
		
		$castleid = mysql_insert_id();
		
		// tribunale
		mysql_query("insert into structures (
		id, parent_structure_id, structure_type_id, region_id, character_id, size) values (
		null, {$castleid}, (select id from structure_types where type = 'court'),
		{$row['id']}, NULL, 'small')") or die(mysql_error());
		
		// barracks
		mysql_query("insert into structures (
		id, parent_structure_id, structure_type_id, region_id, character_id, size) values (
		null, {$castleid}, (select id from structure_types where type = 'barracks_1'),
		{$row['id']}, NULL, 'small')") or die(mysql_error());
		
		mysql_query("insert into structures (
		id, parent_structure_id, structure_type_id, region_id, character_id, size) values (
		null, {$castleid}, (select id from structure_types where type = 'tavern'),
		{$row['id']}, NULL, 'small')") or die(mysql_error());
		
		mysql_query("insert into structures (
		id, parent_structure_id, structure_type_id, region_id, character_id, size) values (
		null, {$castleid}, (select id from structure_types where type = 'market'),
		{$row['id']}, NULL, 'small')") or die(mysql_error());
		
		$sql = "
			select rp.id  
			from regions_paths rp 
			where rp.region_id = {$row['id']}
			and   rp.type in ( 'sea', 'mixed' ) ";
		
		$paths = mysql_query($sql) or die (mysql_error());		
		$regions = mysql_num_rows($paths);
		
		if ( $regions > 0 ) 			
		{
			$log->LogDebug("Adding harbour in region: {$row['name']}");
			mysql_query("insert into structures (
			id, parent_structure_id, structure_type_id, region_id, character_id, size) values (
			null, null, (select id from structure_types where type = 'harbor'),
			{$row['id']}, NULL, 'small')") or die(mysql_error());
		}
		
		mysql_query("insert into structures (
		id, parent_structure_id, structure_type_id, region_id, character_id, size) values (
		null, null, (select id from structure_types where type = 'dump'),
		{$row['id']}, NULL, 'small')") or die(mysql_error());
		
	}
	
	// Fix Independent Regions
	$log->LogDebug('Fixing Independent regions...');
	
	$independentregions = mysql_query("
	select * from regions 
	where capital = false and kingdom_id = 37
	and type = 'land' ");
	while ( $row = mysql_fetch_assoc( $independentregions ) ) 
	{
		
		
		
		mysql_query("insert into structures (
		id, parent_structure_id, structure_type_id, region_id, character_id, size) values (
		null, null, (select id from structure_types where type = 'nativevillage'),
		{$row['id']}, NULL, 'small')") or die(mysql_error());
		
		mysql_query("insert into structures (
		id, parent_structure_id, structure_type_id, region_id, character_id, size) values (
		null, null, (select id from structure_types where type = 'dump'),
		{$row['id']}, NULL, 'small')") or die(mysql_error());
		
	}
	
	// Fix Religious HQ
	
	mysql_query("insert into structures (
		id, parent_structure_id, structure_type_id, region_id, character_id, size) values (
		null, null, (select id from structure_types where type = 'religion_1' and church_id = 1),
		(select id from regions where name = 'regions.roma'), NULL, 'small')") or die(mysql_error());
	
	$parentstructure_id = mysql_insert_id();
	
	mysql_query("insert into structures (
		id, parent_structure_id, structure_type_id, region_id, character_id, size) values (
	null, {$parentstructure_id}, (select id from structure_types where type = 'religion_2' and church_id = 1),
		(select id from regions where name = 'regions.roma'), NULL, 'small')") or die(mysql_error());
	
	$parentstructure_id = mysql_insert_id();
	
	mysql_query("insert into structures (
		id, parent_structure_id, structure_type_id, region_id, character_id, size) values (
	null, {$parentstructure_id}, (select id from structure_types where type = 'religion_3' and church_id = 1),
		(select id from regions where name = 'regions.roma'), NULL, 'small')") or die(mysql_error());
	
	$parentstructure_id = mysql_insert_id();
	
	mysql_query("insert into structures (
		id, parent_structure_id, structure_type_id, region_id, character_id, size) values (
	null, {$parentstructure_id}, (select id from structure_types where type = 'religion_4' and church_id = 1),
		(select id from regions where name = 'regions.roma'), NULL, 'small')") or die(mysql_error());
	
	
	mysql_query("insert into structures (
		id, parent_structure_id, structure_type_id, region_id, character_id, size) values (
		null, null, (select id from structure_types where type = 'religion_1' and church_id = 3),
		(select id from regions where name = 'regions.turnu'), NULL, 'small')") or die(mysql_error());
		
	$parentstructure_id = mysql_insert_id();
	
	mysql_query("insert into structures (
		id, parent_structure_id, structure_type_id, region_id, character_id, size) values (
	null, {$parentstructure_id}, (select id from structure_types where type = 'religion_2' and church_id = 3),
		(select id from regions where name = 'regions.turnu'), NULL, 'small')") or die(mysql_error());
	
	$parentstructure_id = mysql_insert_id();
	
	mysql_query("insert into structures (
		id, parent_structure_id, structure_type_id, region_id, character_id, size) values (
	null, {$parentstructure_id}, (select id from structure_types where type = 'religion_3' and church_id = 3),
		(select id from regions where name = 'regions.turnu'), NULL, 'small')") or die(mysql_error());
	
	$parentstructure_id = mysql_insert_id();
	
	mysql_query("insert into structures (
		id, parent_structure_id, structure_type_id, region_id, character_id, size) values (
	null, {$parentstructure_id}, (select id from structure_types where type = 'religion_4' and church_id = 3),
		(select id from regions where name = 'regions.turnu'), NULL, 'small')") or die(mysql_error());
	

	mysql_query("insert into structures (
		id, parent_structure_id, structure_type_id, region_id, character_id, size) values (
		null, null, (select id from structure_types where type = 'religion_1' and church_id = 5),
		(select id from regions where name = 'regions.cairo'), NULL, 'small')") or die(mysql_error());
		
	$parentstructure_id = mysql_insert_id();
	
	mysql_query("insert into structures (
		id, parent_structure_id, structure_type_id, region_id, character_id, size) values (
	null, {$parentstructure_id}, (select id from structure_types where type = 'religion_2' and church_id = 5),
		(select id from regions where name = 'regions.cairo'), NULL, 'small')") or die(mysql_error());
	
	$parentstructure_id = mysql_insert_id();
	
	mysql_query("insert into structures (
		id, parent_structure_id, structure_type_id, region_id, character_id, size) values (
	null, {$parentstructure_id}, (select id from structure_types where type = 'religion_3' and church_id = 5),
		(select id from regions where name = 'regions.cairo'), NULL, 'small')") or die(mysql_error());
	
	$parentstructure_id = mysql_insert_id();
	
	mysql_query("insert into structures (
		id, parent_structure_id, structure_type_id, region_id, character_id, size) values (
	null, {$parentstructure_id}, (select id from structure_types where type = 'religion_4' and church_id = 5),
		(select id from regions where name = 'regions.cairo'), NULL, 'small')") or die(mysql_error());
			
	mysql_query("insert into structures (
		id, parent_structure_id, structure_type_id, region_id, character_id, size) values (
		null, null, (select id from structure_types where type = 'religion_1' and church_id = 6),
		(select id from regions where name = 'regions.kiev'), NULL, 'small')") or die(mysql_error());
	
	$parentstructure_id = mysql_insert_id();
	
	mysql_query("insert into structures (
		id, parent_structure_id, structure_type_id, region_id, character_id, size) values (
	null, {$parentstructure_id}, (select id from structure_types where type = 'religion_2' and church_id = 6),
		(select id from regions where name = 'regions.kiev'), NULL, 'small')") or die(mysql_error());
	
	$parentstructure_id = mysql_insert_id();
	
	mysql_query("insert into structures (
		id, parent_structure_id, structure_type_id, region_id, character_id, size) values (
	null, {$parentstructure_id}, (select id from structure_types where type = 'religion_3' and church_id = 6),
		(select id from regions where name = 'regions.kiev'), NULL, 'small')") or die(mysql_error());
	
	$parentstructure_id = mysql_insert_id();
	
	mysql_query("insert into structures (
		id, parent_structure_id, structure_type_id, region_id, character_id, size) values (
	null, {$parentstructure_id}, (select id from structure_types where type = 'religion_4' and church_id = 6),
		(select id from regions where name = 'regions.kiev'), NULL, 'small')") or die(mysql_error());
	
	
	$log -> LogDebug('Adding nativevillages to independent regions...');
	$rset = mysql_query("
	select distinct r.name, s.structure_type_id from regions r, structures s
	where r.kingdom_id = 37
	and   s.region_id = r.id 
	and   r.`type` != 'sea' 
	and   not exists
	(select * from structures where region_id = r.id and structure_type_id 
	= (select id from structure_types where type = 'nativevillage'));"
		) or die (mysql_error());	
	
	while ( $row = mysql_fetch_assoc( $rset ) ) 
		mysql_query("insert into structures (
		id, parent_structure_id, structure_type_id, region_id, character_id) values (
		null, null, (select id from structure_types where type = 'nativevillage'),
		{$row['id']}, NULL)") or die(mysql_error());
		
	// Rimuovo tutte le risorse.
	
	$log->LogDebug("-> Wiping resources...");
	// non cancello fish shoal.
	mysql_query ("delete from structures where structure_type_id in
		( select id from structure_types where parenttype in
		('fish_shoal', 'forest', 'mine', 'breeding_region'))") or die(mysql_error());
	mysql_query ("delete from structure_resources") or die(mysql_error());
	
	$log->LogDebug("-> Wiped resources.");
	
	// Riaggiungo le strutture
	
	$log->LogDebug("-> Reinserting resources....");
	
	// load resources
	$resources = 
		array(		
			'land' => array( 
				'forest' =>
					array(
						'mandragora' => array( 'small' => 250, 'medium' => 500, 'large' => 1000),
						'wood_piece' => array( 'small' => 250, 'medium' => 500, 'large' => 1000),
						'medmushroom' => array( 'small' => 250, 'medium' => 500, 'large' => 1000)					
					),
				'mine_iron' => array(
					'iron_piece' => array( 'small' => 250, 'medium' => 500, 'large' => 1000)),
				'mine_clay' => array('clay_piece' => array( 'small' => 250, 'medium' => 500, 'large' => 1000)),
				'breeding_cow_region' => array('cows' => array( 'small' => 20, 'medium' => 40, 'large' => 80)),
				'breeding_sheep_region' => array('sheeps' => array( 'small' => 250, 'medium' => 500, 'large' => 1000)),
				'mine_coal' => array('coal_piece' => array( 'small' => 250, 'medium' => 500, 'large' => 1000)),
				'mine_stone' => array('stone_piece' => array( 'small' => 250, 'medium' => 500, 'large' => 1000)),
				'breeding_bee_region' => array('bees' => array( 'small' => 5000, 'medium' => 10000, 'large' => 20000)),
				'breeding_pig_region' => array('pigs' => array( 'small' => 20, 'medium' => 40, 'large' => 80)),
				'mine_gold' => array('gold_piece' => array( 'small' => 250, 'medium' => 500, 'large' => 1000)),
				'saltern' => array('salt_heap' => array( 'small' => 250, 'medium' => 500, 'large' => 1000)),
				'cave_white_sand' => array('sand_heap' => array( 'small' => 250, 'medium' => 500, 'large' => 1000)),
				'breeding_silkworm_region' => array('silkworms' => array( 'small' => 500, 'medium' => 1000, 'large' => 2000)),
			),
			'sea' => array (
				'fish_shoal' => array('fish' => array( 'small' => 20, 'medium' => 40, 'large' => 80)),						
			)
		);
	
	$dimension = array('small', 'medium', 'large');	
	$log->LogDebug("-> Processing regions...");	
	$regions = mysql_query("select * from regions");
	
	while ( $row = mysql_fetch_assoc( $regions ) ) 
	{
		
		$log->LogDebug("-> Processing region {$row['name']}...");
		$structure = array_rand($resources[$row['type']]);
		$size = $dimension[rand(0,2)];
		//$log->logDebug("-> Adding resource [{$structure}] size [{$size}] to region: {$row['name']}");
		
		mysql_query("insert into structures (
		id, parent_structure_id, structure_type_id, region_id, character_id, size) values (
		null, null, (select id from structure_types where type = '{$structure}'),
		{$row['id']}, NULL, '{$size}')") or die(mysql_error());
		
		$structure_id = mysql_insert_id();
		
		foreach ($resources[$row['type']][$structure] as $_resource => $_dimension)
		{
			$resourcesize = $_dimension[$size];
			$log->logDebug("-> Adding {$_resource} {$size} {$resourcesize} to region: {$row['name']}");
			$sql = "INSERT INTO `structure_resources` 
			VALUES (NULL, {$structure_id}, '{$_resource}', {$resourcesize}, {$resourcesize}, unix_timestamp()	)";
			//$log->LogDebug($sql);
			mysql_query($sql) or die(mysql_error());
				
		}
	
	}	
	
	// Fixing diplomacy
	$log->LogDebug('-> Fixing diplomacy...');	
	mysql_query("truncate table diplomacy_relations");
	$kingdomssource = mysql_query("select * from kingdoms_v") or die(mysql_error());
	$kingdomstarget = mysql_query("select * from kingdoms_v") or die(mysql_error());
	while ( $row = mysql_fetch_assoc( $kingdomssource )) 
	{
		while ( $row1 = mysql_fetch_assoc( $kingdomstarget )) 
		{
			$log->LogDebug("-> Fixing diplomacy {$row['name']} {$row1['name']}");	
			if ($row1['id'] != $row['id'] )	
				mysql_query("INSERT INTO `diplomacy_relations` (`id`, `sourcekingdom_id`, `targetkingdom_id`, `type`, `description`, `timestamp`, `signedby`) VALUES (NULL, {$row['id']}, {$row1['id']}, 'neutral', NULL, 
			unix_timestamp() - (15*24*3600), NULL );") or die(mysql_error());
		}
		mysql_data_seek ( $kingdomstarget , 0 );
	}	
	$log->LogDebug('-> Fixing taxes...');	
	mysql_query("truncate table taxes");	
	mysql_query("truncate table kingdom_taxes");
	$kingdoms = mysql_query("select * from kingdoms_v") or die(mysql_error());
	while ( $row = mysql_fetch_assoc( $kingdoms ) ) 
	{
		mysql_query("INSERT INTO `taxes` (`id`, `tag`, `type`, `region_id`, `kingdom_id`, `name`, `description`, `value`) VALUES (NULL, 'kingdom_property', 'kingdom', NULL, {$row['id']}, 'taxes.kingdom_property', 'taxes.kingdom_property_desc', 5);") or die(mysql_error());
		
		mysql_query("INSERT INTO `taxes` (`id`, `tag`, `type`, `region_id`, `kingdom_id`, `name`, `description`, `value`) VALUES (NULL, 'kingdom_selling', 'kingdom', NULL, {$row['id']}, 'taxes.kingdom_selling', 'taxes.kingdom_selling_desc', 5);") or die(mysql_error());
		
		mysql_query("INSERT INTO `kingdom_taxes` (`id`, `kingdom_id`, `name`, `hostile`, `neutral`, `friendly`, `allied`, `citizen`) VALUES (NULL, {$row['id']}, 'distributiontax', 5, 5, 5, 5, 5);") or die(mysql_error());
		
	}
	$regions = mysql_query("select * from regions") or die(mysql_error());
	mysql_query("truncate table region_taxes") or die(mysql_error());
	
	while ( $row = mysql_fetch_assoc( $regions ) ) 
	{
		
		mysql_query("INSERT INTO `taxes` (`id`, `tag`, `type`, `region_id`, `kingdom_id`, `name`, `description`, `value`) VALUES (NULL, 'region_selling', 'region', {$row['id']}, NULL, 'taxes.region_selling', 'taxes.region_selling_desc', 5);") or die(mysql_error());
		
		mysql_query("INSERT INTO `region_taxes` (`id`, `region_id`, `name`, `param1`, `hostile`, `neutral`, `friendly`, `allied`, `citizen`, `timestamp`) VALUES (NULL, {$row['id']}, 'valueaddedtax', NULL, 5, 5, 5, 5, 2, unix_timestamp())") or die(mysql_error());

	}
		
	$log->LogDebug('-> Committing...');
	mysql_query("commit");
	$log->LogDebug('-> Committed.');
	
}	catch (Exception $e)
{
		$log ->LogDebug( $e->getMessage() );
		$log->LogDebug('-> Rollbacking...');
		mysql_query("rollback");
		$log->LogDebug('-> Rollbacked');
}
?>
