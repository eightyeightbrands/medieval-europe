	<?php defined('SYSPATH') OR die('No direct access allowed.');

	
class Character_NPC_Largedog_Model extends Character_NPC_Model
{
	
	protected $maxhealth = 50;	
	protected $respawntime = 18;
	protected $rate = 0.1;
	protected $maxnumber = 1000;	
	
	function create( $name )
	{
		
		parent::create( $name );				

		$this -> setStr(12);		
		$this -> setDex(10);
		$this -> setSex('M');
		$this -> setIntel(3);
		$this -> setCost(12);
		$this -> setCar(3);			
		$this -> setGlut(50);
		$this -> setEnergy(50);
		$this -> setMaxhealth(50);
		$this -> setHealth(50);
		$this -> setRespawntime(18);		
		$this -> setNpctag('largedog');
		$this -> setName ( $name );	
		$this -> setRate(0.1);
		$this -> setMaxnumber(1000);
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
				AND tag in ('meat')") -> as_array();
				
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
					
				
					$quantity = 1;
					
					$item -> removeitem( 'structure', $market -> id, $quantity );
					
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
		
		if ($r >= 1 and $r < 80)
		{
			;
		}
		
		if ($r >= 80 and $r <= 100)
		{
			$item['quantity'] = 1;
			$item['tag'] = 'meat';
		}			
		
		if ($item['quantity'] > 0)
		{
			kohana::log('debug', "-> NPC Die Aftermath: dropping {$item['quantity']} {$item['tag']}");
			$droppeditem = Item_Model::factory(null, $item['tag']);
			$droppeditem -> additem("region", $this -> getPosition_id(), $item['quantity']);			
		}
				
	}
	
}
?>