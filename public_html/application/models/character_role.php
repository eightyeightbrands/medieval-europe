<?php defined('SYSPATH') OR die('No direct access allowed.');

class Character_Role_Model extends ORM
{	
	protected $belongs_to = array( 'character', 'region', 'structure' );	
	protected $sorting = array('id' => 'desc');
	
	const CHURCH_LEVEL_2_NEEDED_CONTRIBUTEDFPS = 940;
	const CHURCH_LEVEL_3_NEEDED_CONTRIBUTEDFPS = 520;
	const CHURCH_LEVEL_4_NEEDED_CONTRIBUTEDFPS = 220;

	/**
	* ritorna gli FP necessari per assegnare o rimuovere un ruolo
	* @param char oggetto char
	* @param mode assign|revoke
	* @param role tag ruolo
	* return numero di FP
	*/
	
	function get_requiredfp( $char, $mode, $tag )
	{
		if ( $mode == 'assign' )
		{
			switch ( $tag )
			{
				case 'church_level_2': $cost = 50; break;
				case 'church_level_3': $cost = 25; break;
				case 'church_level_4': $cost = 20; break;
				default: break;
			}
		}
		elseif ( $mode == 'revoke' )
		{
			$role = $char -> get_current_role();
			switch ( $role -> tag )
			{
				case 'church_level_2' : 
					$cost = 50 + round( ( time() - $role -> begin ) / (  24 * 3600 ) ) ; break;
				case 'church_level_3' : 
					$cost = 25 + 0.5 * round( ( time() - $role -> begin ) / (  24 * 3600 )) ; break;				
				case 'church_level_4' : 
					$cost = 20 + 0.2 * round( ( time() - $role -> begin ) / (  24 * 3600 )) ; break;				
				default: break;
			}
		}
		return intval($cost);
		//var_dump ( $cost ); exit; 
		
	}
	
	/**
	* ritorna i denari necessari per assegnare o rimuovere un ruolo
	* @param char oggetto char
	* @param mode assign|revoke
	* @param role tag ruolo
	* return numero di denari necessari
	*/
	
	function get_requiredcoins( $char, $mode, $tag )
	{
		if ( $mode == 'assign' )
		{
			switch ( $tag )
			{
				case 'vassal': $cost = 50; break;
				case 'judge': $cost = 25; break;
				case 'sheriff': $cost = 20; break;
				case 'towerguardian': $cost = 15; break;
				case 'academydirector': 
				case 'drillmaster': $cost = 10; break;
				default: break;
			}
		}
		elseif ( $mode == 'revoke' )
		{
			$role = $char -> get_current_role();
			
			switch ( $role -> tag )
			{
				case 'vassal' : 
					$cost = 50 + round( ( time() - $role -> begin ) / (  24 * 3600 ) ) ; break;
				case 'judge' : 
					$cost = 25 + 0.5 * round( ( time() - $role -> begin ) / (  24 * 3600 )) ; break;
				case 'sheriff' : 
					$cost = 20 + 0.3 * round( ( time() - $role -> begin ) / (  24 * 3600 )) ; break;
				case 'towerguardian' : 
					$cost = 15 + 0.3 * round( ( time() - $role -> begin ) / (  24 * 3600 )) ; break;	
				case 'academydirector' : 
				case 'drillmaster':
					$cost = 10 + 0.1 * round( ( time() - $role -> begin ) / (  24 * 3600 )) ; break;								
				default: break;
			}
		}
				
		return intval($cost);
	}
		
	
	/** 
	* Returns character title
	* @param boolean $translate if true, translates directly the title otherwise he just saves
	*        the translation
	* @return string $title translated title
	*/
	
	function get_title( $translate = false ) 
	{
		$title = '';		
		
		//////////////////////////////////////////
		// Titoli per ruoli del Regno
		//////////////////////////////////////////
		
		if ( in_array ( $this -> tag, 
			array(
				'king', 
				'seneschal', 
				'constable', 
				'chancellor', 
				'chamberlain', 
				'treasurer', 
				'ambassador',					
		) ) )
		{
			
			$kingdom = ORM::factory('kingdom', $this-> region -> kingdom -> id );
			
			if ( in_array($this -> tag, array('king')))
			{
				if ( $translate )
					$title .= kohana::lang( $kingdom -> title . '_' . strtolower($this -> character -> sex) ) . ' ' ;
				else
					$title .= ';__' . $kingdom -> title . '_' . strtolower($this -> character -> sex) . '; ';
			}
			else
			{				
				if ( $translate )
					$title .= kohana::lang( 'global.' . $this -> tag . '_' . strtolower($this -> character -> sex) ) . ' ' ;
				else
					$title .= ';__' . 'global.' . $this -> tag . '_' . strtolower($this -> character -> sex) . '; ';
			}
			
			// regno
			
			if ( $translate )			
				$title .= kohana::lang( $kingdom -> get_name()  );
			else
				$title .= ';__' . $kingdom -> get_name()  ;
			
		}
		
		//////////////////////////////////////////		
		// Titoli Role Play a livello Regione o 
		// senza Regione. Per questi non è 
		// prevista una traduzione.
		//////////////////////////////////////////
		
		
		elseif ( in_array( $this -> tag, array( 			
			'prince', 
			'marquis', 
			'duke', 
			'earl', 
			'viscount', 
			'baron', 
			'lord', 
			'knight') ))
		{
			// Verifico se esiste un titolo
			// nobiliare personalizzato
			
			$title = ORM::factory('kingdom_nobletitle')
			->where
			(
				array
				( 
				'kingdom_id' => $this->kingdom_id,
				'title' => $this->tag
				)
			)
			-> find();
		
			$temprole = '';
			
			// Check: esiste il titolo personalizzato
			if ($title->loaded) 
			{
				// Ritorno il titolo in base al sesso del character
				if ($this->character->sex == 'M')
				{ $temprole = $title->customisedtitle_m; }
				else
				{ $temprole = $title->customisedtitle_f; }
			}
			else
			{
				// Non esiste il ruolo personalizzato
				// Uso i nomi standard previsti dal gioco
				$temprole = kohana::lang('global.' . $this -> tag . '_' . strtolower( $this -> character -> sex));
			}

			// Se è stato specificato un feudo
			// lo aggiungo alla stringa
			if ( !is_null( $this -> place ) )
			{
				$temprole .= ' ' . $this -> place;
			}
			
			// Ritorno la stringa costruita
			
			$title = $temprole;
		}
		// titoli che dipendono dalla regione		
		elseif ( in_array( $this -> tag, array( 			
			'vassal', 
			'sheriff', 
			'towerguardian',
			'drillmaster', 
			'academydirector', 
			'judge', 
			'chaplain', 
			'prefect', 
			'customsofficer',
			'lieutenant', 
			'bailiff', 
			'trainer', 
			'assistant',
			)) )
		{
			if ( $translate )				
				$title .= kohana::lang( 'global.' . $this -> tag . '_' . strtolower( $this -> character -> sex )) . ' ' . kohana::lang($this -> region -> name) ;
			else
				$title .= ';__' . 'global.' . $this -> tag . '_' . strtolower( $this -> character -> sex ) . '; ' . ';__' . $this -> region -> name ;
		}
		
		// titoli chiesa
		// Parametri: TAG - CHURCH - REGIONE
		
		elseif ( in_array( $this -> tag, array( 
			'primate', 
			'generalvicar', 
			'greatinquisitor', 
			'greatalmoner', 
			'ambassadorchurch', 
			'inquisitor', 
			'almoner', 
			'monk', 
			'acolyte'
			) ) )
		{
		
			if ( $translate )		
				$title .= 
					kohana::lang( 'global.' . $this -> tag . '_' . strtolower( $this -> character -> sex )) . ' ' . 
					kohana::lang('religion.church-' . $this -> character -> church -> name) . ' ' . kohana::lang( $this -> region -> name );
			else			
				$title .= 
					';__' . 'global.' . $this -> tag . '_' . strtolower( $this -> character -> sex ) . '; ' . 
					';__' . 'religion.church-' . $this -> character -> church -> name . '; ' . ';__' . $this->region -> name ;				
		}
		elseif ( in_array( $this -> tag, array( 
	
			'church_level_1', 
			'church_level_2', 
			'church_level_3', 
			'church_level_4',
		)))
		{
			if ( $translate )		
				$title .= 
					kohana::lang( 'global.' . $this -> tag . '_' . $this -> character -> church -> name) . ' ' . 
					kohana::lang('religion.church-' . $this -> character -> church -> name);
			else			
				$title .= 
					';__' . 'global.' . $this -> tag . '_' . $this -> character -> church -> name . '; ' . 
					';__' . 'religion.church-' . $this -> character -> church -> name;					
		}
		
		//var_dump( $title ); exit; 
		
		return $title;
		
	}
		
	/**
	* Inizia un incarico
	* @param obj $char Personaggio che ha il ruolo
	* @param string $roletag il ruolo che viene dato
	* @param obj $region oggetto regione dell' incarico che viene dato
	* @param int $structure_id Struttura legata all' incarico
	* @param string luogo dell' investitura (Parigi ecc) (stringa)
	* @param boolean flag che determina se il ruolo è GDR
	*/
	
	function start( $char, $roletag, $region, $structure_id, $place, $is_gdr )
	{	
		
		$cr = new Character_Role_Model();		
		$cr -> character_id = $char->id;
		$cr -> tag = $roletag;
		$cr -> region_id = $region -> id;
		$cr -> kingdom_id = $region -> kingdom -> id;
		$cr -> structure_id = $structure_id;
		$cr -> current = true;
		$cr -> begin = time();
		$cr -> church_id = $char -> church_id;
		$cr -> place = $place;
		$cr -> gdr = $is_gdr;
		$cr -> save();
		
		// Eseguo i controlli solo se
		// mi trovo davanti ad un ruolo vero e non gdr
		
		if ( ! $is_gdr )
		{
			// reload char (changed role!)
			
			$character = ORM::factory('character', $char -> id ); 
	
			///////////////////////////////////////////
			// trovo la strutture controllate dal ruolo
			// e le assegno al char
			///////////////////////////////////////////

			//////////////////////////////////////////////		
			// Qui do il controllo, per tutte le regioni 
			// controllate dal char, di tutte le strutture
			// a lui spettanti.
			// Esempio: il vassallo dovrà controllare, in 
			// tutte le regioni che controlla:
			// castello, e progetti a lui assegnati
			// Esempio: un cardinale controllerà solo la
			// regione dove c'è il  pal. cardinalizio		
			//////////////////////////////////////////////
				
			$controlledstructures = $cr -> get_controlledstructures(); 
			
			foreach ( (array) $controlledstructures as $controlledstructure )
			{
				kohana::log('info', 
				'-> Giving control of structure ID: ' . $controlledstructure -> id . '-' . $controlledstructure -> structure_type -> type . ', region: ' . $controlledstructure -> region -> name . ' to char: ' . $character -> id ); 				
				$controlledstructure -> character_id = $character -> id;				
				$controlledstructure -> save();
				
				// pulisco la cache delle grant
		
				$cachetag = '-charstructuregrant_' . $controlledstructure -> character_id . '_' . $controlledstructure -> id;
				My_Cache_Model::delete($cachetag);
				
			}
		}
			
	}
	
	/**
	* Termina il ruolo
	* @param char oggetto char
	* @retun none
	*/
	
	function end()
	{
		// Eseguo i controlli solo se
		// mi trovo davanti ad un ruolo vero e non gdr
		
		kohana::log('debug', '------ ENDING ROLE ------');		
		kohana::log('debug', '-> Ending role: ' . $this -> tag . ' for char: ' . $this -> character -> name . 
			'structure_id: ' . $this -> structure_id );
		
		if ( ! $this -> gdr )
		{
			// trova tutte le strutture che controlla il char
			// e gli toglie il controllo
			
			kohana::log('debug', '-> Finding Controlled Structures...');
			$controlledstructures = $this -> get_controlledstructures();
			
			foreach ( (array) $controlledstructures as $structure )
			if ( !is_null ( $structure) ) 
				if ( $structure -> structure_type -> subtype != 'player' ) 
				{
					kohana::log('debug', '-> End Role: Revoking control of structure [' . $structure -> structure_type -> type 
					. '] in: [' . $structure -> region -> name . ']'); 
					
					// pulisco la cache delle grant
		
					$cachetag = '-charstructuregrant_' . $structure -> character_id . '_' . $structure -> id;
					My_Cache_Model::delete($cachetag);	
					
					// tolgo ownership
					
					$structure -> character_id = null;
					$structure -> save();

				}

			// deequippa item legati al ruolo
			
			kohana::log('debug', '-> EndRole: Unequipping Items linked to role...');
		
			foreach ( $this -> character -> item as $item ) 
			{
				if ( !is_null( $item -> cfgitem -> linked_role ) and $item -> cfgitem -> linked_role != $this -> tag )				
				{
					$item -> equipped = 'unequipped' ;
					$item -> save();
				}
			}
		}
		
		// Elimina tutte le grant legate strettamente al ruolo
		
		kohana::log('debug', '-> EndRole: Removing all grants linked to role...');
		
		$rolegrants = ORM::factory('structure_grant') -> where ( 
			array( 
				'character_id' => $this -> character_id,
				'grant' => $this -> tag,
				'expiredate>' => time() ) ) -> find_all();
		
		foreach ( $rolegrants as $rg )
		{
			$rg -> expiredate = time();
			$rg -> save();
		}
		
		// termina il ruolo
		
		kohana::log('debug', '-> EndRole: Completing...');
		
		$this -> current = false;
		$this -> end = time();
		$this -> save();
		
		return;
	}
	
	
	/** 
	* verifica se il char può assumere il ruolo
	* @param $char oggetto char che deve essere nominato
	* @param $role tag ruolo (come da structure_types.associated_role_tag)	
	* @church oggetto church
	* @param $message eventuale messaggio d'errore
	* @return true false
	*/
	
	function check_eligibility( $char, $role, $church, &$message )
	{
		/**
		* Nota: abbassato temporaneamente requirement per church level 1
		* da 120 a 50
		*/
		
		$requirements = array
		(
			'church_level_4' => array ( 'car' => 10, 'age' => kohana::config('medeur.churchlevel4minage', 7) ),
			'sheriff'     => array ( 'str' => 11, 'age' => kohana::config('medeur.guardcaptainminage', 7) ),
			'towerguardian' => array( 'str' => 10, 'age' => kohana::config('medeur.towerguardianminage', 7)  ),
			'judge'       => array ( 'car' => 12, 'age' => kohana::config('medeur.judgeminage', 30)  ),
			'church_level_3'      => array ( 'car' => 12, 'age' => kohana::config('medeur.churchlevel3minage', 30)  ),
			'vassal'      => array ( 'car' => 14, 'age' => kohana::config('medeur.vassalminage', 60)  ),
			'church_level_2' => array ( 'car' => 14, 'age' => kohana::config('medeur.churchlevel2minage', 30)  ),
			'academydirector' => array ( 'intel' => 12, 'age' => kohana::config('medeur.academydirectorminage', 30)  ),
			'drillmaster' => array ( 'str' => 12, 'age' => kohana::config('medeur.drillmasterminage', 30)  ),
			'king'        => array ( 'car' => 16, 'age' => kohana::config('medeur.kingminage', 90)  ),			
			'church_level_1' => array ( 'car' => 16, 'age' => kohana::config('medeur.churchlevel1minage', 50)  ),
		);
		
		// controlla caratteristiche
		
		$req = $requirements[ $role ] ;
		
		kohana::log( 'debug', '-> Eligibility - Checking ' . key($req) . ' for ' . $char -> name ); 
		kohana::log( 'debug', '-> Eligibility - Char has: ' . $char -> get_attribute( key($req) ) . ' required: ' . current( $req ) );
		
		/////////////////////////////////
		// Controllo attributi
		/////////////////////////////////
		
		if ( $char -> get_attribute( key($req) ) < current( $req ) )
		{
			$message = kohana::lang('character.' . key($req) . '_requirementfailed', $char -> name, current($req));
			kohana::log('info', '-> Char has not enough ' . key( $req ) ); 
			return false;
		}
		
		next( $req );
		kohana::log( 'info', '-> Char has: ' . $char -> get_age() . ' required: ' . current( $req ) );
		
		/////////////////////////////////
		// Controllo anzianità
		/////////////////////////////////
		
		if ( $char -> get_age() < current( $req ) )
		{
			$message = kohana::lang('character.agerequirementfailed', current( $req ) );
			kohana::log('info', '-> Char is not old enough.' ); 
			return false;
		}
		
		//////////////////////////////////////////////
		// controlli relativi ad incarichi religiosi	
		//////////////////////////////////////////////				
		
		if ( in_array( $role, array('church_level_2', 'church_level_3', 'church_level_4' ) ) )
		{
			// appartiene alla stessa chiesa?
			
			if ( $char -> church -> id != $church -> id )
			{
				$message = kohana::lang('global.error-charnotcorrectchurch');
				kohana::log('info', 'Char does not belong to a church.' ); 				
				return false;
			}
								
			if ( $role == 'church_level_2' )
			{						
				if ( Character_Role_Model::charhasservedrole( $char, 'church_level_3' ) == false )
				{
					$message = kohana::lang('religion.didnotserveasreligionlevel3_' . $char -> church -> name, $char -> name );
					kohana::log('info', 'Char did not serve as church_level_3.' ); 				
					return false;
				}
				
				// FPs
				
 				$contributedfps = $char -> get_stats( 'fpcontribution', $char -> church_id );
				
				//var_dump( $contributedfps ); exit; 
				
				if ( is_null( $contributedfps ) or $contributedfps[0] -> value < self::CHURCH_LEVEL_2_NEEDED_CONTRIBUTEDFPS )
				{
					$message = kohana::lang('religion.notenoughfp', $char -> name );
					kohana::log('info', 'Char did not contribute enough FPs.');
					return false;
				}
			}
								
			if ( $role == 'church_level_3' )
			{						
				if ( Character_Role_Model::charhasservedrole( $char, 'church_level_4' ) == false )
				{
					$message = kohana::lang('religion.didnotserveasreligionlevel4_' . $char -> church -> name, $char -> name );
					kohana::log('info', 'Char did not serve as church_level_4.' ); 				
					return false;
				}
				
				// FPs
				$contributedfps = $char -> get_stats( 'fpcontribution', $char -> church_id );
				if ( is_null( $contributedfps ) or $contributedfps[0] -> value < self::CHURCH_LEVEL_3_NEEDED_CONTRIBUTEDFPS )
				{
					$message = kohana::lang('religion.notenoughfp', $char -> name );
					kohana::log('info', 'Char did not contribute enouth FPs.');
					return false;
				}
			}
			
			if ( $role == 'church_level_4' )
			{						
				
				// FPs
 				$contributedfps = $char -> get_stats( 'fpcontribution', $char -> church_id );
				if ( is_null( $contributedfps ) or $contributedfps[0] -> value < self::CHURCH_LEVEL_4_NEEDED_CONTRIBUTEDFPS )
				{
					$message = kohana::lang('religion.notenoughfp', $char -> name );
					kohana::log('info', 'Char did not contribute enouth FPs.');
					return false;
				}
			}
			
		}		
		
		return true;
		
	}
	
	/** 
	* Ritorna un array di tag di strutture controllate da un ruolo
	* @return vettore di oggetti structure oppure null
	*/
	
	function get_controlledstructurestags(  $tag  )
	{		
		switch ( $tag ) 
		{
			case 'vassal' : $structures = array( 'castle', 'buildingsite' ); break;
			case 'judge' : $structures = array('court') ; break;
			case 'pope' : $structures = array('holysee') ; break;
			case 'sheriff' : $structures = array('barracks' ); break;
			case 'towerguardian' : $structures = array('watchtower' ); break;			
			case 'academydirector' : $structures = array('academy' ); break;
			case 'drillmaster' : $structures = array('trainingground' ); break;
			case 'church_level_1' : $structures = array('religion_1'); break;			
			case 'church_level_2' : $structures = array('religion_2'); break;
			case 'church_level_3' : $structures = array('religion_3'); break;
			case 'church_level_4' : $structures = array('religion_4'); break;
			case 'bishop' : $structures = array( 'bishoppalace' ); break;
			case 'priest' : $structures =  array('cathedral') ; break;
			case 'king' : $structures =  array('royalpalace', 'buildingsite') ; break;
			default: $structures = NULL; break;
		}
			
		return $structures;
		
	}

	/** 
	* Ritorna un vettore con le strutture controllate da un ruolo
	* @param none
	* @return vettore di oggetti structure oppure null
	*/
	
	function get_controlledstructures()
	{
		kohana::log('info', '-> Tag: ' . $this -> tag );
		$structurestags = $this -> get_controlledstructurestags( $this -> tag );		
		$crs = $cs = null;
		
		//////////////////////////////////////////////////
		// determiniamo le regioni che il char controlla
		//////////////////////////////////////////////////
		
		// Re, controlla tutte le regioni
		
		if ( $this -> tag == 'king' )
		{
			$controlledregions = $this -> character -> get_controlledregions(); 
			$crs = $controlledregions -> as_array();
		}
		
		// vassallo, controlla le regioni a lui assegnate

		if ( $this -> tag == 'vassal' )
		{
			$controlledregions = $this -> character -> get_controlledregions(); 
			$crs = $controlledregions -> as_array();
		}		
		
		// cariche ecclesiastiche, la regione è quella legata al ruolo
		
		else if ( in_array( $this -> tag, 
			array ('church_level_1', 'church_level_2', 'church_level_3', 'church_level_4' ) ) )
			$crs[] = $this -> region ;	
		
		// altro, la regione è quella la regione è quella legata al ruolo
		
		else		
			$crs[] = $this -> region ;	
		
		//////////////////////////////////////////////////
		// Per ogni regione controllata, costruiamo il
		// vettore delle strutture controllate.
		//////////////////////////////////////////////////
				
		foreach ( $crs as $controlledregion )		
		{
			
			kohana::log( 'debug', "-> Processing region controlled by character [{$controlledregion -> name}]." ); 
			
			foreach ( $structurestags as $structuretag )
			{				
				
				// le structure controllate dipendono dai tag controllati
				
				$structures = $controlledregion -> get_structures( $structuretag );
					
				if ( !is_null( $structures ) )
				
					foreach ( $structures as $structure )
					
					{
						kohana::log('debug', "-> Controlled structure: " . $structure -> getSupertype() . ", type: {$structure -> structure_type -> type}, id: {$structure->id}, region: {$structure -> region -> name}." );
						
						// nel caso del castello, il vassallo controlla SOLO quello della regione a lui assegnata.
						
						if ( $structure -> getSupertype() == 'castle')
						{
							kohana::log('debug', "-> Role region id: {$this -> region_id}, Structure Region id: {$structure -> region_id}.");
							if ( $structure -> region_id == $this -> region_id )
							{
								
								kohana::log('debug', "-> Adding controlledstructure: {$structure -> id}.");
								$cs[] = $structure;
							}
						}
						// nel caso di buildingsite, vengono caricati solo quelli accessibili al ruolo						
						elseif ( $structure -> getSupertype() == 'buildingsite' )
						{
							
							// get kingdomproject
							
							$kp = ORM::factory('kingdomproject') -> where ( 'structure_id', $structure -> id ) -> find(); 
							
							kohana::log('debug', '=> buildingsite project type: ' .  $kp -> cfgkingdomproject -> tag );  
							
							if ( $kp -> cfgkingdomproject -> owner == $this -> tag )
							{
								kohana::log('debug', '=> Adding structure.');
								$cs[] = $structure;
							}
						}
						else
						{
							kohana::log('debug', '=> Adding structure.');
							$cs[] = $structure;
						}
					}	
			}
		}			
			return $cs;		
	}	
	
	/**
	* Verifica se il giocatore ha rivestito il ruolo
	* Se è un ruolo religioso, il controllo si estende
	* al parametro chiesa.
	* @param char: oggetto char
	* @param tag: ruolo da verificare
	* @return false o true
	*/
	
	function charhasservedrole ( $char, $tag )
	{
		// disabilitato
		
		return true;
		
		$db = Database::instance();
		if ( in_array( $tag, array('church_level_2', 'church_level_3', 'religion_level_4' ) ) )
			$res = ORM::factory( 'character_role' ) -> 
			where ( array( 
				'character_id' => $char -> id,
				'tag' => $tag,
				'church_id' => $char -> church -> id
				) ) -> count_all() ;
		else
			$res = ORM::factory( 'character_role' ) -> 
			where ( array( 
				'character_id' => $char -> id,
				'tag' => $tag				
				) ) -> count_all() ;
				
		if ($res > 0 )
			return true;
		else
			return false;
	}
	
	/**
	* Ritorna se un ruolo è di governo o di chiesa
	* @param none
	* @return string religious o government
	*/
	
	function get_roletype()
	{
		switch ( $this -> tag ) 
		{
			case 'church_level_1':
			case 'church_level_2':
			case 'church_level_3':
			case 'church_level_4':
				return 'religious' ; break;
			default: 
				return 'government' ; break;		
		}
	
	}
	
	/**
	* Trova la struttura principale legata al ruolo
	* @param none
	* @return oggetto struttura legata al ruolo o null
	*/
	
	function get_controlledstructure()
	{
		
		$controlledstructure = null;
		
		$controlledstructures = $this -> get_controlledstructures();
		foreach ($controlledstructures as $controlledstructure )
		{
			
			if ( $controlledstructure -> structure_type -> supertype == 'buildingsite' )
				continue;
			
			return $controlledstructure;
		}
		
	}
	
	/** 
	* Returns character title image
	* @param  array   $style   css style to apply
	* @return string           html to print
	*/
	
	function get_title_image($style=NULL) 
	{
		if ( file_exists('media/images/badges/nobletitles/custom/'.$this->kingdom_id.'/'.$this->tag.'.png') )
		{
			return html::image( array('src' => 'media/images/badges/nobletitles/custom/'.$this->kingdom_id.'/'.$this->tag.'.png'), $style );
		}
		else
		{
			return html::image( array('src' => 'media/images/badges/nobletitles/'.$this->tag.'.png'), $style );
		}
	}
}
?>
