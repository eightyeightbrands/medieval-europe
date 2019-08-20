<?php defined('SYSPATH') OR die('No direct access allowed.');

class CA_Donatecoins_Model extends Character_Action_Model
{
	protected $immediate_action = true;
	const DONATIONCOINS = 15;
	const DELTAFAITHLEVEL = 4;
	
	// Effettua tutti i controlli relativi alla use, sia quelli condivisi
	// con tutte le action che quelli peculiari della eat
	// @input: array di parametri
	// @par[0] : oggetto char
	// @par[1] : oggetto structure
	// @output: TRUE = azione disponibile, FALSE = azione non disponibile
	//          $messages contiene gli errori in caso di FALSE
	
	protected function check( $par, &$message )
	{ 
		if ( ! parent::check( $par, $message ) )					
		{ return false; }
		
		// Controllo parametri		
		if (! $par[1] -> loaded or $par[1] -> region -> id != $par[0] -> position_id )
		{ $message = kohana::lang('global.operation_not_allowed'); return FALSE; }
		
		// Il char ha soldi?
		
		if (! $par[0] -> check_money( self::DONATIONCOINS ) )		
		{ $message = kohana::lang('charactions.global_notenoughmoney'); return FALSE; }

		// solo un fedele può donare
		
		if ( is_null( $par[0] -> church_id ) or $par[0] -> church_id != $par[1] -> structure_type -> church_id )
		{ $message = kohana::lang('ca_donatecoins.onlyfollowerscandonate'); return FALSE; }

		// se si ha un ruolo nella chiesa, non si può donare
		$role = $par[0] -> get_current_role();
		
		if ( !is_null( $role ) and $role -> get_roletype() == 'religious' )
		{ $message = kohana::lang('ca_donatecoins.religiousrolecantdonate'); return FALSE; }
		
		return true;
		
	}

	
	// nessun controllo particolare
	protected function append_action( $par, &$message )
	{	}

	
	
	public function execute_action ( $par, &$message ) 
	{
	
		// tolgo i soldi
		
		$par[0] -> modify_coins( - self::DONATIONCOINS, 'donatecoins' );
		$par[0] -> save();
	
		// do x FP
		
		$par[0] -> modify_faithlevel( self::DELTAFAITHLEVEL );
		
		// evento
		
		Character_Event_Model::addrecord( $par[0] -> id, 'normal', '__events.donatecoins' . ';' . 
			self::DONATIONCOINS . ';' . self::DELTAFAITHLEVEL 		
		, 'normal' );
		
		// distribuisco i soldi alla gerarchia (5 li brucio)		
		
		//$coins = Item_Model::factory( null, 'silvercoin' );		
		// dà i soldi alla struttura livello 4
		//$coins -> additem( 'structure', $par[1] -> id, 4 );	
		
		$par[1] -> modify_coins( 4, 'donatecoins' ); 
		
		Structure_Event_Model::newadd( $par[1] -> id, 		
			'__events.structure_donatedcoins' . ';' .				
				$par[0] -> name . ';' . 
				4);
		
		$p1structure = ORM::factory('structure', $par[1] -> parent_structure_id );
		if ( $p1structure -> loaded )
		{
			$p1structure -> modify_coins( 3, 'donatecoins' ); 
			Structure_Event_Model::newadd( $p1structure -> id, 		
			'__events.structure_donatedcoins' . ';' .				
				$par[0] -> name . ';' . 
				3);
					
			$p2structure = ORM::factory('structure', $p1structure -> parent_structure_id );
			if ( $p2structure -> loaded )
			{
				
				$p2structure -> modify_coins( 2, 'donatecoins' ); 
				// evento
				Structure_Event_Model::newadd( $p2structure -> id , 		
				'__events.structure_donatedcoins' . ';' .				
				$par[0] -> name . ';' . 
				2);
				
				$p3structure = ORM::factory('structure', $p2structure -> parent_structure_id );
				if ( $p3structure -> loaded )				
				{
					
					$p3structure -> modify_coins( 1, 'donatecoins' ); 
					Structure_Event_Model::newadd( $p1structure -> id, 		
					'__events.structure_donatedcoins' . ';' .				
					$par[0] -> name . ';' . 
					1);
				}
			}
		}			
		
		// stat
		
		$par[0] -> modify_stat('alms', self::DONATIONCOINS, $par[0] -> church_id );
		
		$message = kohana::lang( 'ca_donatecoins.donate-ok');
		
		return true;
	}
}
