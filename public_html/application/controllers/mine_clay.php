<?php defined('SYSPATH') OR die('No direct access allowed.');

class Mine_clay_Controller extends Template_Controller
{
	// Imposto il nome del template da usare
	

	function shovel( $structure_id, $qta = 1 )
	{
		// Carico la struttura "Miniera di argilla"
		$structure = StructureFactory_Model::create( null, $structure_id );

		// Controllo che la struttura sia effettivamente una miniera di argilla
		if ($structure->structure_type->type <> 'mine_clay' ) 
		{
			Session::set_flash('user_message', "<div class=\"error_msg\">". kohana::lang('structures.error_structurenotvalid') . "</div>");
			url::redirect( "region/view/" . Session::instance()->get("char_id"));			
		}

		// Controllo che la miniera di carbone si trovi nello stesso
		// nodo dove si trova il char
		if ($structure->region_id <> Character_Model::get_info( Session::instance()->get('char_id') ) -> position_id) 
		{
			Session::set_flash('user_message', "<div class=\"error_msg\">". kohana::lang('structures.error_structurenotinregion') . "</div>");
			url::redirect( "region/view/" . Session::instance()->get("char_id"));			
		}

		// Se tutti i controlli vengono superati allora
		// inizializzo l'azione dig
		$message = "";
		$char = ORM::factory( "character" )->find( Session::instance()->get("char_id") );
		$ca_dig = Character_Action_Model::factory("shovel");
		if ( $ca_dig->do_action( array( $structure, $char, $qta ),  $message ) )
			Session::set_flash('user_message', "<div class=\"info_msg\">". $message . "</div>");		
		else		
			Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");		
		url::redirect( "region/view");	
	}
}
