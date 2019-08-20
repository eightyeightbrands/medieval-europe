	<?php defined('SYSPATH') OR die('No direct access allowed.');

class Quest_Crafting_Model extends Quest_Model
{
	protected $name = 'crafting';
	protected $stepsnumber = 2;
	protected $strinit = '00--------';
	protected $id = 3;	
	protected $author_id = 1;
	protected $path = 'tutorial';
	
	function activate( $character, &$message, $spare3 = null, $spare4 = null )
	{
		
		$quest = QuestFactory_Model::createQuest('training');		
		if ($quest -> get_status($character) != 'completed')
		{
			$message = 'quests.error-trainingnotcompleted';
			return false;			
		}
		
		// aggiunge bottega Locanda
		
		
		$structure = StructureFactory_Model::create('chef_1', null);
		
		$structure -> locked = true;
		$structure -> character_id = $character -> id ;	
		$structure -> region_id = $character -> region_id ;		
		$structure -> save();		
		$spare3 = $structure -> id;
				
		$item = Item_Model::factory( null, 'cookingpot' );		
		$item -> quality = 10;		
		$item -> additem( 'character', $character -> id , 1 );
		
		// aggiunge gli items
		
		$item = Item_Model::factory( null, 'wheat_bag' );		
		$item -> additem( 'structure', $structure -> id , 4 );
		
		$item = Item_Model::factory( null, 'wood_piece' );		
		$item -> additem( 'structure', $structure -> id , 2 );		
		
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
	
	function process_event_craft( $char, $event, $par, $instance )
	{
		
		$produceditems = 0;
		
		if ( !is_null( $instance -> spare4 ))
			$produceditems = $instance -> spare4;
		
		if ( $par[0] -> cfgitem -> tag == 'bread' )		
			if ( $produceditems + $par[1] >= 14 )
			{
				$produceditems += $par[1];
				$instance -> spare4 = null;
				$instance -> save();				
				
				$this -> complete_step( $char, $instance, 0 );
			}
			else
			{
				$produceditems += $par[1];
				$instance -> spare4 = $produceditems;
				$instance -> save();
			}
		
	}
	
	function process_event_sellitemmarket( $char, $event, $par, $instance )
	{
		kohana::log('debug', '-> Quest: processing event: ' . $event );
		
		if ( 
			$par[0] -> cfgitem -> tag == 'bread' and 
			$par[1] -> id == $char -> id )			
			$this -> complete_step( $char, $instance, 1 );		
	}
	
	
	function finalize_quest( $char, $instance ) 
	{
	
		$char -> modify_coins( +80, 'questreward' );
		// distruggi il negozio regalato (doniamo le scarpe)
		
		$shop = ORM::factory('structure', $instance -> spare3 );
		if ( $shop -> loaded )
			$shop -> destroy();
		
	}
	
}