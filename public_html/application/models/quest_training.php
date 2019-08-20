<?php defined('SYSPATH') OR die('No direct access allowed.');

class Quest_Training_Model extends Quest_Model
{
	protected $name = 'training';
	protected $stepsnumber = 2;
	protected $strinit = '00--------';
	protected $id = 9;
	protected $author_id = 1;
	protected $path = 'tutorial';
	
	function activate( $character, &$message, $spare3 = null, $spare4 = null )
	{
		
		$quest = QuestFactory_Model::createQuest('cultivatecrops');		
		if ($quest -> get_status($character) != 'completed')
		{
			$message = 'quests.error-cultivatecropsnotcompleted';
			return false;			
		}
		
		// da un bonus velocità
		
		Character_Model::modify_stat_d(
			$character -> id,
			'speedbonus',
			10,
			null,
			null, 
			true,
			time()+1800);
		
		// aggiunge scarpe 
		
		if ( $character -> sex == 'M' )
			$item = Item_Model::factory( null, 'shoesm_1' );		
		else
			$item = Item_Model::factory( null, 'shoesf_1' );		
		$item -> quality = 40;
		$item -> additem( 'character', $character -> id , 1 );
		
		
		// aggiunge writing kit e spada di legno
		
		$item = Item_Model::factory( null, 'writingkit' );
		$item -> quality = 10;
		$item -> additem( 'character', $character -> id , 1 );
		
		$item = Item_Model::factory( null, 'woodensword' );
		$item -> quality = 10;
		$item -> additem( 'character', $character -> id , 1 );
		
		$rc = parent::activate( $character, $message, $spare3, $spare4 );
		
		if ( $rc == false )
			return false;					
		
		$this -> initialize( $character, $spare3, $spare4 	);
		
		return true;
		
	}	
	
	function process_event_study( $char, $event, $par, $instance )
	{
		kohana::log('debug', '-> Quest: processing event: ' . $event );
		if ( $par[0] == 'retorica' )
			$this -> complete_step( $char, $instance, 0 );		
		if ( $par[0] == 'battleagility' )
			$this -> complete_step( $char, $instance, 1 );		
	}
		
	function finalize_quest( $char, $instance ) 
	{
		$char -> modify_coins( +40, 'questreward' );	
	}

}