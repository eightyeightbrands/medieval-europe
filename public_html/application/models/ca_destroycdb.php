<?php defined('SYSPATH') OR die('No direct access allowed.');


class CA_DestroyCdb_Model extends Character_Action_Model
{		
	
	protected $cancel_flag = false; // se true, la azione è cancellabile dal pg.	
	protected $immediate_action = true;	
	
	protected function check( $par, &$message )	{ }

	public function append_action( $par, &$message ){ }
	
	public function cancel_action() {	}
	
	public function complete_action( $data )
	{
		
		$cdb = ORM::factory('structure', $data -> param1 );			
		if ( $cdb -> loaded )
		{
			// trovo tutti i char che hanno combattuto nella battaglia
			// e che sono rimasti all' interno del campo di battaglia
			
			$sql = "select id from characters c 
				where position_id = " . $cdb -> region_id;
			
			$res = Database::instance() -> query( $sql );
			
			foreach ( $res as $row )
			{
				if ( Character_Model::is_fighting( $row -> id ) )
					Character_Model::modify_stat_d( 
					$row -> id,
					'fighting',
					false,
					null,
					null,					
					true
					);
			}
			
			$cdb -> destroy();					
			
		}		
	
	}

	public function get_action_message( $type = 'long') 
	{
		$pending_action = $this->get_pending_action();
		$message = "";
		
		if ( $pending_action -> loaded )
		{
				if ( $type == 'long' )		
					$message = '__regionview.recovering_longmessage';
			else
				$message = '__regionview.recovering_shortmessage';
		}
		
		return $message;
	
	}	

	
}
