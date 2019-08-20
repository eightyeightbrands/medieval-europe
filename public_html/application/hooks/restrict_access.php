<?php defined('SYSPATH') OR die('No direct access allowed.');

function Restrict_Access()
{	

	/////////////////////////////////////////////////////////////////
	// le seguenti pagine possono essere accedute senza autenticarsi
	/////////////////////////////////////////////////////////////////
	
	$db = Database::instance();
	$db -> query("select '--restrictaccess--'");
	
	$okdirectaccess = array(
		'/', 
		'affiliate/login',
		'affiliate/logout',
		'affiliate/register',
		'affiliate/forgotpassword',
		'banner/display',
		'batch/watchdog',
		'batch/mergeregions',
		'batch/consumeitems',
		'batch/cleanupdatabase',
		'batch/tempproc',
		'batch/givereferralcoins',
		'batch/rechargebasicresources',
		'batch/computestats',
		'batch/deleteavatar',
		'batch/checkpremiumexpiration',
		'batch/sendstarvingemail',
		'batch/mergeregions',
		'batch/reduceintoxicationlevel',
		'batch/computenativeattack',
		'batch/getitemaverageprices',
		'batch/initquest',
		'batch/give_daily_revenues',
		'batch/mergekingdoms',
		'batch/splitkingdoms',
		'batch/sendretentionemail',
		'batch/computekingdomsactivity',
		'batch/respawnnpcs',
		'easteregg/initialize',
		'character/complete_action',
		'jqcallback/bbcodepreview',
		'jqcallback/get_servertime',
		'character/change_language',
		'newchat/init',
		'page/readnews',		
		'page/display/homepageframe',
		'page/display/custom_404',
		'page/display/notauthorizedpage',
		'page/display/toplists',		
		'page/display/terms-of-use',
		'page/display/game-rules',
		'page/display/privacy-and-cookies',
		'page/display/unsubscribe-nok',
		'page/display/unsubscribe-ok',				
		'page/index',
		'page/jail',
		'page/serverinfo',
		'paymentlistener/coinpayments' ,	
		'paymentlistener/gourl',
		'quests/tutorialentry',
		'test/test',		
		'test/patch',		
		'toplist/reward',
		'user/activate',
		'user/login',
		'user/fb_login',
		'user/google_login',
		'user/relaxbb_login',
		'user/y8_login',
		'user/register',
		'user/logout',
		'user/registered',
		'user/resendpassword',			
		'user/resendvalidationtoken',		
		'user/unsubscribe',
	);
	
	/////////////////////////////////////////////////////////////////
	// array di pagine che è possibile navigare senza char
	/////////////////////////////////////////////////////////////////
	
	$okwithoutchar = array_merge ( 
		$okdirectaccess, 
		array (
			'user/logout',
			'character/create',
			'jqcallback/generatename', 
			'jqcallback/get_kingdominfo',		
		)
	);	

	/////////////////////////////////////////////////////////////////
	// controllo se l' utente è autorizzato ad accedere alla pagina
	// fix: url::current() ritorna anche i parametri, per gestire 
	// una lista di controller/function è
	// necessario usare l' helper uri.
	/////////////////////////////////////////////////////////////////
	
	$controller = Router::$controller;
	$method = Router::$method;
	$parameters = implode ('/', Router::$arguments );
	
	if ( $controller == 'page' and $method == 'display' )
		$action = $controller . '/' . $method . '/' . $parameters;
	else
		$action = $controller . '/' . $method ;
	
	kohana::log('debug', '-> Restrict Access: Action called: [' . $action . ']' );	
	$user = Auth::instance() -> get_user();		
	kohana::log('debug', '-> Restrict Access: Checking if user is suspended or canceled.');
	if ($user and ($user -> status == 'suspended' or $user -> status == 'canceled') ) 
		Auth::instance() -> logout( );
	
	if ( !in_array( $action, $okdirectaccess) )
	{		
		kohana::log('debug', '-> Restrict access: to access this action, the char must be logged.');
		
		if ( ! Auth::instance() -> logged_in() )
		{
			kohana::log('debug', '-> Restrict access - action: ' . $action . ' - user is not logged in, redirecting to login screen...' );
			url::redirect('page/display/notauthorizedpage');
		}
	}
	
	/////////////////////////////////////////////////////////////////
	// Se l' utente è autenticato l'oggetto user esiste.
	// Controlliamo che l' utente abbia un char valido associato 
	// altrimenti viene rediretto alla creazione utente (se non è un affiliato)		
	/////////////////////////////////////////////////////////////////
	
	//$db = Database::instance();
	//$db -> query('select 2' );
	
	
	kohana::log('debug', '-> Restrict Access: Retrieving char.');	
	$char = Character_Model::get_data( Session::instance() -> get('char_id') );  	
	kohana::log('debug', "-> Restrict Access: Action: {$action}, Char is null? " . is_null($char));
	// se la request non è nell' elenco degli indirizzi a cui si puo' accedere senza char e il 
	// personaggio non è ancora stato creato, ridirezioniamo alla pagina di creazione carattere.	
		
	if ( !in_array( $action, $okwithoutchar) and is_null($char) ) 
	{			
		kohana::log('debug', '-> Restrict Access: Char is not loaded, redirecting to char creation...' ); 
		Session::instance() -> set( 'char_id', 0 );
		url::redirect('/character/create');
	}

	/////////////////////////////////////////////////////////////////
	// Se siamo arrivati qui, l' utente è loggato ed ha un char.
	// Controlliamo lo stato
	/////////////////////////////////////////////////////////////////
	
	if ( !is_null( $char ) )
	{
		kohana::log('debug', '-> Char is logged.');
		$lastactiontime = Character_Model::get_lastactiontime_d( $char -> id );
		
		// salva il dato su DB solo se sono passati + di 15 minuti
		
		if ( time() - $lastactiontime > Kohana::config( 'medeur.maxidletime' ))
		$db -> query( "update characters set lastactiontime = unix_timestamp() where id = " . $char -> id );
		
	My_Cache_Model::set ( '-charinfo_' . $char -> id . '_lastactiontime', time() ); 		
		
		Character_Model::handle_char_specialstatus();
	}

	kohana::log('debug', '-> Displaying page.');
	$db -> query("select '--restrictaccess--'");

	
}

// Aggiungo l'evento prima di istanziare un controllore
if ( ! Event::has_run( 'system.pre_controller' ) )
	Event::add('system.pre_controller', 'Restrict_Access');

?>
