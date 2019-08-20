<?php defined('SYSPATH') OR die('No direct access allowed.');

class Quest_Prisoncoreloop_Model extends Quest_Model
{
	
	protected $name = 'prisoncoreloop';
	protected $stepsnumber = 3;
	protected $strinit = '000-------';
	protected $id = 5;
	protected $author_id = 17123;
	protected $path = 'tutorial';
	
	function activate( $character, &$message, $spare3 = null, $spare4 = null )
	{	
		// da un bonus velocità
		
		Character_Model::modify_stat_d(
			$character -> id,
			'speedbonus',
			10,
			null,
			null, 
			true,
			time()+1800);
			
		$rc = parent::activate( $character, $message, 0, null, null );
		
		if ($rc)
			$this -> initialize( $character, $spare3, $spare4);
		
		// pagnotta
		$item = Item_Model::factory( null, 'bread' );						
		$item -> additem( 'character', $character -> id , 1 );
		
		return $rc;	
	}
	
	function process_event_cleanprison( $char, $event, $par, $instance )
	{
		kohana::log('debug', '-> Quest: processing event: ' . $event );
		if ( $par[0] == 1 )
			$this -> complete_step( $char, $instance, 0 );				
						
	}
	
	function process_event_eatfood( $char, $event, $par, $instance )
	{
		kohana::log('debug', '-> Quest: processing event: ' . $event );
					
		$this -> complete_step( $char, $instance, 1 );
		
	}
	
	function process_event_resttavern( $char, $event, $par, $instance )
	{
		kohana::log('debug', '-> Quest: processing event: ' . $event );
		
		if ( $par[0] == true )
			$this -> complete_step( $char, $instance, 2 );		
		
	}
	
	function finalize_quest( $char, $instance ) {

		$char -> modify_coins( +10, 'questreward' );						

	}
}
