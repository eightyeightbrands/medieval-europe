<?php defined('SYSPATH') OR die('No direct access allowed.');

class Quest_Cultivatecrops_Model extends Quest_Model
{
	protected $name = 'cultivatecrops';
	protected $stepsnumber = 3;
	protected $strinit = '000-------';
	protected $id = 2;
	protected $author_id = 1;
	protected $path = 'tutorial';
	
	function activate( $character, &$message, $spare3 = null, $spare4 = null )
	{
		
		$quest = QuestFactory_Model::createQuest('prisoncoreloop');		
		if ($quest -> get_status($character) != 'completed')
		{
			$message = 'quests.error-prisoncoreloopnotcompleted';
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
		
		$rc = parent::activate( $character, $message, $spare3, $spare4 );
		
		if ( $rc == false )
			return false;					
		
		$this -> initialize( $character, $spare3, $spare4);
		
		return true;
		
	}
	
	function initialize( $character, $spare3 = null, $spare4 = null )
	{
		
		// add terrain
		
		$structure = StructureFactory_Model::create('terrain_1',  null);
		$structure -> locked = true;
		$structure -> character_id = $character -> id ;	
		$structure -> region_id = $character -> position_id ;
		
		// give items
		
		$item = Item_Model::factory( null, 'hoe' );				
		$item -> quality = 10;
		$item -> additem( 'character', $character -> id , 1 );
		
		$item = Item_Model::factory( null, 'seed_medherb' );						
		$item -> additem( 'character', $character -> id , 2 );
		
		$item = Item_Model::factory( null, 'fertilizer' );				
		$item -> additem( 'character', $character -> id , 2 );
		
		$item = Item_Model::factory( null, 'sickle' );				
		$item -> quality = 10;
		$item -> additem( 'character', $character -> id , 1 );
	
		// gestione per strutture di tipo terrain: metti stato incolto
		
		if ( $structure -> structure_type -> supertype == 'terrain' )
			$structure -> attribute1 = 0;					
		$structure -> save();
		
		// da un bonus velocità
		
		Character_Model::modify_stat_d(
			$character -> id,
			'speedbonus',
			10,
			null,
			null, 
			true,
			time()+1800);
				
		parent::initialize( $character, $structure -> id, null );		
		
	}
	
	function process_event_seedfield( $char, $event, $par, $instance )
	{
		kohana::log('debug', '-> Quest: processing event: ' . $event );
		if ( $par[0] -> tag == 'seed_medherb' )
			$this -> complete_step( $char, $instance, 0 );
		
		
	}
	
	function process_event_harvestfield( $char, $event, $par, $instance )
	{
		
		$str = (string) $instance -> stat2;
				
		kohana::log('debug', '-> Quest: processing event: ' . $event );
		$this -> complete_step( $char, $instance, 1 );		
	}
	
	
	function process_event_sellitemmarket( $char, $event, $par, $instance )
	{
		kohana::log('debug', '-> Quest: processing event: ' . $event );
		
		if ( 
			$par[0] -> cfgitem -> tag == 'medherb' and 
			$par[1] -> id == $char -> id )			
			$this -> complete_step( $char, $instance, 2 );		
	}
	
	
	function finalize_quest( $char, $instance ) 
	{
				
		// distruggi il terreno
		
		$terrain = ORM::factory('structure', $instance -> spare3 );		
		if ( $terrain -> loaded )
			$terrain -> destroy();
	
		$char -> modify_coins( +20, 'questreward' );
		
	}

}