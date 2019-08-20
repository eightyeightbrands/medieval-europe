	<?php defined('SYSPATH') OR die('No direct access allowed.');

	
class Character_NPC_JamesCotterel_Model extends Character_NPC_Model
{
	protected $headequipment = '';
	protected $bodyequipment = 'leather_armor_body';
	protected $legsequipment = 'leather_armor_legs';	
	protected $feetequipment = 'leather_armor_feet';
	protected $righthandequipment = 'knife';
	protected $lefthandequipment = '';	
	protected $silvercoins = 15;
	
	
	function create( $name )
	{
		
		parent::create( $name );				
		kohana::log('debug', '-> Called create...');
		$this -> setStr(15);
		$this -> setDex(15);
		$this -> setSex('M');
		$this -> setIntel(10);
		$this -> setCost(10);
		$this -> setCar(8);		
		$this -> setGlut(50);
		$this -> setEnergy(50);
		$this -> setMaxhealth(100);
		$this -> setHealth($this -> getMaxHealth());
		$this -> setRespawntime(24);		
		$this -> setNpctag('jamescotterel');
		$this -> setName ( 'James Cotterel' );	
		$this -> setRate(1);
		$this -> setMaxnumber(1);			
	}
		
	function npcAI()
	{		
			
		kohana::log('debug', "-> -------- NPC: {$this -> name}: Called npcAI --------");
		
		// 1. se è busy, return
		
		kohana::log('debug', '-> Checking if a pending action exists...');		
				
		$action = Character_Model::get_currentpendingaction( $this -> id );			
		if ( is_array( $action ) )
		{			
			kohana::log('debug', "-> NPC: {$this -> name} pending action DOES exists ({$action['action']}), doing NOTHING." );						
			return true;
		}
		
		// 2. Se ha meno di 15 silver coins, tenta di rubare.
		
		if ($this -> get_item_quantity( 'silvercoin') < 15 )
		{
			
			kohana::log('info', "-> NPC: {$this -> name} is broken, trying to rob someone...");
			
			// find characters here...
			$characters = ORM::factory('character') 
				-> where ( array(
					'position_id' => $this -> position_id,
					'id !=' => $this -> id ) ) -> find_all() -> as_array();
			
			if (count($characters) > 0 )
			{
				
				$robbed = $characters[array_rand( $characters )];
				
				kohana::log('info', "-> Trying to rob {$robbed->name}...");
				
				$ca = Character_Action_Model::factory("steal");											
				$par[0] = $this;
				$par[1] = $robbed;
					
				$rc = $ca -> do_action( $par,  $message );		
				kohana::log('info', "-> NPC: Result of Steal action: {$message}"); 							
				
				if ($rc == true)
				{
					kohana::log('info', "-> NPC: Steal, success, moving...");
					$this -> move();
					return true;
				}
			}
			
		}
		
		// 2. se è sotto 30 di sazietà , compra cibo nel mercato. Se non trova il mercato
		// si sposta.
		
		if ($this -> glut < 30 )
		{
			
			kohana::log('debug', "-> NPC: {$this -> name} is hungry, searching a market...");
			// Trova il mercato nella città
			
			$npccurrentposition = ORM::factory('region', $this -> position_id );
			
			$market = $npccurrentposition -> get_structure( 'market' );
			if ( is_null($market) )
			{
				kohana::log('debug', "-> NPC: {$this -> name}: No market found, moving away from here!");
				$this -> move();
				return true;
			}
			
			// trovo cibo nel mercato, ordinato dal più cheap.
			
			$food = Database::instance() -> query ("
			SELECT i.id, i.seller_id 
			FROM items i, cfgitems ci
			WHERE ci.id = i.cfgitem_id 
			AND i.structure_id = {$market->id}				
			AND ci.subcategory in ('cookedfood')
			ORDER by i.price asc limit 1
			") -> as_array();
			
			if (count($food)== 0)
			{
				kohana::log('debug', "-> NPC: {$this -> name}: No food found.");					
			}
			else
			{
				kohana::log('debug', "NPC: {$this -> name}: Yum, food found!");					
				$item = ORM::factory('item', $food[0] -> id);
				
				$ca = Character_Action_Model::factory("marketbuyitem");											
				$par[0] = $market;
				$par[1] = $this;
				$par[2] = $item;
				$par[3] = 1;
				
				$rc = $ca -> do_action( $par,  $message );
				kohana::log('info', "-> NPC: Result of Buy Food action: {$message}"); 							
				
				if ($rc == true )
				{
				
					// Avvertiamo il proprietario
					
					Character_Event_Model::addrecord( 
						$item -> seller_id,
						'normal',
						'__events.npcboughtfood' . 
						';' . $this -> name .
						';1' .
						';__' . $item ->cfgitem -> name .
						';__' . $market -> region -> name);
					
					$item -> removeitem( 'structure', $market -> id, 1 );
					
					kohana::log('debug', "NPC: {$this -> name}: Eating a {$item -> cfgitem -> tag}.");
					
					$this -> modify_glut( $item -> cfgitem -> spare1);
					$this -> modify_energy( $item -> cfgitem -> spare1);
					
					$this -> save();										
				}
			}
		}
			
		// 3. Move (?)
		
		$r = mt_rand(1,2);
		
		if ( $r == 1 )
		{			
			kohana::log('debug', "NPC: {$this -> name} Moving away...");
			$this -> move();			
		}
		else
			kohana::log('debug', "NPC: {$this -> name} Staying here.");
		
		return true;
		
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
		
		if ($r >= 1 and $r <= 10)
		{
			$item['quantity'] = 5;
			$item['tag'] = 'silvercoin';
		}
		if ($r >= 11 and $r <= 30)
		{
			$item['quantity'] = 1;
			$item['tag'] = 'rattail';
		}
		if ($r >= 31 and $r <= 35)
		{
			$item['quantity'] = 1;
			$item['tag'] = 'doubloon';
		}
		if ($r >= 36 and $r <= 50)
		{
			$item['quantity'] = 1;
			$item['tag'] = 'meat';
		}
		if ($r >= 51 and $r <= 100)
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
	
	function respawn()
	{
		
		kohana::log('debug', "-> Respawning " . $this->getName());
		
		$silvercoins = $this -> get_item_quantity('silvercoin');
		if ($silvercoins < 15 )
			$this -> modify_coins(15-$silvercoins);		
		
		parent::respawn();
		
	}
	
}
?>