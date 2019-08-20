<?php defined('SYSPATH') OR die('No direct access allowed.');

class Admin_Model
{

	
	/*
	* Restore a char
	*/
	
	public function restorechar( $charname, $ispaid = false, $anonymize = false, $newname = null, $regionname = 'regions.agrigento', &$message )
	{
		
		kohana::log('info', '------ Restore Char ------');
		kohana::log('info', "Name = {$charname}");
		kohana::log('info', "Anonymize flag = {$anonymize}");
		
		
		$char = ORM::factory('ar_character') 
			-> where ('name', $charname ) 
			-> orderby ( 'id', 'DESC' )
			-> limit (1) -> find();			
			
		if (!$char -> loaded)
		{
			$message = 'Personaggio non trovato.';
			return false;			
		}
		
		kohana::log('info', "Current char id = {$char -> id}");
		
		$db = Database::instance();
		$db -> query("set autocommit = 0");
		$db -> query("start transaction");
		$db -> query("begin");
			
		try {
		
		
			if ( $anonymize == true )
			{	
				$res = $db -> query ( "show table status like 'characters' ");
				$newcharacter_id = $res[0] -> Auto_increment;
			}
			else
				$newcharacter_id = $char -> id;
			
			// char
			
			kohana::log('info', "Restoring character {$char -> name}, new char id is: {$newcharacter_id}");
			
			
			// update all the records in ar_archive with the new character_id
		
			$db -> query("update ar_characters set id = {$newcharacter_id} where id = {$char -> id}");
			$db -> query("update ar_character_stats set character_id = {$newcharacter_id} where character_id = {$char -> id}");
			$db -> query("update ar_character_titles set character_id = {$newcharacter_id} where character_id = {$char -> id}");
			$db -> query("update ar_items set character_id = {$newcharacter_id} where character_id = {$char -> id}");
			$db -> query("update ar_items set seller_id = {$newcharacter_id} where seller_id = {$char -> id}");
			$db -> query("update ar_structures set character_id = {$newcharacter_id} where character_id = {$char -> id}");
			$db -> query("update ar_character_events set character_id = {$newcharacter_id} where character_id = {$char -> id}");
			$db -> query("update ar_messages set char_id = {$newcharacter_id} where char_id = {$char -> id}");			
			$db -> query("update ar_messages set fromchar_id = {$newcharacter_id} where fromchar_id = {$char -> id}");			
			$db -> query("update ar_messages set tochar_id = {$newcharacter_id} where tochar_id = {$char -> id}");			
			$db -> query("update ar_character_premiumbonuses set character_id = {$newcharacter_id} where character_id = {$char -> id}");			

			// Start
			
			$db -> query("replace into characters select * from ar_characters where id = {$newcharacter_id}");			
			$db -> query("update characters set status = null where id = {$newcharacter_id}");
			
			if ($ispaid == false )
				$db -> query("
					update characters set 
						str = round(str*70/100,0),
						intel = round(intel*70/100,0),
						dex = round(dex*70/100,0),
						cost = round(cost*70/100,0),
						car = round(car*70/100,0)
					where id = {$newcharacter_id}");
			
			// action consumeglut
			
			$db -> query("INSERT INTO `character_actions` VALUES (NULL,{$newcharacter_id}, NULL, NULL, 'consumeglut', 0, 1, 'running', unix_timestamp(), unix_timestamp(), NULL, NULL, NULL, NULL, NULL)");						
			
			// stats
			
			$db -> query("replace into character_stats select * from ar_character_stats where character_id = {$newcharacter_id}");
			
			// titles
			
			
			$db -> query("replace into character_titles select * from ar_character_titles where character_id = {$newcharacter_id}");
			
			
			// items
			
			$db -> query("replace into items select * from ar_items where character_id = {$newcharacter_id}");
			$db -> query("replace into items select * from ar_items where seller_id = {$newcharacter_id}");
			
			// structures
			
			$db -> query("replace into structures select * from ar_structures where character_id = {$newcharacter_id}");
			
			// items in structures
			
			$db -> query("replace into items select * from ar_items where structure_id in (select id from ar_structures where character_id = {$newcharacter_id})");

			// eventi

			$db -> query("replace into character_events select * from ar_character_events where character_id = {$newcharacter_id}");				
			
			// messaggi
			
			$db -> query("replace into messages select * from ar_messages where char_id = {$newcharacter_id}");
			
			// bonuses
			$db -> query("replace into character_premiumbonuses select * from ar_character_premiumbonuses where character_id = {$newcharacter_id}");			
			
			// altro
			
			$db -> query("update characters set deathdate = null, death_region_id = null, health=100, energy=50, glut=50 where id = {$newcharacter_id}");
			
			$db -> query("delete from character_permanentevents where character_id = {$newcharacter_id} and description like '__permanentevents.death;%'");
			
			
			// TOFO
			
			//	​- you will have a new character id
			//	​- your biography will be blanked
			//	​- all your permanent events will be blanked
			//	​- all your messages will be blanked (otherwise people who sent you the message will know it's you)
			//	​- your slogan will be blanked
			//	​- your message signature will be blanked
			
			if ($anonymize == true )
			{
				
				// blank description, slogan, signature
				$db -> query("update characters set description=NULL, slogan = NULL, history=NULL, signature=NULL where id = {$newcharacter_id}");
				// delete permanent events: non necessario per via del nuovo IF.
				$db -> query("delete from messages where char_id = {$newcharacter_id}");				
				// cancella score e titoli
				
				$db -> query("delete from character_titles where character_id = {$newcharacter_id}");
				$db -> query("update characters set score = 0 where id = {$newcharacter_id}");
				$db -> query("update users set last_login = (unix_timestamp()-600) where id = {$char -> user_id}");
				
				// set age date to 0
				$db -> query("update characters set birthdate = unix_timestamp() where id = {$newcharacter_id}");
				
				$region = ORM::factory('region') 
					-> where( 'name', strtolower('regions.' . $regionname ))
					-> find();				
					
				if ($region -> loaded == false )
					throw new Exception("Region {$regionname} Not Found");
			
				// cambia residenza e birth city
				$db -> query("update characters set 
					position_id = {$region -> id},
					region_id = {$region -> id},
					birth_region_id = {$region -> id}
					where id = {$newcharacter_id}");
				
				// cambia nome
				
				if (!is_null($newname))
					$db -> query("update characters set name = '{$newname}' where id = {$newcharacter_id}");
				
				// manda messaggio a Re.
				
				$king = $region -> get_roledetails( 'king' );
				$vassal = $region -> get_roledetails( 'vassal' );
				$chancellor = $region -> get_roledetails( 'chancellor' );				
				
				if ( !is_null ( $king ) )
				{
					Character_Event_Model::addrecord( 
						$king->character_id, 
						'normal', 
						'__events.city_newcharacterborn;' . 
						'__' . $region->kingdom -> get_name()  . ';__'.$region->name  . ';' . $newname,
						'evidence');							
				}
					
				if ( !is_null( $vassal) ) 
				{
					Character_Event_Model::addrecord( $vassal->character_id, 'normal', '__events.city_newcharacterborn;' . 
						'__' . $region->kingdom -> get_name()  . ';__'.$region->name  . ';' . $newname,
						'evidence' );
				}
				
				
			
			}
			
			
			
		} catch (Exception $e)
		{
			kohana::log('error', kohana::debug( $e -> getMessage() ));
			kohana::log('error', 'An error occurred during operations, rollbacking everything.');
			Database::instance() -> query("rollback");						
			$message = "Errore durante l\' operazione: " . $e->getMessage();
			return false;
		}
		
		Database::instance()->query("set autocommit = 1");
		
		$message = 'Personaggio resuscitato.';
		
		return true;
		
	}

	function get_horizontalmenu( $action )
	{
		
		$lnkmenu = array
		(
			'/admin/console/' => array( 
				'name' => 'Console',
				'htmlparams' => array( 
					'class' =>( $action == 'console' ) ? 'selected' : '' )
			),				
			'/admin/giveitems/' => array( 
				'name' => 'Dai oggetti',
				'htmlparams' => array( 
					'class' =>( $action == 'giveitems' ) ? 'selected' : '' )
			),
			'/admin/add_adminmessage/' => array( 
				'name' => 'Messaggio IG',
				'htmlparams' => array( 
					'class' =>( $action == 'add_adminmessage' ) ? 'selected' : '' )
			),
				'/admin/wardrobeapprovalrequests/' => array( 
				'name' => 'Guardaroba',
				'htmlparams' => array( 
					'class' =>( $action == 'wardrobeapprovalrequests' ) ? 'selected' : '' )
			),			
			'/admin/multicheck/' => array( 
				'name' => 'Multicheck',
				'htmlparams' => array( 
					'class' =>( $action == 'wardrobeapprovalrequests' ) ? 'selected' : '' )
			),
		);
		
		return $lnkmenu;	
			
	}
}
