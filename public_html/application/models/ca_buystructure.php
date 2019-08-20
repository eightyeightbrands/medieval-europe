<?php defined('SYSPATH') OR die('No direct access allowed.');


class CA_Buystructure_Model extends Character_Action_Model
{

	protected $immediate_action = true;
	protected $price = 0;
	protected $structure = null;
	protected $baseprice = null;
	protected $controllingcastle = null;
	
	// Effettua tutti i controlli relativi all' azione, sia quelli condivisi
	// con tutte le action che quelli peculiari
	// @input: array di parametri	
	// par0 : str Tipo struttura da comprare
	// par1 : obj Personaggio che compra
	// par2 : obj Regione dove si compra
	// @output: TRUE = azione disponibile, FALSE = azione non disponibile
	//          $messages contiene gli messagei in caso di FALSE	
	
	protected function check( $par, &$message )
	{ 
		
		// Metodo ereditato dal modello Character_Action. Controllo che non ci siano
		// altre azioni in corso				
	
		if ( ! parent::check( $par, $message ) )					
		{ return FALSE; }	
		
		$structureinstance = StructureFactory_Model::create( $par[0] );
		
		$this -> price = $structureinstance -> getPrice( $par[1], $par[2]);
		$this -> baseprice =  $structureinstance -> getBaseprice();
		
		// Se è una regione indipendente, non è possibile
		// comprare strutture
		
		if ( $par[2] -> kingdom -> image == 'kingdom-independent')
		{
			$message = kohana::lang('global.operation_not_allowed');
			return false;
		}
			
		// la struttura si può comprare?
		
		if ( $structureinstance -> getIsbuyable() == false )
		{
			$message = kohana::lang('global.operation_not_allowed');
			return false;
		}
		
		
		// il char ha abbastanza soldi?
		
		if ( ! $par[1] -> check_money( $this -> price ) )
		{
			$message = kohana::lang( 'charactions.global_notenoughmoney');
			return false;
		}
		
		// se il char è ostile, non può comprare proprietà
		
		$dr = Diplomacy_Relation_Model::get_diplomacy_relation( 
			$par[2] -> kingdom_id, $par[1] -> region -> kingdom_id ); 
		
		if ( $dr['type'] == 'hostile' )
		{
			$message = kohana::lang( 'ca_buystructure.error-hostile');
			return false;		
		}
	
		$this -> controllingcastle = $par[2] -> get_structure('castle' ); 
		
		if ( is_null( $this -> controllingcastle ) )
			$this -> controllingcastle = $par[2] -> get_structure('nativevillage' ); 
				
		// verifica se ha troppi terreni
		
		if ( $structureinstance -> structure_type -> parenttype == 'terrain' )
			if ( $par[1] -> count_my_structures( 'terrain' ) >= Kohana::config('medeur.maxterrains'))
			{
				$message = kohana::lang( 'ca_buystructure.error-fields_maxlimit');
				return false;		
			}
		
		
		// check per negozi
		
    if ( $structureinstance -> structure_type -> parenttype == 'shop' )
		{
			$ownedshops = $par[1] -> count_my_structures( 'shop' );
			
			// se ha già un numero di negozi > di quelli permessi errore.
			
			if ($ownedshops > Kohana::config('medeur.maxshops'))
			{
        $message = kohana::lang( 'ca_buystructure.error-shops_maxlimit');
        return false;
      }
			
			// se ha  un numero di negozi = a quelli permessi errore
			// a meno che la chiesa non abbia il bonus ecc.
			
			else if ($ownedshops == Kohana::config('medeur.maxshops'))		
			{
				
				$churchhasoraetlaborabonus = Church_Model::has_dogma_bonus( $par[1] -> church_id, 'craftblessing');
				$charhasfpcontribution = Character_Model::get_achievement( $par[1] -> id, 'stat_fpcontribution');
				
				if (
					$churchhasoraetlaborabonus == false
					or
					( is_null($charhasfpcontribution) or $charhasfpcontribution['stars'] < 3 )
				)        
				{
					$message = kohana::lang( 'ca_buystructure.error-shops_maxlimit');
					return false;
				}
			}
		}
		
		// verifica se il char ha già case in questo nodo
		
		if ( $structureinstance -> structure_type -> parenttype == 'house' )
		{
	
			$ownedhouses = Database::instance() -> query ( 
				"select s.id from structures s, structure_types st 
				 where s.structure_type_id = st.id
				 and   s.region_id = " . $par[2] -> id . " 
				 and   s.character_id = " . $par[1] -> id . " 
				 and   st.parenttype = 'house' " ); 
			
			if ( $ownedhouses -> count() > 0 )
			{
				$message = kohana::lang( 'ca_buystructure.error-housealreadyowned');
				return false;		
			}
		}		
				
		return true;
		
	}

	protected function append_action( $par, &$message )	{	}

	public function complete_action( $data ) {	}
		
	public function execute_action ( $par, &$message ) 
	{	
		
		// assegno la struttura al char
		
		$structureinstance = StructureFactory_Model::create( $par[0] );		
		$structureinstance -> character_id = $par[1] -> id;
		$structureinstance -> region_id = $par[2] -> id;
		
		// gestione per strutture di tipo terrain: metti stato incolto
		
		if ( $structureinstance -> structure_type -> parenttype == 'terrain' )
			$structureinstance -> attribute1 = 0;					
		
		$structureinstance -> save();
		
		// gestione per strutture di tipo negozio: crea la licenza
		
		if ( $structureinstance -> structure_type -> parenttype == 'shop' )		
		{
			$license = Item_Model::factory( null, 'scroll_propertylicense' );
			$license -> param1 = $structureinstance -> id . ';' . time() . ';' . $structureinstance -> getTag() ;	
			$license -> param2 = $structureinstance -> id; 
			$license -> additem( 'character', $par[1] -> id, 1 ) ; 
		}
	
		// toglie i soldi al char
		
		$par[1] -> modify_coins( - $this -> price, 'structurebuy' );
		
		// applica la tassa				
		
		$_par[0] = $this -> controllingcastle;
		$_par[1] = $par[1];
		$_par[2] = $this -> price;
		$_par[3] = 'structure';
		$_par[4] = null;
		$_par[5] = 'structurebuy';
		$_par[6] = $this -> baseprice;
		
		$net = Tax_ValueAdded_Model::apply( $_par );
						
		// eventi		
		
		Character_Event_Model::addrecord( $par[1] -> id, 'normal', '__events.charstructurebought' . 
			';__' . $structureinstance -> structure_type -> name . 
			';' . $this -> price );
		
		// Memorizza stat data acquisto struttura (per gestione prezzo di vendita)
		Structure_Model::modify_stat_d(
			$structureinstance -> id,
			'structurebought',
			time()
		);
		
		// pulisco cache
		
		$cachetag = '-regionstructures_' . $par[2] -> id;
		My_Cache_Model::delete( $cachetag );

		$message = kohana::lang('ca_buystructure.info-structurebought', $this -> price ); 
		
		return true;
		
	}
	
}
