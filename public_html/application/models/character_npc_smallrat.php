	<?php defined('SYSPATH') OR die('No direct access allowed.');

	
class Character_NPC_Smallrat_Model extends Character_NPC_Model
{
	
	// i parametri = ai campi delle tabelle
	// vanno settati con get e set.
	
	protected $maxhealth = 15;	
	protected $respawntime = 120;
	protected $rate = 0.025;
	protected $maxnumber = 1000;	
		
	function create( $name )
	{		
		parent::create( $name );
		$this -> setName ( $name );	
		$this -> setNpctag( 'smallrat' );
		$this -> setStr(2);		
		$this -> setDex(2);
		$this -> setSex('M');		
		$this -> setIntel(10);		
		$this -> setCost(1);
		$this -> setCar(1);		
		$this -> setGlut(50);
		$this -> setEnergy(50);
		$this -> setHealth(10);		
		$this -> setName ( $name );			
	}
		
	function npcAI()
	{	
		
		parent::npcAI();
			
		kohana::log('debug', "-> ***** NPC: {$this -> name}: Called npcAI *****");
		
		// se è busy, return
		
		kohana::log('debug', '-> Checking if a pending action exists...');		
				
		$action = Character_Model::get_currentpendingaction( $this -> id );
			
		if ( is_array( $action ) )
		{			
			kohana::log('debug', "-> NPC: {$this -> name} pending action DOES exists ({$action['action']}), doing NOTHING." );			
			return;
		}
		
		// se è sotto 30 di sazietà , mangia cibo nel mercato 
		// se no si sposta
		
		if ($this -> glut < 30 )
		{
			// Trova il mercato nella cittÃ 
			
			$npccurrentposition = ORM::factory('region', $this -> position_id );
			
			$market = $npccurrentposition -> get_structure( 'market' );
			if ( is_null($market) )
			{
				kohana::log('debug', "-> NPC: {$this -> name}: No market found, moving away from here!");
				$this -> move();
			}
			else
			{
				// trovo cibo nel mercato
				
				$food = Database::instance() -> query ("
				SELECT i.id, i.seller_id 
				FROM items i, cfgitems ci
				WHERE ci.id = i.cfgitem_id 
				AND i.structure_id = {$market->id}
				AND ci.subcategory in ('rawfood', 'cookedfood')") -> as_array();
				
				if (count($food)== 0)
				{
					kohana::log('debug', "-> NPC: {$this -> name}: No food found.");
					
				}
				else
				{
					kohana::log('debug', "NPC: {$this -> name}: Yum, food found!");
					
					$stolenfood = array_rand( $food );			
					
					$item = ORM::factory('item', $food[$stolenfood] -> id);
					
					// Avvertiamo il proprietario
					
					Character_Event_Model::addrecord( 
						$item -> seller_id,
						'normal',
						'__events.npcatefood' . 
						';' . $this -> name .
						';1' .
						';__' . $item ->cfgitem -> name .
						';__' . $market -> region -> name);
					
					$item -> removeitem( 'structure', $market -> id, 1 );
					
					kohana::log('debug', "NPC: {$this -> name}: Eating a {$item -> cfgitem -> tag}.");
					
					$this -> modify_glut( $item -> cfgitem -> spare1 * 5 );
					
					$this -> save();					
					
				}
			}
		}	
			
		$r = mt_rand(1,2);
		
		if ( $r == 1 )
		{			
			kohana::log('debug', "NPC: {$this -> name} Moving away...");
			$this -> move();			
		}
		else
			kohana::log('debug', "NPC: {$this -> name} Staying here.");
		
	}
	
	public function die_aftermath()
	{
		
		$item = array( 
			'quantity' => 0,
			'tag' => ''
		);
		
		mt_srand();
		$r = mt_rand(1,100);

		kohana::log('debug', "-> NPC Die Aftermath: rolled: {$r}");
		
		if ($r >= 1 and $r < 6)
		{
			$item['quantity'] = 5;
			$item['tag'] = 'silvercoin';
		}
		if ($r >= 6 and $r < 16)
		{
			$item['quantity'] = 1;
			$item['tag'] = 'rattail';
		}
		if ($r >= 16 and $r < 17)
		{
			$item['quantity'] = 1;
			$item['tag'] = 'doubloon';
		}
		if ($r >= 17 and $r < 18)
		{
			$item['quantity'] = 1;
			$item['tag'] = 'meat';
		}
		if ($r >= 18 and $r <= 100)
		{
			;
		}
		
		if ($item['quantity'] > 0)
		{
			kohana::log('debug', "-> NPC Die Aftermath: dropping {$item['quantity']} {$item['tag']}");
			$droppeditem = Item_Model::factory(null, $item['tag']);
			$droppeditem -> additem("region", $this -> getPosition_id(), $item['quantity']);			
		}

		// evento per quest		
		$char = Character_Model::get_info( Session::instance()->get('char_id') );		
		GameEvent_Model::process_event( $char, 'killrat', null );			
		
	}
	
}
?>