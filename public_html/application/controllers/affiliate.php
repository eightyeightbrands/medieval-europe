<?php defined('SYSPATH') OR die('No direct access allowed.');

class Affiliate_Controller extends Template_Controller
{		
	
		
	function register()
	{
		$view = new view ('affiliates/register');
		$sheets  = array( 'affiliates' => 'screen' );
		$this -> template = new View('template/affiliates');
				
		$this -> auth = new Auth();      
	
		
		$form = array(
			'username' => '',
			'email' => '',  			
			'domainname' => false			
		);
		
		//if a post exists, validate and process input
		
		if ($this -> input -> post())
		{
		
			$post = Validation::factory($this -> input -> post())
				-> pre_filter('trim', TRUE)
				-> add_rules('username','required', 'alpha_numeric', 'length[5,20]')
				-> add_rules('email', 'required', 'email', 'length[1,60]')
				-> add_rules('domainname', 'required', 'length[5,40]');

			$post -> add_callbacks('username', array($this, '_unique_username'));
			$post -> add_callbacks('email', array($this, '_unique_email'));			
			$post['ipaddress'] = $this -> input -> ip_address();
			
			if ( $post -> validate() )
			{
				// Add user
				$rc = Affiliate_Model::register($post);
				if ( $rc == false )
					Session::set_flash( 'user_message', "<div class=\"alert alert-danger\">" . $message . "</div>");										
				else
				{
					
					$auth = new Auth();
					$auth -> force_login( $this -> input -> post('username'));
					header('P3P: CP="NOI ADM DEV COM NAV OUR STP"');
					url::redirect( 'affiliate/dashboard');						
				}
			}
			else
			{      
				$errors = $post -> errors('form_errors');										
				$view -> errors = $errors;			
				$form = arr::overwrite( $form, $post -> as_array());	
			}
		}
		
		$view -> form = $form;		
		$this -> template -> sheets = $sheets;
		$this -> template -> content = $view;	
		
	}
	
	function login()
	{
		
		$view = new view ('affiliates/login');
		$sheets  = array( 'affiliates' => 'screen' );
		$this -> template = new View('template/affiliates');
		
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
				// si puÃ² usare l' username.
				$user = ORM::factory( 'user', $username) ;
				
				if ( !$user -> loaded )
				{
					$error = 'user.login_usernotfound';
					Session::set_flash( 'user_message', "<div class=\"error_msg\">".Kohana::lang( $error )."</div>");
				}			
				
				if ( is_null( $error ) )
				{
					// check user and password
					kohana::log('debug', "User: {$user}, Pass: {$password}");
					$rc = $this -> auth -> login( $user -> username, $password );						
					kohana::log('debug', "-> Return from aut: {$rc}");
					
					if ( $rc )
					{
						header('P3P: CP="NOI ADM DEV COM NAV OUR STP"');
						url::redirect( 'affiliate/dashboard');						
					}					
					else
					{
						kohana::log( 'debug', "-> Password [{$password}] is wrong." ); 
						Session::set_flash( 'user_message', "<div class=\"alert alert-warning\">".Kohana::lang("user.incorrectpassword")."</div>");			
					}
					
				}
			}	
			else
				Session::set_flash( 'user_message', "<div class='error_msg'>".kohana::lang("user.login_autherror")."</div>");
		}
		
		$this -> template -> sheets = $sheets;
		$this -> template -> content = $view;	
		
	}
	
	/*
	 * Reinvia la password per la email specificata. 	 
	 */

	public function forgotpassword()
	{		
		
		$view = new view ('affiliates/forgotpassword');
		$sheets  = array( 'affiliates' => 'screen' );
		$this -> template = new View('template/affiliates');

		$form = array('email' => '');				
		
		if ( $this -> input -> post() )
		{
			  
			$post = Validation::factory($_POST)
				-> pre_filter('trim', TRUE)
				-> add_rules( 'email', 'required', 'email', 'length[1,30]' );
				
			if ($post->validate() )
			{
				
				$user = ORM::factory('user') 
				-> where( 
					array( 
					'email' => $this -> input -> post('email')
					)	
				) -> find();

				if ( $user->loaded )
				{
					$newpassword_clr = substr(md5(time()),1,5);
					kohana::log( 'info', "user: " . $user -> username . " new password clear: " . $newpassword_clr );          
					$user -> password = $newpassword_clr;                             					
					$result_save = $user -> save();

					// email
					
					$subject = 'Resend Password';
					$body    = sprintf (
					'A new password has been generated: %s. We suggest you login with your user: %s and change the password as soon as possible.',
						$newpassword_clr, 
						$user->username 
					);
					$to      = $post['email'];					
					$rc = Utility_Model::mail( $to, $subject, $body );								
					if ($rc)
						Session::set_flash('user_message', "<div class=\"alert alert-info\">A new password has been emailed.</div>");        
					else
						Session::set_flash('user_message', "<div class=\"alert alert-danger\">Something went wrong while sending email, please contact support.</div>");         
				}  							
				else
				{
					Session::set_flash('user_message', "<div class=\"alert alert-warning\">No user found with this email.</div>");          
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
			$this -> template -> sheets = $sheets;
			$this -> template -> content = $view;
	}
	
	public function dashboard()
	{
		
		$view = new view ('affiliates/dashboard');
		$sheets  = array( 'affiliates' => 'screen' );
		$this -> template = new View('template/affiliates');
		$user = Auth::instance() -> get_user();
		
		$view -> user = $user;
		$this -> template -> sheets = $sheets;
		$this -> template -> content = $view;	
		
	}
	
	public function statistics()
	{
		$view = new view ('affiliates/statistics');
		$sheets  = array( 'affiliates' => 'screen' );
		$this -> template = new View('template/affiliates');
		$user = Auth::instance() -> get_user();
		
		$view -> user = $user;
		$this -> template -> sheets = $sheets;
		$this -> template -> content = $view;	
		
	}
	
	public function media()
	{
		
		$view = new view ('affiliates/media');
		$sheets  = array( 'affiliates' => 'screen' );
		$this -> template = new View('template/affiliates');
		$this->template->sheets = $sheets;   		
		$user = Auth::instance() -> get_user();
		
		$view -> user = $user;		
		$this -> template -> content = $view;	
		
	}
	
	public function logout()
	{
		
		$view = new view ('affiliates/logout');
		$sheets  = array( 'affiliates' => 'screen' );
		$this -> template = new View('template/affiliates');
		$this->template->sheets = $sheets;   		
		
		Session::instance() -> destroy();		
		Auth::instance()->logout( true );			
		$this -> template -> content = $view;	
		
	}
	
	public function changepassword()
	{
    $view = new view ('affiliates/dashboard');
		$sheets  = array( 'affiliates' => 'screen' );
		$this -> template = new View('template/affiliates');
		$this->template->sheets = $sheets;   	
		
		$form = array(
			'old_password' => '',
			'password' => '', 
			'password_confirm' => '',			  
		);	
		
		$user = Auth::instance() -> get_user();	
			
		if ( $this -> input -> post() )
		{      
			  
							
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
				Session::set_flash('user_message', "<div class=\"alert alert-warning\">".Kohana::lang('user.change_password_ok')."</div>" );
				url::redirect('affiliate/dashboard');
			}	
			else
			{

				$errors = $post -> errors('form_errors');                             				
				$view -> bind('errors', $errors); 					
			}
			
			$view -> bind('form', $form);				
			
		}		
		$view -> user = $user;
		$this -> template -> content = $view;
		$this -> template -> sheets = $sheets;
		
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
	
	/*
	* Callback che verifica l' unicitÃ  del nome
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
	 * Callback che verifica l' unicitÃ  della email
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
	
}
