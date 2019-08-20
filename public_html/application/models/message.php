<?php defined('SYSPATH') OR die('No direct access allowed.');
class Message_Model extends ORM
{

/** 
* manda un messaggio IG al char
* @param obj sender char
* @param obj receiver char
* @param string subject
* @param string body
* @param boolean massive flag massiva
* @param boolean is sent by system
* @param boolean make a copy for sender?
* @param type mail template
* @param string param1
* @return none
*/

public function send
( 
	$sender, 
	$recipient, 
	$subject, 
	$body, 
	$massive = false, 
	$system = false, 
	$copyforsender = true, 
	$type = 'normal',
	$param1 = null)
{	
	// La mail è massiva
	
	if ( $massive )
	{		
	
		$role = $sender -> get_current_role();
				
		// il char ha i permessi di mandare una mail massiva?
		
		if ( is_null($role) or ($role -> tag != 'king' and $role -> tag != 'vassal') )
			return kohana::lang('message.massivenotallowed');
		
		$db = Database::instance(); 
		
		if ( $role -> tag == 'king' )
			$recipients = $db -> query ( "select id from characters 
				where region_id in ( select id from regions where kingdom_id = " . $sender -> region -> kingdom -> id . ")" ); 
		else
			$recipients = ORM::factory('character')->
				where( array( 'region_id' => $sender -> region_id )) -> find_all();				
		
		// il char ha energia a sufficienza? (1 punto energia ogn 20 email)
		
		$energy  = min( 50, 
			max(1, intval( 
				$recipients -> count() * 0.05 )));
			
		if ( $sender -> energy < $energy )
			return kohana::lang('message.notenoughenergy');
		
		foreach ( $recipients as $r )
		{
			$m = new Message_Model();
			$m -> char_id = $r -> id ;			
			$m -> fromchar_id = $sender -> id ;			
			$m -> date = time();
			$m -> tochar_id = $r -> id ;
			$m -> subject = $subject;
			$m -> body = $body;				
			$m -> type = $type;
			$m -> param1 = $param1;
			$m -> save();
		}
							
		My_Cache_Model::delete (  '-charinfo_' . $r -> id . '_unreadmessages' );
						
		// takeoff energy
		
		$sender -> modify_energy ( -$energy, false, 'sendingmessage' );
		
	}
	else
	{
		
		
		if ( $system == true )		
		{
			$m = new Message_Model();
			$m -> date = time();
			$m -> char_id = $recipient -> id ;
			$m -> tochar_id = $recipient -> id ;
			$m -> fromchar_id = -1;
			$m -> subject = $subject;
			$m -> body = $body;		
			$m -> type = $type;
			$m -> param1 = $param1;
			$m -> save();	
		}
		else
		{
			if ( $copyforsender ) 
			{
				$m = new Message_Model();			
				$m -> date = time();
				$m -> char_id = $sender -> id ;
				$m -> fromchar_id = $sender -> id;
				$m -> tochar_id = $recipient -> id ;			
				$m -> subject = $subject;
				$m -> body = $body;
				$m -> type = $type;
				$m -> param1 = $param1;			
				$m -> save();	
			}
			
			$m = new Message_Model();			
			$m -> date = time();
			$m -> char_id = $recipient -> id ;
			$m -> fromchar_id = $sender -> id;			
			$m -> tochar_id = $recipient -> id ;			
			$m -> subject = $subject;
			$m -> body = $body;		
			$m -> type = $type;
			$m -> param1 = $param1;
			$m -> save();	
		}
		
		// Se l' utente ha selezionato di ricevere le email, manda una email.
		
		Utility_Model::send_notification( $recipient -> user -> id, "You received an Ingame message: {$subject}", nl2br($body));
		
		My_Cache_Model::delete (  '-charinfo_' . $recipient -> id . '_unreadmessages' );
		
	}
	
	return 'OK';
}

/**
* Trova i messaggi non letti
*/

function get_unreadmessages( $character_id )
{

	$umq = $sql = 
		"select c.name sender, m.isread, m.fromchar_id,
 		 m.id, m.subject, m.date 
		 from characters c, messages m 
		 where  m.fromchar_id = c.id 
		 and    m.char_id = " . $character_id . " 
		 and    m.tochar_id = " . $character_id . "
		 and    isread = 0
		 order by m.date desc ";
		 
	$um = Database::instance() -> query( $sql ) -> as_array() ;
	
	return $um;
}

	/**
	* Menu orizzontale
	*/
	
	function get_horizontalmenu( $action )
	{
		return array(
			'message/received' => 
				array( 
					'name' => kohana::lang('message.received_messages'), 
					'htmlparams' => array( 'class' =>( $action == 'received' ) ? 'selected' : '' )),
			'message/sent' =>
				array( 
					'name' => kohana::lang('message.sent_messages'), 
					'htmlparams' => array( 'class' =>( $action == 'sent' ) ? 'selected' : '' )),
			'message/write' => 	
				array( 
					'name' => kohana::lang('message.write_scroll'), 
					'htmlparams' => array( 'class' =>( $action == 'write' ) ? 'selected' : '' )),
			'message/archiveindex' => 	
				array( 
					'name' => kohana::lang('message.archiveindex'), 
					'htmlparams' => array( 'class' =>( $action == 'archiveindex' ) ? 'selected' : '' )));
	}
	

}
