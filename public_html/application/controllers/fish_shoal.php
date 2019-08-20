<?php defined('SYSPATH') OR die('No direct access allowed.');

class Fish_shoal_Controller extends Template_Controller
{

	function fish( $structure_id, $qty = 1)
	{
		// Carico la struttura "Salina"
		$structure = StructureFactory_Model::create( null, $structure_id );
		$char = Character_Model::get_info( Session::instance()->get('char_id') );
		// Controllo che la struttura sia effettivamente un branco di pesci
		if ($structure->structure_type->type <> 'fish_shoal' ) 
		{
			Session::set_flash('user_message', "<div class=\"error_msg\">". kohana::lang('structures.error_structurenotvalid') . "</div>");
			url::redirect( "region/view/" . Session::instance()->get("char_id"));			
		}

		// Controllo che il branco di pesci si trovi nello stesso
		// nodo dove si trova il char
		if ($structure->region_id <>  Character_Model::get_info( Session::instance()->get('char_id') ) -> position_id) 
		{
			Session::set_flash('user_message', "<div class=\"error_msg\">". kohana::lang('structures.error_structurenotinregion') . "</div>");
			url::redirect( "region/view/" . Session::instance()->get("char_id"));			
		}

		// Se tutti i controlli vengono superati allora
		// inizializzo l'azione fish
		
		$message = "";		
		$par[0] = $structure;
		$par[1] = $char;		
		$par[2] = $qty;
				
		$ca = Character_Action_Model::factory("fish");
		if ( $ca->do_action( $par,  $message ) )
			Session::set_flash('user_message', "<div class=\"info_msg\">". $message . "</div>");		
		else		
			Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");		
		url::redirect( "region/view");	
	}
}
