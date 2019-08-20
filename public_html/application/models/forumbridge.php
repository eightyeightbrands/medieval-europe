<?php defined('SYSPATH') OR die('No direct access allowed.');

define('SMF_INTEGRATION_SETTINGS', serialize(array(
	'integrate_change_email' => 'change_email_function',
	'integrate_change_member_data' => 'change_member_data_function',
	'integrate_reset_pass' => 'reset_pass_function',
	'integrate_exit' => 'exit_function',
	'integrate_logout' => 'logout_function',
	'integrate_outgoing_email' => 'outgoing_email_function',
	'integrate_login' => 'login_function',
	'integrate_validate_login' => 'validate_login_function',
	'integrate_redirect' => 'redirect_function',
	'integrate_delete_member' => 'delete_member_function',
	'integrate_register' => 'register_function',
	'integrate_pre_load' => 'pre_load_function',
	'integrate_whos_online' => 'whos_online_function',
)));

class ForumBridge_Model {

const SECURITYKEY = '4320482rwjlksrewjrerjelwjrlwj'; 
const REGISTEREDUSERSGROUP = 32;

/**
* autentica l' utente nel forum 
* @param: char oggetto utente
* @return: none
*/

function create_account( $char, $foruminstance, &$data )
{
	
	$memberName = Database::instance() -> escape ($char->user->username);
	$realName = Database::instance() -> escape ( $char -> name ); 
	$emailAddress = $char->user->email;
	$is_activated = 1;					
	$ID_POST_GROUP = 4;
	$id_group = 36;
	$salt = substr(md5(mt_rand()), 0, 4);
	$temp_pwd = strtolower(substr(md5(date("Y-m-d H:i:s")),0,12));
	$password = sha1($temp_pwd);
	
	$dateRegistered = time();	
	$dbforum = Database::instance( $foruminstance );

	// creo account sul forum 
	try {
			
		kohana::log( 'debug', '-> Creating account on forum: ' . $foruminstance ); 

		$query = "REPLACE INTO smf_members(member_name, real_name, email_address, is_activated, id_post_group, passwd, date_registered, password_salt, ID_GROUP) VALUES( $memberName, $realName, '$emailAddress', '$is_activated', '$ID_POST_GROUP', '$temp_pwd', '$dateRegistered', '', '$id_group')"; 
		
		kohana::log( 'debug', '-> Executing query: ' . $query ) ; 
		
		$dbforum -> query ( $query ); 
		
		kohana::log('debug', '->Sending informational email to member:' .  $memberName );

		// Invio via mail l'avviso di creazione dell'account sul forum
		
		$data['username'] = $char -> user -> username;
		$data['password'] = $temp_pwd;		
		
		Character_Event_Model::addrecord( 
				$char -> id, 
				'normal', 
				'__user.register_emailbodyforumaccount' . 
				';' . $char -> user -> username .
				';' . $temp_pwd,
				'normal' );
		
	} catch (Kohana_Database_Exception $e)
	{
		kohana::log('error', 'An error occurred while creating forum account for member:' .  $memberName );
		kohana::log('error', kohana::log( 'error', $e -> getMessage() ));
		return false;
	}
	
	return true;	
	
}
	
	function delete_account( $char, $foruminstance )
	{
		// inabilitazione utente forum
		$dbforum = Database::instance( $foruminstance );
		$salt = substr(md5(mt_rand()), 0, 4);
		$temp_pwd = strtolower(substr(md5(date("Y-m-d H:i:s")),0,12));
		$password = sha1(strtolower( $this-> name ) . $temp_pwd);
		
		try {
			kohana::log( 'debug', 'Deleting account on forum: ' . $foruminstance );

		// rinomina e disabilita il vecchio account
			$sql = "UPDATE smf_members set member_name = '" . $this -> user -> username . '_' . $salt . "', email_address='deleted_" . $this -> user -> username . "@x.it' , password_salt = '$salt', passwd='$password', real_name = concat('(RIP) ' , real_name)	where member_name = '" . $this->user->username . "'" ; 
			
			kohana::log('info', $sql ); 
			$dbforum -> query( $sql ); 
			
		} catch (Kohana_Database_Exception $e)
		{
			kohana::log('error', 'An error occurred while deleting forum account for member:' .  $this->user->username );
			kohana::log('error', kohana::log( 'error', $e->getMessage() ));
		}	
						
		return;
	}
}
?>
