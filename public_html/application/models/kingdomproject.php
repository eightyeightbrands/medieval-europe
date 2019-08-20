<?php defined('SYSPATH') OR die('No direct access allowed.');

class Kingdomproject_Model extends ORM
{
	protected $has_one = array( 'cfgkingdomproject' );		
	protected $belongs_to = array( 'region' );		
	
	/**
	* Ritorna info sul progetto
	* @param none
	* @return array $info
	*/
	
	function get_info()
	{
		$info = array( 
			'id' => null,
			'neededitems' => null, 
			'builtstructure' => null,
			'project' => null,
			'status' => null,
			'workedhours' => 0,
			'totalhours' => 0,
			'workedhours_percentage' => 0,
			'region' => null
		);
		
		$average_percentage = 0;
		$n = 0;
		
		$structure = StructureFactory_Model::create( null, $this -> structure_id );
		
		$info['id'] = $this -> id;
		
		// Oggetti richiesti
		foreach( $this -> cfgkingdomproject -> cfgkingdomproject_dependency as $dep )
		{
			$info['neededitems'][$dep->cfgitem->name]['general'] = $dep;			
			$info['neededitems'][$dep->cfgitem->name]['providedquantity'] = $structure -> get_item_quantity( $dep -> cfgitem -> tag );			
			$info['neededitems'][$dep->cfgitem->name]['percentage'] = min(100, round ($info['neededitems'][$dep->cfgitem->name]['providedquantity'] /$dep->quantity,4) * 100);
			$n++;
		}
		
		// struttura prodotta
		// per i progetti religiosi è necessario anche differenziare la struttura per chiesa.
		
		
		
		if ( in_array( $this -> cfgkingdomproject -> tag, array( 'religion_2', 'religion_3', 'religion_4' ) ) )
		{
			$parentstructure = StructureFactory_Model::create( null, $structure -> parent_structure_id ); 			
			
			$info['builtstructure'] = 
			ORM::factory('structure_type') -> where( 
				array( 
					'type' => $this -> cfgkingdomproject -> tag,
					'church_id' => $parentstructure -> structure_type -> church_id ) ) -> find();
						
		}
		else
		{			
			$info['builtstructure'] = ORM::factory('structure_type') 
				-> where( 'type', $this -> cfgkingdomproject -> tag ) -> find(); 
		}
		
		$info['project'] = $this ;
		$info['region'] = $structure -> region ;
		$info['status'] = $this -> status;
		$info['workedhours'] = $this->workedhours ;
		$info['totalhours'] = $this -> cfgkingdomproject -> required_hours;
		$info['workedhours_percentage'] = min(100, round($this -> workedhours / $this -> cfgkingdomproject -> required_hours, 4)*100);
		
		return $info;
	
	}
	
	/**
	* determina se nella struttura ci sono tutti gli item necessari 
	* e quindi se si può costruire.
	* @param none
	* @return boolean
	*/
	
	function is_buildable()
	{
	
		if ( $this -> status == 'building' )
			return true;
			
		$structure = StructureFactory_Model::create( null, $this -> structure_id );		
		foreach( $this->cfgkingdomproject -> cfgkingdomproject_dependency as $dep )
			if ( $structure -> get_item_quantity( $dep -> cfgitem -> tag) < $dep -> quantity )
				return false;
		
		kohana::log('debug', '-> structure is buildable, wiping items.');
		
		// project is buildable, change status and wipe out resources.		
		foreach( $this->cfgkingdomproject -> cfgkingdomproject_dependency as $dep )
		{

			$item = Item_Model::factory( null, $dep -> cfgitem -> tag );			
			$item->removeitem( 'structure', $structure->id, $dep -> quantity );
		}
			
		// change status
		
		$this -> status = 'building' ;
		$this -> save() ;
		
		return true;
	
	}
	
		/**
	* ritorna gli slot disponibili per la costruzione
	* controlla il denaro nella struttura e divide per il costo impostato.
	* @param none
	* @return array $slots
	* 	slots[3] => numero di slots di 3 ore disponibili 
	* 	slots[6] => numero di slots di 6 ore disponibili 
	* 	slots[9] => numero di slots di 9 ore disponibili 
	*/
	
	function get_slots()
	{			
			
		$structure = StructureFactory_Model::create( null, $this -> structure_id );		
		$coins = $structure -> get_item_quantity( 'silvercoin' );		
		
		$slots['3'] = floor( $coins / ( max(1, $this -> hourlywage) * 3) );
		$slots['6'] = floor( $coins / ( max(1, $this -> hourlywage) * 6) );
		$slots['9'] = floor( $coins / ( max(1, $this -> hourlywage) * 9) );
		
		//kohana::log('debug', 'slots: ' . kohana::debug($slots));
		
		return $slots;
	
	}
	/**
	* completa un progetto
	* @param $buildingsite_id ID del building site
	* @param type tipo di completamento (creation o upgrade)
	* @return none
	*/
	
	function complete( $buildingsite_id, $type = 'creation' )
	{
			// codice di guardia			
			if ( $this -> status == 'completed' )
				return;
				
			$buildingsite = ORM::factory('structure', $buildingsite_id );		
			$cfgstructure = ORM::factory('structure_type', $buildingsite -> attribute1 );			
			$db = Database::instance();
			
			////////////////////////////////////////////////////////////////
			// se il tipo struttura è training grounds o accademia,
			// setta il costo per ora di default (1 citizens, 2 foreigners)
			////////////////////////////////////////////////////////////////
			
			if ( in_array( $cfgstructure -> supertype , array( 'academy', 'trainingground' ) ) )
			{
				$buildingsite -> attribute2 = 1;
				$buildingsite -> attribute3 = 2;			
			}
			
			////////////////////////////////////////////////////////////////
			// se il tipo struttura è castello
			// il villaggio della regione va cancellato
			////////////////////////////////////////////////////////////////
			
			if ( $cfgstructure -> type == 'castle' )
			{
				$nativevillage = $buildingsite -> region -> get_structure('nativevillage');
				if ( $nativevillage -> loaded ) 
					$nativevillage -> destroy();			
			}
			
			// Rimuovi grant del corrente owner
			
			$cachetag = '-charstructuregrant_' . $buildingsite -> character_id . '_' . $buildingsite -> id;
			My_Cache_Model::delete($cachetag);
			
			/////////////////////////////////////////////////////////////////////
			// Scrivi eventi prima di distruzione
			/////////////////////////////////////////////////////////////////////			
			
			if ( $cfgstructure -> subtype == 'church' )
				Character_Event_Model::addrecord( 
				null, 
				'announcement', 
				'__events.churchcommunityproject_completed' . 				
				';__' . $cfgstructure -> name . 
				';__' . $buildingsite -> region -> name,
				'evidence'
				);
			else
				Character_Event_Model::addrecord( 
				null, 
				'announcement', 
				'__events.communityproject_completed' . 
				';__' . $buildingsite -> region -> kingdom -> get_name()  . 
				';__' . $cfgstructure -> name . 
				';__' . $buildingsite -> region -> name,
				'evidence');

			if ( $type == 'upgrade' )			
			{
				$res = Database::instance() -> query( 
					"select s.* from structures s, structure_types st 
					where s.structure_type_id = st.id 
					and   s.region_id = " . $buildingsite -> region_id . "
					and   st.supertype = '" . $cfgstructure -> supertype . "'" ) -> as_array();
				Database::instance() -> query ( "update structures set structure_type_id = " . $cfgstructure -> id . " 	where id = " . $res[0] -> id );						
			}
			else			
			{
				$buildingsite -> structure_type_id = $cfgstructure -> id ;
				$buildingsite -> character_id = null;
				$buildingsite -> save();
			}
			
			/////////////////////////////////////////////////////////////////////
			// trasferisci tutti gli oggetti (rimanenti) nella 
			// struttura alla struttura padre
			/////////////////////////////////////////////////////////////////////
			
			$controllingstructure = ORM::factory('structure', $buildingsite -> parent_structure_id );			
			if ( $buildingsite -> loaded )
				foreach ( $buildingsite -> item as $item )
				{			
					$item -> structure_id = $controllingstructure -> id ;
					$item -> save();
					
					Structure_Event_Model::newadd( $controllingstructure -> id, 
						'__events.completedprojectitemtransfered' . ';' .
						'__' . $cfgstructure -> name . ';' . 
						'__' . $buildingsite -> region -> name . ';' .
						$item -> quantity . ';' .
						'__' . $item -> cfgitem -> name				
					);
				}
			
			
			///////////////////////////////////////////////////////////////////
			// se esiste già il tipo di struttura è una upgrade. quindi va 
			// aggiornato lo structure_id del progetto e spostate tutte le 
			// statistiche
			////////////////////////////////////////////////////////////////////
			
			if ( $type == 'upgrade' )
			{
			
				$this -> structure_id = $res[0] -> id;
				$this -> status = 'completed';
				$this -> save();
				
				$buildingstats = ORM::factory('structure_stat') -> where (
					array( 'structure_id' => $buildingsite -> id ) ) -> find_all ();				
				
				// scorro tutte le statistiche del cantiere. Se c'era già una statistica
				// per il player sulla struttura originale di livello 1 devo sommare le
				// ore, altrimenti aggiungerle.
				
				foreach ( $buildingstats as $buildingstat )
				{
					$oldstat = ORM::factory('structure_stat') -> 
						where ( array( 
							'name' => 'workinghours',
							'structure_id' => $res[0] -> id ) ) -> find();
						
					// se c'è sommo le ore
					if ( $oldstat -> loaded )
					{
						$oldstat -> value += $buildingstat -> value ;
						$oldstat -> save();
					}
					else
					{
						$buildingstat -> structure_id = $res[0] -> id ;
						$buildingstat -> save();
					}
				}
				
				// distruggo il cantiere, se è una upgrade.
								
					$buildingsite -> destroy();
				
			}
			else
			{
			
				$this -> end = time();
				$this -> status = 'completed' ; 				
				$this -> save();
			}
			
			return;
			
	}

}
?>
