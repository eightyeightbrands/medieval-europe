<?php defined('SYSPATH') OR die('No direct access allowed.');

class Battlefield_Controller extends Template_Controller
{
	// Imposto il nome del template da usare
	
	public $template = 'template/gamelayout';

	/*
	* Entra nel campo di battaglia
	*/
	
	public function enter( $structure_id = null )
	{
	
		$view = new view ('battlefield/enter');
		$sheets  = array('gamelayout'=>'screen');
		$char = Character_Model::get_info( Session::instance()->get('char_id') );					
		$region = ORM::factory('region', $char -> position_id );
		$structure = $region -> get_structure( 'battlefield' );
		
		$hasdogma_meditateanddefend =
			Church_Model::has_dogma_bonus( $char -> church_id, 'meditateanddefend');		
		
		$hasdogma_killtheinfidels = 
			Church_Model::has_dogma_bonus( $char -> church_id, 'killtheinfidels');		
		
		if ($_POST)
		{			
			
			if (!is_null($this-> input->post('configurefightmode')))
			{
				if 
				(					
					0 and in_array($this -> input -> post('fightmode'), array( 'normal', 'defend', 'attack') )
				)
				{
					kohana::log('debug', '-> Saving fightmode preference.');
					
					Character_Model::modify_stat_d(
						$char -> id,						
						'fightmode',
						0,
						null,
						null,
						true,
						$this -> input -> post('fightmode')
					);
				}
				else
					Character_Model::modify_stat_d(
						$char -> id,						
						'fightmode',
						0,
						null,
						null,
						true,
						'normal'
					);
			}
		}
		
		// Verifico l' attack mode
		
		$fightmodestat = Character_Model::get_stat_d(
			$char -> id,
			'fightmode'
		);
		
		if ($fightmodestat -> loaded)
			$fightmode = $fightmodestat -> stat1;
		else
			$fightmode = 'normal';
		
		
		// se il battlefield non esiste, ridireziona alla città.
		
		if ( is_null( $structure ) )
		{			
			$char -> modify_stat( 
				'fighting', 
				false,
				null,
				null,
				true );
				
			Session::set_flash('user_message', 
				"<div class=\"error_msg\">". kohana::lang('global.operation_not_allowed') . "</div>");
			url::redirect ( 'region/view/' ); 
		}			
		
		$battle = ORM::factory('battle', $structure -> attribute1 );
		$attackingregion = ORM::factory('region', $battle -> source_region_id ); 
		$attackedregion = ORM::factory('region', $battle -> dest_region_id ); 
		
		// setta lo stato del player a fighting
		
		if ( Character_Model::is_fighting( $char -> id ) == false ) 
		{
			$char -> modify_stat( 
				'fighting', 
				true,
				null,
				null,
				true );
			
			Character_Event_Model::addrecord( $char -> id , 'normal', '__events.battlefield_enter');
			
		}
		
		///////////////////////////////////////
		// conta i giocatori schierati
		///////////////////////////////////////
		
		$_attackers = array();
		$_defenders = array();		
		
		$battletype = Battle_TypeFactory_Model::create( $battle -> type );		
		
		$joinedcharacters = $battletype -> get_joined_characters( $battle -> id );
		
		$attackerslist = implode( ', ', $joinedcharacters['attack']['list'] );
		$defenderslist = implode( ', ', $joinedcharacters['defend']['list'] );		
				
		// get time of next round
		
		$nextround = ORM::factory( 'character_action' )
		->where( array( 
			'action' => 'battleround',
			'status' => 'running',
			'param2' => $battle -> id ) ) -> find();

		$view -> fightmode = $fightmode;
		$view -> hasdogma_meditateanddefend = $hasdogma_meditateanddefend;
		$view -> hasdogma_killtheinfidels = $hasdogma_killtheinfidels;
		$view -> structure = $structure;
		$view -> battle = $battle; 
		$view -> attackers_count = count( $joinedcharacters['attack']['list'] );
		$view -> defenders_count = count( $joinedcharacters['defend']['list'] );
		$view -> attackers = $attackerslist ;
		$view -> defenders = $defenderslist ;
		$view -> joinedcharacters = $joinedcharacters;
		$view -> targetregion = $attackedregion;
		$view -> timetostart = $nextround -> starttime;
		$view -> structure = $structure;
		$this -> template->sheets = $sheets;	
		$this -> template->content = $view;
			
	}
	
	/*
	* Permette di schierarsi con una fazione
	* @param faction fazione (attack, defense)
	* @param structure_id ID del battlefield
	* 
	*/
	
	function joinfaction(  $faction, $structure_id )
	{
				
		// Carico la struttura "Campo di battaglia"
		
		$structure = StructureFactory_Model::create( null, $structure_id );
		$char = Character_Model::get_info( Session::instance()->get('char_id') );			
		$ca = Character_Action_Model::factory("joinfaction");		
		
		// Passo come parametro il campo di battaglia
		
		$par[0] = $char;
		$par[1] = $structure;
		$par[2] = $faction;
		
		if ( $ca -> do_action( $par,  $message ) )
		{ 				
			Session::set_flash('user_message', "<div class=\"info_msg\">". $message . "</div>");			
			url::redirect ( 'battlefield/enter/' . $structure -> id);
		}	
		else	
		{ 			
			Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>"); 
			url::redirect ( 'battlefield/enter/' . $structure -> id);
		}
		
	}
	
	
	/*
	* Lascia il campo di battaglia, ed entra nella città
	*/
	
	function entercity( $structure_id = null)
	{
		$char = Character_Model::get_info( Session::instance()->get('char_id') ); 
		$structure = StructureFactory_Model::create( null, $structure_id );
		
		$ca = Character_Action_Model::factory("entercity");		
		
		$par[0] = $char;
		$par[1] = $structure;
		
		if ( $ca -> do_action( $par,  $message ) )
		{ 				
			Session::set_flash('user_message', "<div class=\"info_msg\">". $message . "</div>");
			url::redirect('region/view/' . $char -> position_id ); 

		}	
		else	
		{ 
			Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>"); 
			url::redirect ( 'battlefield/enter/' . $structure -> id);
		}
		
	
	}

	public function manage( $structure_id )
	{
		
		url::redirect('/battlefield/raidloot/' . $structure_id);
	}
	
	/*
	* Accedi al bottino del campo di battaglia
	* @param structureid id struttura
	* @return none
	*/
	
	public function raidloot( $structure_id )
	{
	
		$char = Character_Model::get_info( Session::instance()->get('char_id') );						
		$structure = StructureFactory_Model::create( null, $structure_id );
		$battle = ORM::factory( 'battle', $structure -> attribute1 );			
		
		//////////////////////////////////////
		// il battlefield, esiste?
		//////////////////////////////////////
		
		if ( !$structure -> loaded or $structure -> getParentType() != 'battlefield' )
		{			
			Session::set_flash('user_message', "<div class=\"error_msg\">". kohana::lang('global.operation_not_allowed') . "</div>");
			url::redirect ( 'battlefield/enter/' );
		}	

		//////////////////////////////////////
		// La battaglia � in stato completed?
		//////////////////////////////////////		
		
		if ( $battle -> status != 'completed' )
		{			
			Session::set_flash('user_message', "<div class=\"error_msg\">". kohana::lang('global.operation_not_allowed') . "</div>");
			url::redirect ( 'battlefield/enter/' ); 
		}		
		
		//////////////////////////////////////
		// Solo i giocatori della nazione 
		// vincente possono accedere al bottino
		//////////////////////////////////////		
		
		$attackingregion = ORM::factory('region', $battle -> source_region_id ); 
		$attackedregion = ORM::factory('region', $battle -> dest_region_id ); 		
				
		if ( 
			($battle -> attacker_wins > $battle -> defender_wins and 
			$char -> region -> kingdom -> id != $attackingregion -> kingdom -> id )
			or
			($battle -> defender_wins > $battle -> attacker_wins 
			and $char -> region -> kingdom -> id != $attackedregion -> kingdom -> id )	)
		{
			Session::set_flash('user_message', "<div class=\"error_msg\">". kohana::lang('global.operation_not_allowed') . "</div>");
			url::redirect ( 'battlefield/enter/' ); 
		}				

		url::redirect ( 'structure/inventory/' . $structure_id  ); 
		
	}
	
}


