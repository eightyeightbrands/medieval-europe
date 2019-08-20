<?php defined('SYSPATH') OR die('No direct access allowed.');

class Cave_white_sand_Controller extends Template_Controller
{
	
	function shovel( $structure_id, $qta = 1 )
	{
		// Carico la struttura "Cava di sabbia bianca"
		$structure = StructureFactory_Model::create( null, $structure_id );

		// Controllo che la struttura sia effettivamente una cava di sabbia bianca
		if ($structure->structure_type->type <> 'cave_white_sand' ) 
		{
			Session::set_flash('user_message', "<div class=\"error_msg\">". kohana::lang('structures.error_structurenotvalid') . "</div>");
			url::redirect( "region/view/" . Session::instance()->get("char_id"));			
		}

		// Controllo che la miniera di carbone si trovi nello stesso
		// nodo dove si trova il char
		if ($structure->region_id <>  Character_Model::get_info( Session::instance()->get('char_id') ) -> position_id) 
		{
			Session::set_flash('user_message', "<div class=\"error_msg\">". kohana::lang('structures.error_structurenotinregion') . "</div>");
			url::redirect( "region/view/" . Session::instance()->get("char_id"));			
		}

		// Se tutti i controlli vengono superati allora
		// inizializzo l'azione gather
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
