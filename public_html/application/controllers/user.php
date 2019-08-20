<?php defined('SYSPATH') OR die('No direct access allowed.');

class User_Controller extends Template_Controller
{		
	public $template = 'template/gamelayout';	
	
	/*	
	 * Normal user registration
	 * @param none
	 * @return none
	*/
	
	function register()
	{
		
		// GOOGLE SSO		
		$google = new Google_Bridge_Model();	
		
		// FACEBOOK SSO
		$fb = new Facebook_Bridge_Model();
		
		$view = new View('page/home');		
		$this -> template = new View('template/homepage');		
		$sheets = array( 'home' => 'screen',);	 	
		$this -> template -> content = $view; 
		$this -> template -> sheets = $sheets;  
		$this -> auth = new Auth();      
			
		$form = array(
			'username' => '',
			'email' => '', 
			'referreruser' => '',
			'accepttos' => false,
			'newsletter' => true,
		);
		
		//if a post exists, validate and process input
		
		if ($_POST)
		{
			
			$post = Validation::factory($this -> input -> post())
				-> pre_filter('trim', TRUE)
				-> add_rules('username','required', 'alpha_numeric', 'length[5,20]')
				-> add_rules('email', 'required', 'email', 'length[1,60]')
				-> add_rules('referral_id', 'numeric');

			$post -> add_callbacks('username', array($this, '_unique_username'));
			$post -> add_callbacks('email', array($this, '_unique_email'));
			$post -> add_callbacks('email', array($this, '_fake_email'));
			$post -> add_callbacks('captchaanswer', array($this, '_checkcaptcha'));
			$post -> add_callbacks( 'referral_id', array( $this, '_c_referral_id' ));
			
			$post['birthday'] = null;
			$post['gender'] = null;
			$post['status'] = 'new';
			$post['ipaddress'] = $this -> input -> ip_address();
			$post['request_ids'] = $this -> input -> get('request_ids');
			$post['referrersite'] = null;
			
			if ( $post -> validate() )
			{
				$rc = User_Model::registerorloginuser( $post, $message );			
				if ( $rc == false )
					Session::set_flash( 'user_message', "<div class=\"error_msg\">" . $message . "</div>");										
				else
				{
					header('P3P: CP="NOI ADM DEV COM NAV OUR STP"');
					url::redirect( 'boardmessage/index/europecrier');						
				}
			}
			else
			{      
				$errors = $post -> errors('form_errors');						
				$view -> errors = $errors;			
				$form = arr::overwrite( $form, $post -> as_array());	
			}
		}
		// else, redirect to home
		else
		{
			url::redirect('/');			
		}
		

		$view -> form = $form;		
		$view -> facebook_login_url = $fb -> get_login_url();
		$view -> google_login_url = $google -> get_google_login_url();
		
	}
	

	/*
	 * Validazione utente, verifica che il token passato sia uguale a quello
	 * associato all' utente	 
	 */

	public function activate($user_id = null, $activationtoken = null)
	{
		$this -> template = new View('template/homepage');
		$view = new View('user/activate');
		$sheets = array('home' => 'screen');
	 	
		$this->template = new View('template/homepage');
		$this->template->sheets = $sheets;
		
		$user = ORM::factory('user')->where( array( 
		  'id' => $user_id, 
		  'activationtoken' => $activationtoken,
		  'status' => 'new'
		   ))->find();

		//kohana::log('debug', kohana::debug( $user ));
  
		if ( ! $user->loaded  )
		{
			$view->message = Kohana::lang('user.activate_userortokennotfound', 
				html::anchor( "/user/resendvalidationtoken/", Kohana::lang('user.activate_resendtoken') ));
			$this->template->content = $view;		
			return;
		}  
    			
		$user->status = 'active' ;
		
		if ( $user->save() )
			$view->message = Kohana::lang('user.activate_useractivated', html::anchor('/', kohana::lang( 'menu_notlogged.login' ) ) );
		else
			$view->message = Kohana::lang('user.activate_validationerror');

		$this->template->content = $view;
		$this->template->sheets = $sheets;
		return;
	}


	/**
	 * Reinvia il token di validazione per la email specificata. 
	 * @param none
	 * @return none
    */

	public function resendvalidationtoken()
	{
		
		$this -> template = new View('template/homepage');		
		$sheets = array('home' => 'screen');	 	
		$view = new View('user/resendvalidationtoken');
		
		$form = array(			
			'email' => '',  
		);				
		
		// copio errors da form cosi' gli errori matchano le chiavi della form
		
		$errors = $form;		
		
		if ( $_POST )
		{       
		
			$post = Validation::factory($_POST)
			->pre_filter('trim', TRUE)
			->add_rules('email', 'required', 'email', 'length[1,30]');
					
		  
		  if ($post->validate() )
		  {

				$user = ORM::factory('user')->where( array( 
					'email' => $this -> input -> post('email'),
					'status' => 'new'
				)) -> find();
				
				if ($user -> loaded)
				{
					if (!in_array( $user -> referrersite, array( 'facebook', 'bbrelax') ) )
					{
						// email
						$subject = Kohana::lang('user.resendvalidationtoken_emailsubject');				
						$body    = sprintf (Kohana::lang('user.resendvalidationtoken_emailbody'),     
						'https://' . $this->input->server('SERVER_NAME') . "/index.php/user/activate/".$user->id."/".$user->activationtoken);				
						$to = $post['email'];				
						$result = Utility_Model::mail( $to, $subject, $body );	
						
						if ( $result ) 
						{                      
							Session::set_flash('user_message', "<div class=\"info_msg\">".Kohana::lang('user.resendvalidationtoken_success')."</div>" );
						}
						else
						{
								Session::set_flash('user_message', "<div class=\"error_msg\">".Kohana::lang('user.resendvalidationtoken_error')."</div>" );
						}        
					}  
					else
					{
						Session::set_flash('user_message', "<div class=\"info_msg\">".Kohana::lang('user.resendvalidationtoken_noneedtobevalidated')."</div>" );
						
					}
				}
				// Nessun utente � stato trovato con l' email specificata
				else
				{	
					Session::set_flash('user_message', "<div class=\"error_msg\">".Kohana::lang('user.resendvalidationtoken_nouserfound')."</div>" ); 
				}
  
		  }
		  else
		  {      
				$errors = $post->errors('form_errors');                             
				$view -> bind('errors', $errors);        			
		  }
		
		}
		
		
		$view -> bind('form', $form);
		$this -> template -> content = $view;
		$this -> template -> sheets = $sheets;

	} 

	/*
	 * Reinvia la password per la email specificata. 	 
	 */

	public function resendpassword()
	{		
		
		// Imposto il template e gli stylesheets
		
		$this -> template = new View('template/homepage');
		$sheets = array('home' => 'screen');

		$view = new View('user/resendpassword');
		$this -> template -> content = $view;
		$this -> template -> sheets = $sheets;

		$form = array('email' => '');				
			
		// copio errors da form cosi' gli errori matchano le chiavi della form
		
		$errors = $form;
		
		if ( $_POST )
		{
			
			$this -> auth = new Auth();      
			$post = Validation::factory($_POST)
				-> pre_filter('trim', TRUE)
				-> add_rules( 'email', 'required', 'email', 'length[1,30]' );
				
			if ($post->validate() )
			{
				
				$user = ORM::factory('user') -> 
					where( array( 
						'email' => $this -> input -> post('email')
				)) -> find();

				//var_dump($user);exit;

				if ( $user->loaded )
				{

					//print kohana::debug( "previous user password: " . $user->password );
					$newpassword_clr = substr(md5(time()),1,5);
					kohana::log( 'info', "user: " . $user -> username . " new password clear: " . $newpassword_clr );          
					$user -> password = $newpassword_clr;                             
					//print kohana::debug("new user password: " . $user->password);

					$result_save = $user -> save();

					// email
					
					$subject = Kohana::lang('user.resendpassword_emailsubject');
					$body    = sprintf (Kohana::lang('user.resendpassword_emailbody'), $newpassword_clr, $user->username );
					$to      = $post['email'];					
					$result_email = Utility_Model::mail( $to, $subject, $body );
								
					Session::set_flash('user_message', "<div class=\"info_msg\">".Kohana::lang('user.resendpassword_success')."</div>");         
					
				}  
				
				// Nessun utente � stato trovato con l' email specificata
			
				else
				{
					Session::set_flash('user_message', "<div class=\"error_msg\">".Kohana::lang('user.resendpassword_nouserfound')."</div>");          
				}
			}
			else
			{      							
				$errors = $post -> errors('form_errors');				
				$view -> errors = $errors;
				
			}
		  
			$form = arr::overwrite($form, $post->as_array());
		  
		}
		
		$view -> form = $form;
	
		}

	
	/**
	 * Autenticazione utente - Normale login
	 * @param none
	 * @return none
	*/

	public function login( $homepage = 'classic' )
	{
		
		// FACEBOOK SSO
		$fb = new Facebook_Bridge_Model();
		// GOOGLE SSO		
		$google = new Google_Bridge_Model();		

		$message = '';				
		$this -> template = new View('template/homepage');
		$view = new View('page/home');			
		$sheets = array( 'home' => 'screen' );
		$db = Database::instance();				
		$this -> template -> sheets = $sheets;  
		$form = array( 
			'username' => '', 
			'password' => '', 
			'email' => '',
			'referreruser' => '',
		);
		
		$user = $character = null;
		
		// POST: Normale login
		
		if ( $this -> input -> post() )
		{
			
			$post = Validation::factory($_POST)
				 -> pre_filter('trim', TRUE)
				 -> add_rules('username', 'required')				
				 -> add_rules('password', 'required');
		
						
			if ($post -> validate() )
			{
				$post = new Validation( $this -> input -> post() );
				$username = $this->input->post('username');
				$password = $this->input->post('password');
				$this -> auth = new Auth();
				$error = null;			
				
				// L' utente esiste?
				
				kohana::log('info', '-> Check: user: [' . $username . '], exists?');			
				// si può usare l' username.
				$user = ORM::factory( 'user' ) -> where ( 'username', $username) -> find();
				
				if ( !$user -> loaded )
				{
					$error = 'user.login_usernotfound';
					Session::set_flash( 'user_message', "<div class=\"error_msg\">".Kohana::lang( $error )."</div>");
				}			
				
				if ( is_null( $error ) )
				{
					// check user and password
					
					$rc = $this -> auth -> login( $user, $password );						
					kohana::log('debug', "-> Return from aut: {$rc}");
					if ( $rc )
					{
						
						$data['referrersite'] = $user -> referrersite;
						$data['username'] = $username;		
						$data['email'] = $user -> email;
						$data['ipaddress'] = $this -> input -> ip_address();
						$data['fb_id'] = 'normal';
						
						$rc = User_Model::registerorloginuser( $data, $message );									
						
						if ( $rc == false )
							Session::set_flash( 'user_message', "<div class=\"error_msg\">" . $message . "</div>");										
						else
						{
							header('P3P: CP="NOI ADM DEV COM NAV OUR STP"');
							url::redirect( 'region/view');						
						}
						
					}
					else
					{
						kohana::log( 'debug', "-> Password [{$password}] is wrong." ); 
						Session::set_flash( 'user_message', "<div class=\"error_msg\">".Kohana::lang("user.incorrectpassword")."</div>");			
					}
					
				}
				
			}	
			else
				Session::set_flash( 'user_message', "<div class='error_msg'>".kohana::lang("user.login_autherror")."</div>");
		}
		else
		{
			kohana::log( 'debug', '-> Called login, but POST is null.' ); 			
			//kohana::log( 'debug', kohana::debug( $this -> input -> post() ) );
			url::redirect( '/' ); 			
		}
		
		kohana::log('debug', '-> Redirecting to view...' );
		$view -> facebook_login_url = $fb -> get_login_url();
		$view -> google_login_url = $google -> get_google_login_url();
		$view -> referrerurl = $this -> input -> post('referral');
				
		$view -> form = $form;
		
		$this -> template -> content = $view;
		$this -> template -> sheets = $sheets;    
		
	}
	
	/**
	* Y8.com SSO
	* @param none
	* @return none
	*/
	
	function y8_login()
	{
	
		$view = new View('page/home');
		$sheets = array( 'home' => 'screen' );
		$db = Database::instance();				
		$this -> template -> sheets = $sheets;  
		$message = '';
		$access_token = null;
		
		$appID = "54d65aa2694862f28f003b6c";
		$appSecret = "f7d251525ab35037d34429a7465215df92f10a1f820511163f8dbc6ee8fe0a53";			
		$this -> auth = new Auth();
		$user = $character = null;
		
		if (isset($_GET['code'])) {
			$idCode = $_GET['code'];
		} else {
			$idCode = 0;
		}
		if (isset($_GET['state'])) {
			$idState = $_GET['state'];
		} else {
			$idState = 0;
		}
	
		//Reset playerID information
		$PlayerUserID = 0;
		
		//Check loading status
		$loadGame = 0;
	
		//Affiliate information
		$referrersite = 'y8.com';
		
		//Include all callback options & Get token
		require_once(dirname(realpath(__FILE__)) . "/../libraries/vendors/IdNet/classes/CallHelper.php");		
		$result = CallHelper::getCurl($appID, $appSecret, $idCode);
		//Get token
		
		$token_info = json_decode($result, true);
		if (isset($token_info["error"])) {
			;
		} else {
			$access_token = $token_info["access_token"];
		}
		
		$userdata = CallHelper::getUserDataCurl($access_token);			
		$data['username'] = $userdata['nickname'];
		$data['email'] = $userdata['email'];
		if ( empty( $userdata['gender'] ) )
			$data['gender'] = '';
		else				
			$data['gender'] = ($userdata['gender'] == 'male' ? 'm' : 'f') ;								
		$data['birthday'] = null;
		$data['password'] = md5(time());
		$data['newsletter'] = 'N' ;						
		$data['idnet_id'] = $userdata["pid"];				
		$data['external_id'] = $userdata["pid"];				
		$data['fb_id'] = 'y8';
		$data['referrersite'] = 'y8.com' ;
		$data['referreruser'] = null;
		$data['status'] = 'active';
		$data['activationtoken'] = null;
		$data['created'] = time();
		$data['ipaddress'] = $this -> input -> ip_address();
		$data['tutorialmode'] = 'Y';
		
		$rc = User_Model::registerorloginuser( $data, $message );									
		if ( $rc == false )
			Session::set_flash( 'user_message', "<div class=\"error_msg\">" . $message . "</div>");										
		else
		{
			header('P3P: CP="NOI ADM DEV COM NAV OUR STP"');
			url::redirect( 'boardmessage/index/europecrier');						
		}
		
		$view -> referrerurl = $this -> input -> post('referral');
		$this -> template -> content = $view;
		$this -> template -> sheets = $sheets;  	
		
	}
	
	/**
	* Google SSO
	* @param none
	* @return none
	*/
	
	function google_login()
	{
		
		$view = new View('page/home');
		$sheets = array( 'home' => 'screen' );
		$this -> template -> sheets = $sheets;  		
				
		kohana::log('debug', '-> Google login called' );		
		
		// Accertati che non siano in corso attacchi di request forgery, e che l'utente
		// che invia la richiesta di connessione sia quello previsto.
				
		kohana::log('debug', '-> Querying google...' );
		//kohana::log('debug', kohana::debug($this -> input -> get()));
		
		$google = new Google_Bridge_Model();
		$service = $google -> get_service();
		$client = $google -> get_client();
		
		kohana::log('debug', '-> Authenticating...');
		
		$client -> authenticate($this -> input -> get('code'));
		//kohana::log('debug', kohana::debug( $client ));
		$accesstoken = $client->getAccessToken();
		Session::set('googleaccesstoken', $accesstoken );
		
		// get user data
		$info = $service -> people -> get ('me');		
		$emails = $info -> getEmails();
		
		// Leggo referrer dal cookie
		
		$val = cookie::get('referraldata', 'cookiemissing');					
		parse_str($val, $values);
		
		kohana::log('debug', kohana::debug($values));
		
		$data = array();			
		$data['username'] = 'g_' . substr(uniqid('', true), 1, 10);
		$data['email'] = $emails['0'] -> value;
		$data['birthday'] = null;
		$data['gender'] = ($info -> getGender() == 'male') ? 'm' :  'f' ;
		$data['newsletter'] ='Y';
		$data['fb_id'] = null;
		$data['external_id'] = $info -> getId();
		$data['password'] = md5(time());				
		$data['referral_id'] = 0;
		$data['status'] = 'active';
		$data['activationtoken'] = null;
		$data['created'] = time();
		$data['ipaddress'] = $this -> input -> ip_address();
		$data['tutorialmode'] = 'Y';
		$data['referrersite'] = 'google';
		
		if (isset($values['referreruser']) )
			$data['referreruser'] = $values['referreruser'];
		else
			$data['referreruser'] = null;
		
		$rc = User_Model::registerorloginuser( $data, $message );									
		if ( $rc == false )
		{

			Session::set_flash( 'user_message', "<div class=\"error_msg\">" . $message . "</div>");
			url::redirect('/');
		}
		else
		{
			header('P3P: CP="NOI ADM DEV COM NAV OUR STP"');
			//kohana::log('info', '-> Redirecting to europectrier...');
			url::redirect( 'boardmessage/index/europecrier');						
		}				
	}			
	
	/**
	* BB-Relax SSO
	* @param none
	* @return none
	*/
	
	function relaxbb_login()
	{
		
		$view = new View('page/home');
		$sheets = array( 'home' => 'screen' );
		$db = Database::instance();				
		$this -> template -> sheets = $sheets;  
		$message = '';

		// Import BBrelax SDK
		
		require_once('application/libraries/vendors/bbrelax/iplayer.php');
		$iplayer = IPlayer::handle_request();
		
		$form = array(
				'username' => '',			
				'password' => ''	
		);
		
		$this -> auth = new Auth();
		$user = $character = null;
		
		// load user.
		kohana::log('debug', '-> BBRelax login: trying to fetch user.' );
		
		try{
			$user_bbrelax = $iplayer -> user_info();
		}catch( IPlayer_Exception $e )
		{
			kohana::log('error', '-> BBRelax exception: ' . $e -> getMessage() );
			$user_bbrelax = null;
			die( 'An error has occurred: ' . $e -> getMessage());			
		}
		
		$data['username'] = $user_bbrelax['nickname'];
		$data['email'] = $user_bbrelax['username'];
		$data['gender'] = $user_bbrelax['gender'];
		$data['birthday'] = strtotime($user_bbrelax['birthday']);
		$data['password'] = md5(time());
		$data['newsletter'] = 'N' ;
		$data['referrersite'] = 'iplayer.org';
		$data['referreruser'] = null;
		$data['fb_id'] = 'relax';
		$data['status'] = 'active';
		$data['activationtoken'] = null;
		$data['created'] = time();
		$data['ipaddress'] = $this -> input -> ip_address();
		$data['tutorialmode'] = 'Y';
		
		$rc = User_Model::registerorloginuser( $data, $message );									
		if ( $rc == false )
			Session::set_flash( 'user_message', "<div class=\"error_msg\">" . $message . "</div>");
		else
		{
			header('P3P: CP="NOI ADM DEV COM NAV OUR STP"');
			url::redirect( 'boardmessage/index/europecrier');						
		}
			
	}
	
	/**
	* Facebook SSO
	* @param none
	* @return none
	*/
	
	function fb_login()
	{		
			
		// Prendi info dell utente...
		
		$graph_url = "https://graph.facebook.com/me?access_token=" . $_REQUEST['access_token'];		
		$ch = curl_init();		
		curl_setopt ($ch, CURLOPT_URL, $graph_url );
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 0);
		curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, false);
		$rawdata = curl_exec($ch);
			
		$user = json_decode($rawdata);
		kohana::log('info', '----- FB User -----');
		kohana::log('info', kohana::debug($user)); 
		
		if (isset($user -> error)) 
		{
			die ("Could not get a valid token from Facebook.");
		}
		else
		{
			if (isset($user -> email))
				$email = $user -> email;
			else
				$email = uniqid('', true) . '@nowhere.com';
			
			$data = array();			
			$data['username'] = 'f_' . substr(uniqid('', true), 1, 10);
			$data['email'] = $email;
			$data['birthday'] = null;
			if (isset($user -> gender))
				$data['gender'] = $user -> gender == 'male' ? 'm' :  'f' ;
			else
				$data['gender'] = '';
			$data['newsletter'] ='Y';
			$data['referrersite'] = 'facebook';
			$data['fb_id'] = $user -> id;
			$data['external_id'] = $user -> id;
			$data['password'] = md5(time());				
			$data['referral_id'] = 0;
			$data['status'] = 'active';
			$data['activationtoken'] = null;
			$data['created'] = time();
			$data['ipaddress'] = $this -> input -> ip_address();
			$data['tutorialmode'] = 'Y';
			$data['request_ids'] = $this -> input -> get('requests_id');
			
			// Leggo referrer dal cookie
			
			$val = cookie::get('referraldata', 'cookiemissing');					
			parse_str($val, $values);
			
			
			// check requestids from facebook
			// se c'� una request_ids � un invito 
			// quindi � complementare al referreruser
			// che arriverebbe da visita diretta.
			
			if (!is_null($data['request_ids']))
			{
				kohana::log('debug', '-> Adding Referral...');
				$pendingrequest = ORM::factory('facebook_inviterequest') 
					-> where ( array (
						'status' => 'new',
						'request_id' => $data['request_ids'],
						'friend_id' => $data['fb_id']
						) ) -> find();
				
				if ($pendingrequest -> loaded )
				{
					$data['referreruser'] = $pendingrequest -> user_id;
					$pendingrequest -> status = 'processed';
					$pendingrequest -> save();					
				}				
			}
			else
				if (isset($values['referreruser']) )
					$data['referreruser'] = $values['referreruser'];
				else
					$data['referreruser'] = null;
			
			

			$rc = User_Model::registerorloginuser( $data, $message );									
			
			if ( $rc == false )
			{

				Session::set_flash( 'user_message', "<div class=\"error_msg\">" . $message . "</div>");
				url::redirect('/');
			}
			else
			{
				header('P3P: CP="NOI ADM DEV COM NAV OUR STP"');
				//kohana::log('info', '-> Redirecting to europectrier...');
				url::redirect( 'boardmessage/index/europecrier');						
			}				
		}	
			
	}
	
	/*
	 * Logout utente
	 *
	 */
	
	public function logout()
	{
		
		$character = Character_Model::get_info( Session::instance()->get('char_id') ); 		
		$this -> template = new View('template/homepage');
		$sheets = array('home' => 'screen');
		
		$message = '';
		
		// se il char ha il bonus automaticsleep, setta
		// automatic sleep a sì a meno che non sia esplicitamente
		// vietato
		
		if ( !is_null( $character ))
		{
			if ( 
				Character_Model::get_premiumbonus( $character -> id, 'automatedsleep' ) !== false 
				and
				$character -> user -> disablesleepafteraction == 'N' 
			)
			{
				kohana::log('info', "{$character -> name} logged out, I am setting sleepafteraction to Y.");
				$character -> user -> sleepafteraction = 'Y' ;
				$character -> user -> save();
			}
		}
		
		Session::instance() -> destroy();
		
		$this->template->content=new View('user/logout'); 
		$this->template->sheets = $sheets;   
		
		Auth::instance()->logout( true );	
	}

	/*
	* Visualizza il profilo dell' utente
	*
	*/
	
	public function profile()
	{
	
      $view    = new View('user/profile');
      $subm    = new View ('template/submenu');
      $sheets  = array('gamelayout'=>'screen', 'submenu'=>'screen');
	  $char = Character_Model::get_info( Session::instance()->get('char_id') ); 		
      
	  $lnkmenu = $char -> user -> get_account_submenu( 'profile' );	 
	  $subm->submenu = $lnkmenu;
	  $view->submenu = $subm;
      $user = Auth::instance() -> get_user();      
      $view->bind('user', $user);
      	                 
      $this->template->content = $view;
      $this->template->sheets = $sheets;
	}
	
	/**
	* Modifica utente
	* @param none
	* @return none 
	*/

	public function changepassword()
	{
    
		$view    = new View('user/changepassword');
		$subm    = new View ('template/submenu');
		$sheets  = array('gamelayout'=>'screen', 'submenu'=>'screen');
		$char = Character_Model::get_info( Session::instance()->get('char_id') ); 		
		
		$lnkmenu = $char -> user -> get_account_submenu( 'changepassword' );	 
		$subm -> submenu = $lnkmenu;
		$view -> submenu = $subm;
		
		$this -> template -> content = $view;
		$this -> template -> sheets = $sheets;
		$form = array(
			'old_password' => '',
			'password' => '', 
			'password_confirm' => '',			  
		);	
	
		$errors = $form;
	
		if ( $_POST )
		{      
			  
			
			$user = Auth::instance() -> get_user();						
			$post = Validation::factory($_POST)
				 -> pre_filter('trim', TRUE)
				 -> add_rules('old_password', 'required')				
				 -> add_rules('password', 'required')
				 -> add_rules('password_confirm', 'required')
				 -> add_rules('password_confirm', 'matches[password]');
			
			$post -> add_callbacks('old_password', array($this, '_checkoldpassword'));
			
			if ($post -> validate() )
			{
				$user -> password = $this -> input -> post( 'password'); $user -> save();
				Session::set_flash('user_message', "<div class=\"info_msg\">".Kohana::lang('user.change_password_ok')."</div>" );
				url::redirect('user/profile');
			}	
			else
			{

				$errors = $post -> errors('form_errors');                             
				//print kohana::debug( $errors);
				$view -> bind('errors', $errors); 					
			}
			$view -> bind('form', $form);				
			
		}		
		
	}
	
	/*
	* Callback che verifica l' unicità del nome
	*
	* @param  Validation  $array   oggetto Validation
	* @param  string      $field   nome del campo che deve essere validato
	*/

  public function _unique_username(Validation $array, $field)
  {
     // controllo il db
     $name_exists = (bool) ORM::factory('user')->where('username', $array[$field])->count_all();
   
     if ($name_exists)
     {
         // aggiungo l' errore
         $array->add_error($field, 'username_exists');
     }
  }

	/*
	 * Callback che verifica l' unicità della email
	 *
	 * @param  Validation  $array   oggetto Validation
	 * @param  string      $field   nome del campo che deve essere validato
	 */

  public function _unique_email(Validation $array, $field)
  {
     // controllo il db
     $email_exists = (bool) ORM::factory('user')->where('email', $array[$field])->count_all();
   
     if ($email_exists)
     {
         // aggiungo l' errore
         $array->add_error($field, 'email_exists');
     }
  }

  
	/*
	* Callback che verifica che il referral id specificato esista nel database 
	*
	* @param  Validation  $array   oggetto Validation
	* @param  string      $field   nome del campo che deve essere validato
	*/

  public function _c_referral_id(Validation $array, $field)
  {
       
  
     if ( empty($array[$field]) )
        return;
				
     // controllo il db
     $id_exists = (bool) ORM::factory('user')->where(
      array( 
        'id' => $array[$field],
        'status != '   => 'canceled'
        ))->count_all();
   
     if (! $id_exists)
     {
         // aggiungo l' errore
         $array -> add_error($field, 'id_notexisting');
     }
  }
  
	/*
	* Callback: verifica che l' utente abbia accettato il terms of service.
	*
	* @param  Validation  $array   oggetto Validation
	* @param  string      $field   nome del campo che deve essere validato
	*/
  
  public function _c_accepttos(Validation $array, $field)
  {
    if ( $array[$field] != true )
      $array->add_error($field, 'tos_notaccepted');    
  }

	/*
	* Callback: verifica che la vecchia pwd sia corretta
	*
	* @param  Validation  $array   oggetto Validation
	* @param  string      $field   nome del campo che deve essere validato
	*/

	public function _checkoldpassword (Validation $array, $field)
	{
	$user = Auth::instance()->get_user();			
	$salt = Auth::instance()->find_salt( $user->password );
	if ( strcmp($user->password , Auth::instance()->hash_password( $array[$field], $salt )) )
		$array->add_error($field, 'matches');    
	}  

	/**
	* Funzione che lista i referral
	* @param none
	* @return none
	*/

	public function referrals()
	{
	
		$view = new View( 'user/referrals');
		$sheets  = array('gamelayout' => 'screen', 'character'=>'screen', 'pagination'=>'screen', 'submenu'=>'screen');		
		$user = Auth::instance()->get_user();			
		$char = Character_Model::get_info( Session::instance()->get('char_id') );
		
		$db = Database::instance();
		$sql = "
			select r.*, c.name, c.id character_id, u.created 
			from user_referrals r, characters c, users u
			where 
			r.user_id = " . $user -> id . " 
			and r.referred_id = c.user_id and c.user_id = u.id
			order by r.id desc" ;
		
		
		$referrals = $db->query( $sql );
		
		//$output = $this->profiler->render(TRUE);				
		
		$submenu = new View("character/submenu");
		$submenu -> action = 'referrals';
		$view -> submenu = $submenu;
		$view->referrals = $referrals;
		$view->user = $user;
		$view->char = $char;
		$this->template->content = $view;
		$this->template->sheets = $sheets;
	
	}

	/*
	 * Configure Account
	 * @param none
	 * @return none
	 */
	 
	 public function configure()
	 {
	 
		$_user = Auth::instance()->get_user();							
		$user = ORM::factory('user', $_user -> id);		
		
		$char = Character_Model::get_info( Session::instance()->get('char_id') );
		$view = new View ( 'user/configure');
		$subm    = new View ('template/submenu');
		$sheets  = array('gamelayout'=>'screen', 'submenu'=>'screen');
		$lnkmenu = $char -> user -> get_account_submenu( 'configure' );	 
		$titles = array(		
			'notitle' => kohana::lang('global.title_notitle_b'),
			'artisan' => kohana::lang('global.title_artisan_b'),
			'bachelor' => kohana::lang('global.title_bachelor_b'),
			'brother' => kohana::lang('global.title_brother_b'),
			'burgher' => kohana::lang('global.title_burgher_b'),
			'commoner' => kohana::lang('global.title_commoner_b'),
			'don' => kohana::lang('global.title_don_b'),
			'despot' => kohana::lang('global.title_despot_b'),
			'esquire' => kohana::lang('global.title_esquire_b'),
			'explorer' => kohana::lang('global.title_explorer_b'),
			'father' => kohana::lang('global.title_father_b'),
			'freeman' => kohana::lang('global.title_freeman_b'),
			'gentleman' => kohana::lang('global.title_gentleman_b'),
			'magister' => kohana::lang('global.title_magister_b'),
			'monsignor' => kohana::lang('global.title_monsignor_b'),
			'master' => kohana::lang('global.title_master_b'),
			'mercenary' => kohana::lang('global.title_mercenary_b'),
			'merchant' => kohana::lang('global.title_merchant_b'),
			'peasant' => kohana::lang('global.title_peasant_b'),
			'pirate' => kohana::lang('global.title_pirate_b'),
			'rogue' => kohana::lang('global.title_rogue_b'),
			'scholar' => kohana::lang('global.title_scholar_b'),
			'sergeant' => kohana::lang('global.title_sergeant_b'),
			'wanderer' => kohana::lang('global.title_wanderer_b'),
			'warlord' => kohana::lang('global.title_warlord_b'),
			'warrior' => kohana::lang('global.title_warrior_b'),
		);
			   		
		// combo Linguaggi
		
		$view -> spokenlanguages = array(
			'' => kohana::lang('global.select'),
			'Bulgarian' => 'Bulgarian',
			'Croatian' => 'Croatian',
			'Czech' => 'Czech',
			'Dutch' => 'Dutch',
			'English' => 'English',
			'French' => 'French',			
			'German' => 'German',
			'Italian' => 'Italian',			
			'Portuguese' => 'Portuguese',
			'Russian' => 'Russian',						
			'Serbian' => 'Serbian',
			'Spanish' => 'Spanish',
		);
		
		
		if ( !$_POST )
			;
		else
		{
		
			// ***** General Panel *****
			
			if ( $this -> input -> post('general') != '' )
			{
				
				$user -> nationality = $this -> input -> post('nationality' );
			
				// Hide max stat badges
			
				if ( $this -> input -> post('hidemaxstatsbadges') == 'activate')
					$user -> hidemaxstatsbadges = 'Y';
				else
					$user -> hidemaxstatsbadges = 'N';

				// available for religious functions
			
				if ( $this -> input -> post('availableregfunctions') == 'available')
					$user -> availableregfunctions = 'Y';
				else
					$user -> availableregfunctions = 'N';
									
				// Spoken Languages
				
				//var_dump($this -> input -> post());exit;
				
				// linguaggi parlati, su user
								
				foreach ($user -> user_languages as $language )				
				{
					$language -> language = $this -> input -> post('spokenlanguage'.$language -> position);
					//var_dump($language);exit;
					$language -> save();
				}
				
				if ( $this -> input -> post('showlanguagesinpublicprofile') == 'show')
					$user -> showlanguages = 'Y';
				else
					$user -> showlanguages = 'N';
				
				$user -> save();			
				$par[0] = $user -> nationality;
				GameEvent_Model::process_event( $char, 'configurenationality', $par );
			
			}
			
			// ***** SKIN *****
			
			if ( $this -> input-> post('skin') != '' )
			{
				Character_Model::modify_stat_d( $char -> id, 
					'skin', 
					0,
					null,
					null,
					true,
					$this -> input -> post('skin')
				);
			}
			
			// ***** BASIC PACKAGE OPTIONS *****
			
			if ( $this -> input -> post('basicpackage') != '' )
			{
				Character_Model::modify_stat_d( 
					$char -> id,
					'basicpackage',
					0,
					'title',
					null,
					true,					
					$this -> input -> post('title') . '_' . strtolower($char -> sex),
					$this -> input -> post('title')
				);
				
			}
			
			// ***** EMAIL *****
			
			if ( $this -> input -> post('emailsection') == 'Modify' )
			{				
				
				// newsletter
			
				if ( $this -> input -> post('newsletter') == 'send')
					$user -> newsletter = 'Y';
				else
					$user -> newsletter = 'N';
			
				// receive IG messages on email
				
				if ( $this -> input -> post('receiveigmessagesonemail') == 'receive')
					$user -> receiveigmessagesonemail = 'Y';
				else
					$user -> receiveigmessagesonemail = 'N';
				
/*
				if ($user -> email != $this -> input -> post('email') )
					User_model::modifyemail( $user, $this -> input -> post('email'));
*/
				
				$user -> save();
			
			}
			
			// ***** AUTOMATED REST *****
			
			if ( $this -> input -> post('automatedsleep') != '' )
			{
				// Automated Rest
			
				if ( Character_Model::get_premiumbonus( $char -> id, 'automatedsleep' ) !== false )
				{
				
					if ( $this -> input -> post('disablesleepafteraction') == 'activate')
						$user -> disablesleepafteraction = 'Y';
					else
						$user -> disablesleepafteraction = 'N';		
					
					if ( $this -> input -> post('maxglut') < 1 or $this -> input -> post('maxglut') > 50 )
					{
					
					Session::set_flash('user_message', "<div class=\"error_msg\">" . Kohana::lang('user.error-maxglutvalue')."</div>" );
						url::redirect('/user/configure');			
					}
					
					$user -> maxglut = 	$this -> input -> post('maxglut');
					$user -> save();
					
				}
			}
			
			Session::set_flash('user_message', "<div class=\"info_msg\">".Kohana::lang('user.customization_ok')."</div>" );
			
		}
		
		
		// countries		
		$countrycodes = ORM::factory('cfgcountrycode')->find_all();		
		foreach ($countrycodes as $cc)
			$ccodes[$cc -> code] = $cc -> country;
		
		// reload user
		
		$user = ORM::factory('user', $user -> id);		
		$languages = array();
		foreach ($user -> user_languages as $language)
			$languages[$language -> position] = $language -> language;
		
		$stat = Character_Model::get_stat_d(
				$char -> id,
				'basicpackage',
				'title');
			
		if ($stat -> loaded)
			$title = $stat -> stat2;
		else
			$title = '';
		
		$subm -> submenu = $lnkmenu;
		$view -> languages = $languages;
		$view -> title = $title;
		$view -> countrycodes = $ccodes;		
		$view -> submenu = $subm;
	  $view -> user = $user;		
		$view -> titles = $titles;
		$view -> char = $char;
		$this -> template -> content = $view;
		$this -> template -> sheets = $sheets;
		
	}
	 
	/**
	* Returns all bonus bought by player
	* @param none
	* @return none
	*/
	
	function bonuspurchases()
	{
		
		$view = new view( 'user/bonuspurchases');
		$subm    = new View ('template/submenu');
		$sheets  = array('gamelayout' => 'screen', 'character'=>'screen', 'pagination'=>'screen', 'submenu'=>'screen');		
		$char = Character_Model::get_info( Session::instance()->get('char_id') );
		$limit = 20	;

		$activebonuses = Character_Model::get_premiumbonuses($char -> id);
		
		$purchasedbonuses = Database::instance() -> query("
			SELECT cp.id, c.name, c.cutunit, cp.user_id, 
			cp.targetuser_id, cp.targetcharname, cp.character_id, cp.cfgpremiumbonus_id, 
			-- cp.cfgpremiumbonuses_cut_id, 
			cp.starttime, cp.endtime, cp.param1, cp.param2, cp.doubloons 
			FROM character_premiumbonuses cp, cfgpremiumbonuses c
			WHERE cp.character_id = {$char -> id} 
			AND   cp.cfgpremiumbonus_id != 0 
			AND   cp.cfgpremiumbonus_id = c.id
			ORDER BY cp.endtime desc
			");
				
		$this->pagination = new Pagination(array(
			'base_url'=>'user/bonuspurchases',
			'uri_segment'=>'bonuspurchases',
			'style' =>  'extended',
			'total_items' => $purchasedbonuses -> count(),
			'items_per_page'=>$limit));				
		
		$purchasedbonuses = Database::instance() -> query("
			SELECT cp.id, c.name, c.cutunit, cp.user_id, 
			cp.targetuser_id, cp.targetcharname, cp.character_id, cp.cfgpremiumbonus_id, 
			-- cp.cfgpremiumbonuses_cut_id, 
			cp.starttime, cp.endtime, cp.param1, cp.param2, cp.doubloons 
			FROM character_premiumbonuses cp, cfgpremiumbonuses c
			WHERE cp.character_id = {$char -> id} 
			AND   cp.cfgpremiumbonus_id != 0 
			AND   cp.cfgpremiumbonus_id = c.id			
			ORDER BY cp.endtime desc
			limit $limit offset " . $this -> pagination -> sql_offset );
			
		$lnkmenu = $char -> user -> get_account_submenu( 'bonuspurchases' ); 	
		$subm -> submenu = $lnkmenu;
		$view -> submenu = $subm;
		$view -> char = $char;
		$view -> tabindex = 0;
		$view -> pagination = $this->pagination;
		$view -> purchasedbonuses = $purchasedbonuses;
		$view -> activebonuses = $activebonuses;
		$this -> template -> content = $view;
		$this -> template -> sheets = $sheets;
	
	}

	/**
	* Returns all purchases made by the account
	* @param none
	* @return none
	*/
	
	public function purchases( )
	{
		
		$view = new view( 'user/purchases');
		$subm    = new View ('template/submenu');
		$sheets  = 
			array('gamelayout' => 'screen', 'character'=>'screen', 'pagination'=>'screen', 'submenu'=>'screen');		
		$char = Character_Model::get_info( Session::instance()->get('char_id') );
		$limit = 20	;
		
		$purchases = Database::instance() -> query("
			SELECT * 
			FROM electronicpayments
			WHERE user_id = {$char -> user_id}
			ORDER BY id desc;
			");
				
		$this->pagination = new Pagination(array(
			'base_url'=>'user/purchases',
			'uri_segment'=>'purchases',
			'style' =>  'extended',
			'total_items' => $purchases -> count(),
			'items_per_page' => $limit));				
		
		$purchases = Database::instance() -> query("
			SELECT * 
			FROM electronicpayments 
			WHERE user_id = {$char -> user_id}			
			ORDER BY id desc 
			limit $limit offset " . $this -> pagination -> sql_offset );
			
		$lnkmenu = $char -> user -> get_account_submenu( 'purchases' ); 	
		$subm -> submenu = $lnkmenu;
		$view -> submenu = $subm;
		$view -> char = $char;
		$view -> pagination = $this->pagination;
		$view -> purchases = $purchases;		
		$this -> template -> content = $view;
		$this -> template -> sheets = $sheets;		
	}
	
	public function _checkcaptcha(Validation $array, $field)
	{
		
		$query = http_build_query([
		 'secret' => '6Lf_v3MUAAAAANqZZNLdcnp61ux0aEXhCWkfPqkE',
		 'response' => $this -> input -> post('g-recaptcha-response'),		 
		]);
		
		$url = "https://www.google.com/recaptcha/api/siteverify?" . $query;
		kohana::log('debug', kohana::debug($url));
		$ch = curl_init( $url );
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		$response = json_decode(curl_exec($ch));
		kohana::log('debug', curl_error($ch));
		kohana::log('debug', kohana::debug($response));
		$valid = false;
		if ($response && $response -> success) {
			$valid = true;
		}
		else
		{
			$valid = false;
			$array -> add_error($field, 'captchaerror');
			kohana::log('debug', kohana::debug($array));
		}
		
	}

	public function _fake_email(Validation $array, $field)
	{
		$d = explode("@", $array[$field]);
		// controllo il db
		$fake_domain = (bool) ORM::factory('Blockedemailprovider') -> where('domain', $d[1])->count_all();
	   
		if ($fake_domain)
		{
			// aggiungo l' errore
			$array -> add_error($field, 'blocked_domain');
		}
	} 

  /**
  * unsubscribe from the newsletter
  * @param hashcode
  * @param email address
  * @return none
  */
  
  public function unsubscribe ( $username, $hash )
  {
	kohana::log('debug', '-> Trying to unsubscribe {$username}, hash: {$hash}' );
	$user = ORM::factory('user') -> where ( 
		array( 
			'activationtoken' => $hash,
			'username' => $username,
			'newsletter' => 'Y' 
		)) -> find();
		
	if ( $user -> loaded )
	{
		$user -> newsletter = 'N';
		$user -> save();
		url::redirect('page/display/unsubscribe-ok');
	}
	else
		url::redirect('page/display/unsubscribe-nok');
  }
  

  
}
