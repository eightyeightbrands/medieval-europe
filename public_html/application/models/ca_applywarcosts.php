<?php defined('SYSPATH') OR die('No direct access allowed.');

class CA_Applywarcosts_Model extends Character_Action_Model
{
	
	protected $immediate_action = false;
	
	protected function check( $par, &$message ) { return true; }
	
	protected function append_action( $par, &$message ) {}	

	public function complete_action ( $data )
	{
		
		$wars = ORM::factory('kingdom_war') 
			-> 	where ( 'status', 'running') 
			->  find_all();
				
		foreach( $wars as $war )
		{	
			
			kohana::log('debug', "-> Applying cost for war: {$war->id}");
			$daysfromwarstart = round( (time() - $war -> start ) / (24*3600), 0 );
			kohana::log('debug', "Days from war:{$daysfromwarstart}-".Kohana::config('medeur.maxwarlength') );
			// Controlliamo prima se la guerra dura da piu di x giorni
			
			if ($daysfromwarstart > Kohana::config('medeur.maxwarlength') )
			{
				kohana::log('debug', "-> Finishing war {$war -> id} due to max length exceeded.");
				$war -> finish( 'maxlengthexceeded' );
				continue;
			}
		
			kohana::log('debug', "-> Days from start of war: {$daysfromwarstart}");
			
			if ( $daysfromwarstart == 0 )
				continue;
			
			$sourcekingdom = ORM::factory('kingdom', $war -> source_kingdom_id );
			$targetkingdom = ORM::factory('kingdom', $war -> target_kingdom_id );
			// trovo alleati regno attaccante
			$sourcekingdomallies = Kingdom_Model::get_allies($sourcekingdom->id);
			// aggiungo regno attaccante
			$sourcekingdomallies[] = $sourcekingdom -> id;
			
			$alliedcitizens = 0;
			
			foreach ( $sourcekingdomallies as $sourcekingdomally )
			{
				
				$ally = ORM::factory('kingdom', $sourcekingdomally);
				
				kohana::log('debug', "------ Kingdom: {$ally -> name} ------ ");
				
				// loop su tutti i citizens, escludo quelli con età < 30
				
				$allycitizens = Kingdom_Model::get_citizens( $ally -> id );
				
				foreach ( (array) $allycitizens as $allycitizen )
				{					
					
					$allycitizenobj = ORM::factory('character', $allycitizen -> id );
					
					kohana::log('debug', "Citizen: {$allycitizenobj -> name} age: " . 
						$allycitizenobj -> get_age( ) . " Last login: " . 
						date("d-m-Y", $allycitizenobj -> user -> last_login));
					
					if ( 
						$allycitizenobj -> get_age() > kohana::config('medeur.mindaystofight') 
						and
						$allycitizenobj -> user -> last_login > ( time() - (2 * 24 * 3600) )
					)
					{
						kohana::log('debug', 'Adding to allied citizens ready to fight and active');
						$alliedcitizens ++;
					}
				}
				
				

			}
			
			kohana::log('debug', "-> Allies have a total of {$alliedcitizens} citizens.");
						
			$cost = $daysfromwarstart * $alliedcitizens * 0.4;
			
			kohana::log('debug', "-> Total Cost: {$cost}.");
			
			$royalpalace = $sourcekingdom -> get_structure( 'royalpalace' );
			
			if ( $royalpalace -> get_item_quantity( 'silvercoin' ) >= $cost )
			{
				$royalpalace -> modify_coins( -$cost, 'warexpenses');
				kohana::log('debug', "-> Applying cost of {$cost} to kingdom: {$sourcekingdom -> name}");
			}
			else
			{
				kohana::log('debug', "-> Finishing war {$war -> id} due to lack of money.");
				$war -> finish( 'lackofwarfunds' );
			}

		}
		
		// reschedule action
		
		$action = ORM::factory('character_action') -> 
			where( array( 
				'character_id' => -1, 
				'action' => 'applywarcosts' ) ) -> find();
		
		
		$nextdate = time() + (24 * 3600);
		$action -> starttime = $nextdate;
		$action -> endtime = $nextdate;
		$action -> save();
				
		return true;
				
	}
	
	
		
}
