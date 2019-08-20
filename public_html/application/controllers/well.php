<?php defined('SYSPATH') OR die('No direct access allowed.');

class Well_Controller extends Template_Controller
{
	// Imposto il nome del template da usare
	

	function collect_water( $structure_id, $qta = 1 )
	{
		// Carico la struttura "Pozzo"
		$structure = StructureFactory_Model::create( null, $structure_id );

		// Controllo che la struttura sia effettivamente un pozzo
		if ($structure->structure_type->type <> 'well_1' ) 
		{
			Session::set_flash('user_message', "<div class=\"error_msg\">". kohana::lang('structures.error_structurenotvalid') . "</div>");
			url::redirect( "region/view/" . Session::instance()->get("char_id"));			
		}

		// Controllo che il pozzo si trovi nello stesso
		// nodo dove si trova il char
		if ($structure->region_id <>  Character_Model::get_info( Session::instance()->get('char_id') ) -> position_id) 
		{
			Session::set_flash('user_message', "<div class=\"error_msg\">". kohana::lang('structures.error_structurenotinregion') . "</div>");
			url::redirect( "region/view/" . Session::instance()->get("char_id"));			
		}

		// Se tutti i controlli vengono superati allora
		// inizializzo l'azione collectwater
		$message = "";
		$char = ORM::factory( "character" )->find( Session::instance()->get("char_id") );
		$ca_cwater = Character_Action_Model::factory("collectwater");
		if ( $ca_cwater->do_action( array( $structure, $char, $qta ),  $message ) )
			Session::set_flash('user_message', "<div class=\"info_msg\">". $message . "</div>");		
		else		
			Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");		
		url::redirect( "region/view");	
	}

}
