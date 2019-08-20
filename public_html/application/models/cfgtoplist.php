<?php defined('SYSPATH') OR die('No direct access allowed.');

class Cfgtoplist_Model extends ORM
{
	
	/**
  * Ritorna il link della toplist da associare al bottone	
	* @param type (tipo di voto: energy or coins
	* @return  URL da associare (stringa)
	*/
	
	
	
	function obs_hook_toplist( $type )
	{
	
		//kohana::log( 'debug', 'type: ' . $type );
		
		if ( $type != 'energy' and $type != 'silvercoin' ) 
			return null;
			
		$char = Character_Model::get_info( Session::instance()->get('char_id') );
		// trova le toplist per cui oggi il char non ha già votato
		
		$db = Database::instance();
		
		$sql = "select * from cfgtoplists c
		where c.id not in 
			( select cfgtoplist_id 
			  from toplistvotes where character_id = " . $char -> id ." 
		    and from_unixtime(  timestamp , '%Y-%m%-%d' ) = curdate() )
    and status = 'active' 
		and reward like '%" . $type . "%' "; 
		
		//kohana::log('debug', $sql );
		
		$res = $db->query( $sql ) -> as_array();
		
		//kohana::log('debug', kohana::debug( $res ));
		if ( count($res) > 0 )
		{
			$r = rand( 0, count($res) - 1);
			return $res[$r]->id;
		}
		else
			return null;
	
	}
	
}
