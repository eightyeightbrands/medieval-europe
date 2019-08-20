<?php defined('SYSPATH') OR die('No direct access allowed.');

class User_Model extends Auth_User_Model
{

	protected $has_many = array('user_languages');
	protected $has_one = array('character');
	
	
	/**
	* Register or login user
	* @param array $data Array with user data
	* @param string $message message returned
	* @return boolean
	*/
	
	static function registerorloginuser( $data, &$message )
	{
		
		$message = '';
		
		if ( $data['referrersite'] == 'facebook' )
		{
			
			kohana::log('debug', '-> Searching for user with fb id: ' . $data['fb_id'] );			
			$local_user = ORM::factory('user') -> where ( 'fb_id', $data['fb_id'] ) -> find();	

			if ($local_user -> loaded == false )
			{
				kohana::log('debug', "-> User not found with FBID, Trying to search for user with email: [{$data['email']}]");
				$local_user = ORM::factory('user') -> where ( 'email', $data['email'] ) -> find();
			}

			if ($local_user -> loaded and is_null($local_user -> fb_id)  )
			{
				kohana::log('debug', '-> User found, marking it as FB.');
				$local_user -> fb_id = $data['fb_id'];
				$local_user -> external_id = $data['fb_id'];
				$local_user -> referrersite = 'facebook';
				$local_user -> save();
			}
		}
		else
		{
			kohana::log('debug', '-> Searching for user with email: ' . $data['email'] );
			$local_user = ORM::factory('user') -> where ( 'email', $data['email'] ) -> find();
		}
				
		// If exists, we update data and log them in
		
		if ( $local_user -> loaded )
		{
			kohana::log('debug', '-> User exists... logging in.');
			$rc = User_Model::postchecks( $local_user, $data['ipaddress'], $message );
			if ($rc == false)
				return false;
			
			$rc = User_Model::loginuser( $local_user, $data['ipaddress'], $message );
			if ($rc == false)
				return false;
			
		}
		else
		{			
			$newuser = null;
			$rc = User_Model::register( $data, $newuser, $message );			
			if ($rc == false)
				return false;
			
			$rc = User_Model::loginuser( $newuser, $data['ipaddress'], $message );
			if ($rc == false)
				return false;
		}
		
		return true;
		
	}
	
	/**
	* Login Validation Checks
	* @param obj $user User_Model
	* @param string $ipaddress
	* @param string $message in/out message
	* @return boolean true|false
	*/
	
	function postchecks( $user, $ipaddress, &$message )
	{
		
		kohana::log('debug', "------- POSTCHECKS PROCESS -------");
		
		// utente sospeso?		
			
		kohana::log('info', "-> Checking user {$user -> username}." );
		kohana::log('info', '-> Check: user is canceled or suspended?');
		
		if ( in_array( $user -> status, array('canceled', 'suspended', 'banned' ) ) )
		{
			
			if ( $user -> status == 'banned' )
			{
				kohana::log('info', '-> User is banned.');
				if ( $user -> bandate > time() )
				{
					$message = kohana::lang('user.login_userbanned', date("d-m-y H:i:s", $user -> bandate), $user -> reason );
					return false;
				}
				else
					Database::instance() -> query ( "
						update users
						set status = 'active', 
						bandate = null,
						reason = null
						where id = " . $user -> id ); 					
			}
			else
			{
				kohana::log('info', '-> User is suspended or canceled.');
				$message = kohana::lang('user.login_usersuspended', $user -> reason );
				return false;
			}
			
		}
	
		// proxy check
				
		if ( kohana::config( 'medeur.proxytest' ))			
		{						
			
			// se � in grace period, nessun check
			$character = ORM::factory('character')-> where ( 'user_id', $user -> id ) -> find();
			if( !is_null( $user -> gracedate) and $user -> gracedate > time() )
			{
				kohana::log('info', '-> Not checking proxy as user is in grace period.');
			}
			/*
			elseif (
				$character -> loaded 
				and 
				Character_Model::get_premiumbonus( $character->id, 'ipcheckshield' ) === true 
			)
			{
				kohana::log('info', '-> Skipping proxy check because char has ipcheckshield.');				
			}
			*/
			else
			{
				kohana::log('info', '-> Checking IP address for proxy...');
				$proxyscore = User_Model::get_proxyscore($ipaddress);
			
				// Se sta entrando con un proxy ed � gi� stato avvertito 3 giorni fa,
				// si blocca l' utente.			
				
				if ($user -> proxycheckdisabled == 'N' )
				{
					
					kohana::log('debug', "-> Checking user {$user -> username}. Is using a proxy: [{$proxyscore}]." );	
					if ( $proxyscore == 1 )
					{
						if (
							!is_null($user -> proxywarningdate) 
							and
							$user -> proxywarningdate < (time() - (5*24*3600))
						)
						{
							kohana::log('debug', "-> Suspending user.");

							/*							
							$user -> status = 'suspended';
							$user -> reason = 'Using Proxy';				
							$user -> save();
							$message = kohana::lang('user.login_usersuspended', $user -> reason );
							return false;
							*/
						}
						elseif ( is_null ($user -> proxywarningdate) )
						{
							
							// Evento 
							if ($character -> loaded )
							{
								Character_Event_Model::addrecord( 
								$character -> id,
								'normal', 
								'__events.proxywarning' . ';' .	$ipaddress,					
								'evidence'
								);
							}
							
							$user -> proxywarningdate = time();			
							$user -> save();
						}
					}
				}
			}
		}
		
		
		// utente bannato a livello IP?
		
		$bannedip = ORM::factory('admin_bannedip')
			-> where( array( 'ipaddress' => $ipaddress ) )->find();
		
		if ( $bannedip->loaded and $bannedip -> status == 'banned' )
		{
			$message = kohana::lang( 'user.bannedip' );
			return false;
		}
		
		// aggiorna il counter multicheck ed eventualmente sospendi
		
		self::multicheck( $user, $ipaddress );
		
		kohana::log('debug', "------- POSTCHECKS PROCESS END -------");
		
		return true;
	
	}
	
	/**
	* Multiaccounts check, if multi suspends the account
	* @param object $user user to check
	* @param string $ipaddress Shared IP
	* @return none
	*/
	
	function multicheck( $user, $ipaddress )
	{
	
		///////////////////////////////////////////////////////
		// Blocco gli utenti che hanno ip sharato e non sono giustificati.
		// - Se l' utente ha il bonus nobile non � controllato
		// - Se l' utente ha il campo multi = allowed, non � controllato
		// - Se l' utente ha una gracedate > adesso, non � controllato
		// Controllo solo se l' utente corrente non ha allowed
		// Se l' user ha il bonus noble, non lo controllo.
		////////////////////////////////////////////////////////
			
		
		$db = Database::instance();		
		
		$character = ORM::factory('character')
			-> where( 'user_id', $user -> id )->find();
					
		if ( $character -> loaded )
		{
		
			$bonuses = Character_Model::get_premiumbonuses( $character -> id );			
			kohana::log('info', '-> Checking if user ' . $user -> username . ' should be controlled for multi...');
			if ( 
				( 					
					Character_Model::get_premiumbonus( $character -> id, 'ipcheckshield' ) === false and 
					$user -> multi_status != 'allowed' and 
					kohana::config('medeur.multilogin_check') == true  and
					( is_null( $user -> gracedate) or $user -> gracedate < time() ) 
				)
			)
			{
				kohana::log('info', '-> Checking user ' . $user -> username . ' for multi...');
				
				
				
				$sql = "select u.username from users u, characters c
				where c.user_id = u.id 
				and   u.username != ? 
				and   u.ipaddress = ? 
				and   u.status =   'active' " ;
				
				$res = Database::instance() -> query( $sql, $user -> username, $user -> username  ) -> as_array();														
				// Se c'� un altro utente attivo con lo stesso IP e che ha un char...
				
				if ( count ($res) > 0 )
				{
					
					$multi['ipaddress'] = $user -> ipaddress ;
					$multi['username_1'] = $user -> username ;
					$multi['username_2'] = $res[0] -> username ;
										
					$res1 = $db -> query (
						"select counter from trace_userip_conflicts 
						where ipaddress = ?
						and username_1 = ?
						and username_2 = ?",
						$multi['ipaddress'], 
						$multi['username_1'], 
						$multi['username_2']
					) ;
					
					kohana::log('info', 'user: ' . $multi['username_1'] . ' has the same IP of: ' . $multi['username_2'] );
													
					// Incremento il counter.
					
					if ( $res1 -> count() > 0 )	
					{
						
						$c =  $res1[0] -> counter + 1;
						
						$sql = "
						replace into trace_userip_conflicts 
						set 	
						counter = " . $c . ", 
						ipaddress = ?,
						username_1 = ?,
						username_2 = ?";
					}
					else
						$sql = "
					  replace into trace_userip_conflicts 
						set 	
						counter = 1, 
						ipaddress = ?,
						username_1 = ?,						
						username_2 = ?";
					
					
					//kohana::log('debug', 'sql: ' . $sql ); 
					
					$db -> query( $sql,
						$multi['ipaddress'],
						$multi['username_1'],
						$multi['username_2']
					); 
					
					// se il count della coppia � maggiore di un certo parametro sospendi l'account
					// solo in certi giorni del mese (rapporto 15:15)
					
					$res2 = $db -> query ( 
					"
						select sum(counter) n 
						from trace_userip_conflicts
						where username_1 = ?
						and username_2 = ?",
						$multi['username_1'],
						$multi['username_2']
					);
					
					kohana::log('debug', 'Shared IP logins: ' . $res2[0] -> n );
					
					if ( $res2[0] -> n	>= 10 and date("d", time()) >= 15)
					{
						kohana::log('debug', '-> Suspending account: ' . $multi['username_1'] . '!');
					
						$db -> query ( 
							"update users set 
							status = 'suspended',
							reason = 'IP Address Conflict' 
							where username = ?",
								$multi['username_1']
							);
							
						// email
						
						$body = 'Utenti bloccati: ' . $multi['username_1'] . ' e ' . $multi['username_2'];						
						Utility_Model::alertadmins( 'Blocked users', $body );
					}
				}
			}
		}
	
	}
	
	/**
	* Log in the user
	* @param object user
	* @param string ipaddress
	* @param string message 
	* @return none
	*/

	function loginuser( $user, $ipaddress, &$message )
	{
	
		// solo gli admin possono entrare?
		
		kohana::log('debug', "------- LOGIN PROCESS -------");
		kohana::log('debug', '-> isadmin?: ' . Auth::instance() -> logged_in('admin') );
		kohana::log('debug', '-> isstaff?: ' . Auth::instance() -> logged_in('staff') );
		
		if ( 
			! Auth::instance() -> logged_in( 'admin' ) and 
			! Auth::instance() -> logged_in( 'staff' ) and 
			kohana::config( 'medeur.loginonlyadmin' ) )
		{
			$message = kohana::lang('user.login_onlyadmin'); 
			return false;
		}
		
		kohana::log ('info', '-> loginuser: ' . $user -> username . ' logged in succesfully.' ); 				
		
		$auth = new Auth();
		$auth -> force_login( $user );

		// Memorizzo in sessione l' user_id, il char_id e le statistiche di base del char				
		
		//kohana::log( 'info', '-> loginuser: Putting userid: ' . $user -> id . ' in session.');
		Session::instance() -> set( 'user_id', $user -> id );									
		
		$character = ORM::factory('character') -> where( 'user_id', $user -> id ) -> find();
		
		if ( $character -> loaded )
		{
			//kohana::log( 'info', '-> loginuser: Putting charid ' . $character -> id . ' in session...');
			Session::instance() -> set( 'char_id', $character -> id );
			
			// update game age stats
			Character_Model::modify_stat_d( 
			$character -> id, 
			'gameage',
			$character -> get_age(),
			null,
			null,
			true
			);		
	
		}
		
			// install security cookie
		kohana::log('debug', '-> Installing security cookie...');
		$val = cookie::get('me-login', 'cookiemissing');	
		if ( $val == 'cookiemissing' )
		{
			
			$cookie_params = array  ( 
					'name'   => 'me-login',
					'value'  => md5($user->id.$user->username),
					'httponly' => true,
					'expire' => 1800,
					'path'   => '/' );
				
			cookie::set($cookie_params);
			$val = md5($user->id.$user->username);
		}
		
		// salvo l' ora di log in (per controllo multi)
		
		$ul = new Trace_User_Login_Model();
		$ul -> user_id = $user -> id;
		$ul -> logintime = time();
		$ul -> ipaddress = $ipaddress;	
		$ul -> logincookie = $val;	
		$ul -> save();						
		
		//kohana::log( 'info', '-> loginuser: Setting language...');		
		
		User_Model::setcorrect_language( $ipaddress );
		
		// disabilito sleep automated
		
		//kohana::log( 'info', '-> loginuser: Disabling automated sleep...');		
		
		kohana::log('info', "-> Char: {$user -> character -> name}, setting sleepafteraction to 'N'");

		$user -> last_login = time();
		$user -> ipaddress = $ipaddress;				
		$user -> sleepafteraction = 'N' ;
		$user -> save(); 		
		
		
		if ( Auth::instance()->logged_in('admin') )					
			Session::instance() -> set ('isadmin', true );
			
		if ( Auth::instance()->logged_in('staff') )
			Session::instance() -> set ('isstaff', true );	
	
		kohana::log('debug', "------- LOGIN PROCESS END -------");
	
		return true;
	
	}
	
	/**
	* Register a user
	* @param array $data User Data
	* @param string $ipaddress ipaddress
	* @param object saved user	
	* @param string message 		
	* @return none
	*/
		
	function register( $data, &$user, &$message )
	{	
		
		kohana::log('debug', "------- REGISTER USER START -------");
		
		$user = ORM::factory('user');		
		$user -> username = User_Model::normalizeusername($data['username']);
		$passwordclear = $user -> username  . '_' . substr(md5(time()),1,5);
		$user -> password = $passwordclear;
		$user -> email = $data['email'];
		$user -> referrersite = $data['referrersite'];
		$user -> idnet_id = (isset($data['idnet_id']) ? $data['idnet_id'] : null);
		$user -> fb_id = (isset($data['fb_id']) ? $data['fb_id'] : null);
		$user -> external_id = (isset($data['external_id']) ? $data['external_id'] : null);	
		$user -> birthday = NULL;
		$user -> gender = $data['gender'];
		$user -> activationtoken = uniqid( null, true );
		$user -> status = $data['status'];	
		$user -> ipaddress = $data['ipaddress'];
		$user -> sleepafteraction = 'Y';		
		$user -> newsletter = 'Y';			
		$user -> tutorialmode = 'Y' ;
		$user -> created = time();        
		//kohana::log('debug', kohana::debug($user)); exit;
		
		$rc = $user -> save();
		
		if ( $user -> save() ) 
		{                    
			// Add login role
			Database::instance() -> query ("insert into roles_users values ( {$user -> id}, 1 )");
			
			// add referrer link if user exists
			
			if ( !empty($data['referreruser']) )
			{
				$referreruser = ORM::factory('user', $data['referreruser']);
				if ($referreruser -> loaded )
				{		
					$referral_link = ORM::factory('user_referral');					
					$referral_link -> user_id = $data['referreruser'];
					$referral_link -> referred_id = $user -> id;
					$referral_link -> coins = 0;					
					$referral_link -> save();
				}
				
			}			
			
			// send email				  
			
			if ( !in_array( $user -> referrersite, array( 'facebook', 'bbrelax') ) )
			{
				$subject = Kohana::lang('user.register_emailsubject');
				$body    = Kohana::lang('user.register_emailbody', 
					$user -> username, $passwordclear,
					'https://' . $_SERVER['SERVER_NAME'] . "/index.php/user/activate/".$user -> id."/".$user->activationtoken);
				$to      = $user -> email;
				$result = Utility_Model::mail( $to, $subject, $body );
			}
			
		}
		else 
		{
			$message = kohana::lang('user.error-');
			return false;
		}
		
		kohana::log('debug', "------- REGISTER USER END -------");
		return true;
	
	}

	/**
	* Set correct language based on IP Geolocation
	* @param string $ipaddress IP Address
	* @return none
	*/
	
	static public function setcorrect_language( $ipaddress )
	{
		
		// only if the cookie does not exist, set the language
		
		$val = cookie::get('lang', 'cookiemissing');
		if ($val != 'cookiemissing')
			return;

		$language = 'en_US';
		
		// Find out Geo Location
				
		require_once(dirname(realpath(__FILE__)) . "/../libraries/vendors/GeoIP/geoip.inc");
		$gi = geoip_open(dirname(realpath(__FILE__)) . "/../libraries/vendors/GeoIP/GeoIP.dat", GEOIP_MEMORY_CACHE);	
		$country = geoip_country_code_by_addr($gi, $ipaddress);
		$countrylowercase = strtolower( $country );		
		geoip_close( $gi );
		
		//kohana::log('info', "-> user logged from country: {$countrylowercase}");
		
		if ( $countrylowercase == 'it' )
			$language = 'it_IT';
		
		if ( $countrylowercase == 'fr' )
			$language = 'fr_FR';
		
		if ( $countrylowercase == 'ro' )
			$language = 'ro_RO';
		
		if ( $countrylowercase == 'bg' )
			$language = 'bg_BG';		
			
		if ( $countrylowercase == 'de' )
			$language = 'de_DE';
		
		if ( $countrylowercase == 'ru' )
			$language = 'ru_RU';
		
		if ( $countrylowercase == 'tr' )
			$language = 'tr_TR';	
		
		if ( $countrylowercase == 'pt' )
			$language = 'pt_PT';
		
		//kohana::log('info', "-> Language set to: {$language}");
		
		User_Model::change_language( $language );		
		
	}
	
	static public function change_language( $lang = 'en_US' )
	{
		//kohana::log('info', "-> Setting language to: {$lang}");
		$user = Auth::instance() -> get_user();	
		
		if ( $user )
		{
			$user -> language = $lang;
			$user -> save();
		}
		
		$cookie_params = array  ( 
			'name'   => 'lang',
			'value'  => $lang,
			'httponly' => true,
			'domain' => '',
			'path'   => '/',
		);

		Cookie::set($cookie_params);		
		
	}
	
	/**
	* Normalize username
	* @param string $name username
	* @return $name normalized name
	*/
	
	static function normalizeusername( $name )
	{	
		$name = str_replace('-','',$name);
		$name = str_replace(' ','',$name);
		$name = str_replace('&','',$name);
		$name = str_replace('/','',$name);
		$name = str_replace(':','',$name);
		$name = str_replace('=','',$name);
		$name = str_replace('_','',$name);
		$name = substr(strtolower($name), 0, 25);
		return $name;
	}
	
	/**
	* Builds horizontal menu for User
	* @param string $action option selected
	* @return $html Html
	*/
	
	function get_account_submenu( $action )
	{
	
		$submenu = array( 
			'user/profile/' . $this -> id => 
				array(
				'name' => kohana::lang('user.profile'), 
				'htmlparams' => array( 'class' =>( $action == 'profile' ) ? 'selected' : '' )),
			'user/changepassword/' . $this -> id => 
				array(
				'name' => kohana::lang('user.changepassword'), 
				'htmlparams' => array( 'class' =>( $action == 'changepassword' ) ? 'selected' : '' )),	
			'user/configure/' . $this -> id => 
				array(
				'name' => kohana::lang('user.configure'), 
				'htmlparams' => array( 'class' =>( $action == 'configure' ) ? 'selected' : '' )),		
			'user/bonuspurchases/' => 
				array(
				'name' => kohana::lang('user.bonuspurchases'), 
				'htmlparams' => array( 'class' =>( $action == 'bonuspurchases' ) ? 'selected' : '' )),
			'user/purchases/' => 
				array(
				'name' => kohana::lang('user.purchases'), 
				'htmlparams' => array( 'class' =>( $action == 'purchases' ) ? 'selected' : '' ))
				);
		return $submenu;		
	}		
	/*
	* Determina se un ip � un proxy
	* @param str $ipaddress Indirizzo IP
	* @return float $score Punteggio (0-1)
	*/
	
	function get_proxyscore( $ipaddress )
	{
						
		kohana::log('debug', "-> Checking IP: [{$ipaddress}] for proxy...");
		
		$rec = ORM::factory('ipaddress_proxy') -> where( 'ipaddress', $ipaddress ) -> find();
		
		// cache: 6 mesi
		if (
			$rec -> loaded == false 
			or 
			$rec -> timestamp < (time() - (6*30*24*3600)) 
		)
		{
			kohana::log('debug', '-> Contacting IPintel server...');
						
			$calls = ORM::factory('ipaddress_proxy_call')
			-> where( 'date' , date("Y-m-d", time()) )
			-> find();

			if ($calls->calls >= 500 )
			{
				kohana::log('info', '-> Exceeded limit for todays calls. exiting.');
				return 0;
			}
			
			$calls -> calls +=1;
			$calls -> date = date("Y-m-d", time());
			$calls -> save();
			
			$url = "https://check.getipintel.net/check.php?ip=" . $ipaddress . "&contact=" . kohana::config('medeur.adminemail') . "&flags=m";
			$ch = curl_init();		
			curl_setopt ($ch, CURLOPT_URL, $url );
			curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 0);
			curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, false);
			$rawdata = curl_exec($ch);			
			
			kohana::log('debug', "-> IP: [{$ipaddress}] proxy score: {$rawdata}");
			
			$rec -> ipaddress = $ipaddress;
			$rec -> score = $rawdata;
			$rec -> timestamp = time();
			$rec -> save();
		}
		else
		{
			kohana::log('debug', "-> IP Address {$ipaddress} is cached.");
			
		}
		return $rec -> score;
	}
	
	
	/**
	* Torna i linguaggi conosciuti da un utente
	* @param user
	* @return array $languages
	* primary => (contiene il linguaggio primario)
	* 1 => linguaggio n. 1 (primario)
	* 2 => linguaggio n. 2 
	* ecc.
	*/
	
	public static function get_knownlanguages( $user )
	{
		
		foreach ($user -> user_language as $language)
			$languages[$language -> position ] = $language -> language;
			
		$languages['primary'] = $languages[1];
		
		return $languages;
	}
		
	public function modifyemail( $user, $newemail )
	{
		
		Database::instance() -> query("set autocommit = 0");
		Database::instance() -> query("start transaction");
		Database::instance() -> query("begin");
		
		try 
		{
		
			$user -> email = $newemail;
			$user -> save();
					
			$dbforum = Database::instance('forum');		
			$dbforum -> query ("update smf_members set email_address = ? where member_name = ?",
				$newemail, $user -> username );		
			kohana::log('info', '-> changecharemail ***commit***.');
			Database::instance() -> query("commit");
			
		} catch (Kohana_Database_Exception $e)
		{
			$message = $e -> getMessage();
			kohana::log('error', 'Error while modifying email: ' . $message );
			Database::instance() -> query("rollback");						
			return false;
		}	
		
		Database::instance() -> query("set autocommit = 1");
		
	}

	function has_role( $user, $roletobechecked )
	{
		$has_role = false;
		
		foreach ( $user -> roles as $role )
			if ( $role -> name == $roletobechecked )
				$has_role = true;
		return $has_role;
		
	}
	
}
