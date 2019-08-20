<?php defined('SYSPATH') OR die('No direct access allowed.');

class character_premiumbonus_Model extends ORM
{
	protected $sorting = array('endtime' => 'desc');

	/** 
	* add a bonus
	* @param character oggetto char a cui il bonus è applicato
	* @param structure is struttura a cui il bonus è applicato
	* @param bonustype tipo di bonus
	* @param doubloons dobloni spesi
	* @delta ha due valenze, se si vuole estendere è il delta altrimenti funziona
	*        come valore esplicito
	* @param1 param1
	* @param2 param2
	* @forceadd forza l' aggiunta del record
	* @return
	*/
	
	function add( 	
		$character, 
		$structure_id = null, 
		$bonustype,
		$doubloons,
		$delta,
		$param1 = null,
		$param2 = null,
		$forceadd = false)
	{
		$bonus = ORM::factory( 'character_premiumbonus' )
			-> where ( array( 
					'character_id' => $character -> id,
					'bonus' => $bonustype,
					'endtime >' => time() )) -> find();
		
		kohana::log('debug', '-> bonus exists ' . $bonus -> loaded );
		kohana::log('debug', '-> force flag ' . $forceadd );
		
		if ( $bonus -> loaded and $forceadd == false )
		{		
			$bonus -> endtime += $delta;
			$bonus -> doubloons += $doubloons;
			$bonus -> param1 = $param1;
			$bonus -> param2 = $param2;
			$bonus -> save();
		}
		else
		{
			$b = new character_premiumbonus_Model();			
			$b -> user_id = $character -> user_id;
			$b -> structure_id = $structure_id;
			$b -> character_id = $character -> id;
			$b -> bonus = $bonustype;
			$b -> type = 'premium';
			$b -> starttime = time();
			$b -> endtime = $b -> starttime + $delta;
			$b -> doubloons = $doubloons;
			$b -> param1 = $param1;
			$b -> param2 = $param2;
			$b -> save();
			
		}
		
		$cachetag = '-charinfo_' . $character -> id . '_bonuses' ; 
		My_Cache_Model::delete( $cachetag ); 
		
		return;
		
	}
	
	
	
}
