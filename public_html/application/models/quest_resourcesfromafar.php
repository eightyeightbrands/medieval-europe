	<?php defined('SYSPATH') OR die('No direct access allowed.');

class Quest_Resourcesfromafar_Model extends Quest_Model
{
	protected $name = 'resourcesfromafar';
	protected $stepsnumber = 2;
	protected $strinit = '00--------';
	protected $id = 4;	
	protected $author_id = 11388;
	protected $path = 'tutorial';
	
	function activate( $character, &$message, $spare3 = null, $spare4 = null )
	{
		
		$quest = QuestFactory_Model::createQuest('fighting');		
		if ($quest -> get_status($character) != 'completed')
		{
			$message = 'quests.error-fightingnotcompleted';
			return false;			
		}
			
		// give items
		
		$item = Item_Model::factory( null, 'handaxe' );		
		$item -> quality = 20;
		$item -> additem( 'character', $character -> id , 1 );
		
		Character_Model::modify_stat_d(
			$character -> id,
			'speedbonus',
			10,
			null,
			null, 
			true,
			time()+3600);		
				
		$rc = parent::activate( $character, $message, $spare3, $spare4 );
		
		if ( $rc == false )
			return false;					
		
		$this -> initialize( $character, $spare3, $spare4);
		
		return true;
		
	}
		
	function process_event_getwood( $char, $event, $par, $instance )
	{
		kohana::log('debug', '-> Quest: processing event: ' . $event );
		$collectedwood = 0;
		
		if ( !is_null( $instance -> spare3 ))
			$collectedwood = $instance -> spare3;
		
		if ( $collectedwood + $par[0] >= 10 )
		{
			$collectedwood += $par[0];
			$instance -> spare3 = $collectedwood;
			$instance -> save();
			$this -> complete_step( $char, $instance, 0 );
		}
		else
		{
			$collectedwood += $par[0];
			$instance -> spare3 = $collectedwood;
			$instance -> save();
		}
		
	}
	
	function process_event_sellitemmarket( $char, $event, $par, $instance )
	{
		kohana::log('debug', '-> Quest: processing event: ' . $event );
		
		if ( 
			$par[0] -> cfgitem -> tag == 'wood_piece' and 
			$par[1] -> id == $char -> id and 
			$par[2] >= 10 )						
			$this -> complete_step( $char, $instance, 1 );		
	}
	
	function finalize_quest( $char, $instance ) 
	{
	
		$char -> modify_coins( +320, 'questreward' );
		Achievement_Model::add( $char, 'stat_tutorialcompleted', 1, 1 );
		
	}
	
}