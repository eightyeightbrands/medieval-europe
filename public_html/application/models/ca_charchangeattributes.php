<?php defined('SYSPATH') OR die('No direct access allowed.');

class CA_Charchangeattributes_Model extends Character_Action_Model
{
	protected $immediate_action = true;
	//const MAXAGE = 30; 
	
	// Effettua tutti i controlli relativi alla wear, sia quelli condivisi
	// con tutte le action che quelli peculiari della wear
	// @input: array di parametri
	// par[0]: oggetto char
	// par[1]: array con i nuovi parametri
	// par[2]: somma originaria degli attributi
	// @output: TRUE = azione disponibile, FALSE = azione non disponibile
	//          $messages contiene gli errori in caso di FALSE
	
	protected function check( $par, &$message )
	{ 
		
		if ( ! parent::check( $par, $message ) )					
		{ return false; }

		// è possibile cambiare gli attributi una volta sola e solo entro MAXAGE giorni.
		
		if ( $par[0] -> is_newbie($par[0])==false  or !is_null ( $par[0] -> get_stats( 'attributeredistributed' ) ) ) 
		{	$message = kohana::lang('character.attributesnotchangeable'); return false; }
		
		// nessun attributo deve essere > 15 o < 1
		foreach ( $par[1] as $key => $value ) 		
			if ( $value < 1 or $value > 15 ) 
				{	$message = kohana::lang('character.attributevaluesnotvalid'); return false; }
		
		// la somma dei nuovi attributi deve essere == a quella precedente
		$sum = 0; 
		
		foreach ( $par[1] as $key => $value ) 		
			$sum += $value ; 
			
		if ( $sum != $par[2] ) 
			{	$message = kohana::lang('character.attributesumnotvalid', $par[2]); return false; }
		
		return true;
	}

	
	// nessun controllo particolare
	protected function append_action( $par, &$message )
	{	}

	// @input: par[0] memorizza l'id dell'oggetto che sto indossando
	
	public function execute_action ( $par, &$message ) 
	{
		
		foreach ( $par[1] as $key => $value ) 
			switch ( $key ) 
			{
				case 'str': $par[0] -> str = $value; break ; 
				case 'dex': $par[0] -> dex = $value; break ; 
				case 'intel': $par[0] -> intel = $value; break ; 
				case 'car': $par[0] -> car = $value; break ; 
				case 'cost': $par[0] -> cost = $value; break ; 
				default: break ; 
			}
		
		$par[0] -> save(); 
		
		// salva la statistica in modo che il char non possa piÃ¹ cambiarli
		
		$par[0] -> modify_stat( 
			'attributeredistributed', 1
		); 
		
		$message = sprintf( kohana::lang( 'charactions.changeattributes_ok')); 
		
		return true;
	}
}
