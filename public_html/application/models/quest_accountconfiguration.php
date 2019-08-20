<?php defined('SYSPATH') OR die('No direct access allowed.');

class Quest_Accountconfiguration_Model extends Quest_Model
{
	protected $name = 'accountconfiguration';
	protected $stepsnumber = 5;
	protected $strinit = '00000-----';
	protected $id = 1;	
	
	function process_event_configurenationality( $char, $event, $par, $instance )
	{
		kohana::log('debug', '-> Quest: processing event: ' . $event );
		
		if ( $par[0] != '--' )
			$this -> complete_step( $char, $instance, 0 );
		
	}
	
	function process_event_changecharacterdescription( $char, $event, $par, $instance )
	{
		kohana::log('debug', '-> Quest: processing event: ' . $event );
		
		if ( strlen($par[0]) > 0 )
			$this -> complete_step( $char, $instance, 1 );
		
	}
	
	function process_event_changecharactersignature( $char, $event, $par, $instance )
	{
		kohana::log('debug', '-> Quest: processing event: ' . $event );
				
		if ( strlen($par[0]) > 0 )
			$this -> complete_step( $char, $instance, 2 );
		
	}
	
	function process_event_changecharacteravatar( $char, $event, $par, $instance )
	{		
		kohana::log('debug', '-> Quest: processing event: ' . $event );
				
		$this -> complete_step( $char, $instance, 3 );
	}
	
	function process_event_changecharacterslogan( $char, $event, $par, $instance )
	{
		kohana::log('debug', '-> Quest: processing event: ' . $event );
		
		if ( strlen($par[0]) > 0 )
			$this -> complete_step( $char, $instance, 4 );
	}
		
	function finalize_quest( $char, $instance ) 
	{
	
		$char -> modify_coins( +50, 'questreward' );
		
	}
	
	
}
