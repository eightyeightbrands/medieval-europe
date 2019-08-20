	<?php defined('SYSPATH') OR die('No direct access allowed.');

class Quest_Themarket_Model extends Quest_Model
{
	protected $name = 'themarket';
	protected $stepsnumber = 3;
	protected $strinit = '000-------';
	protected $id = 5;	
	
	function process_event_boughtitemmarket( $char, $event, $par, $instance )
	{
		kohana::log('debug', '-> Quest: processing event: ' . $event );
		
		// step 1:evento compratore compra da chancellor
		
		kohana::log('debug', get_class($this) . '-> Quest: checking for step 1');
		
		if ( $par[0] -> cfgitem -> tag == 'bread' and 
			$par[2] == 1 and 
			$par[1] -> has_rprole ( 'chancellor' ) and 
			$par[1] -> region -> kingdom_id == $char -> region -> kingdom_id )			
			$this -> complete_step( $char, $instance, 0 );
			
		// step 3: evento constabile compra da compratore
		
		kohana::log('debug', get_class($this) . '-> Quest: checking for step 2');
		if ( $par[0] -> cfgitem -> tag == 'bread' and 
			$par[2] == 1 and 
			$par[3] == 3.5 and 
			$par[4] -> has_rprole ( 'chancellor' ) and 
			$par[4] -> region -> kingdom_id == $par[1] -> region -> kingdom_id )			
			$this -> complete_step( $par[1], $instance, 2 );	
		
	}
	
	function process_event_sellitemmarket( $char, $event, $par, $instance )
	{
		kohana::log('debug', '-> Quest: processing event: ' . $event );
		
		if ( 
			$par[0] -> cfgitem -> tag == 'bread' and 
			$par[1] -> id == $char -> id and 
			$par[2] == 1 and 
			$par[3] == 3.5 )						
			$this -> complete_step( $char, $instance, 1 );		
	}
	
	function finalize_quest( $char, $instance ) 
	{

		kohana::log('debug', '-> ' . get_class($this) . ': Finalizing quest.');
		kohana::log('debug', '-> ' . get_parent_class($this) . ': Finalizing quest.');
		
		// give 10 coins
		
		$char -> modify_coins( +15, 'questreward' );
		return;
	}
	
}