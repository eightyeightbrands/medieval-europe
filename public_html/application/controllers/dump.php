<?php defined('SYSPATH') OR die('No direct access allowed.');

class Dump_Controller extends Template_Controller
{
	// Imposto il nome del template da usare
	public $template = 'template/gamelayout';

	// Cerca
	function search( $structure_id ) 
	{
		
		$char = Character_Model::get_info( Session::instance()->get('char_id') ); 
		$structure = StructureFactory_Model::create( null, $structure_id );
		
		$par[0] = $char;
		$par[1] = $structure;
		$ca = Character_Action_Model::factory("searchdump");
		
		if ( $ca->do_action( $par,  $message ) )
		 	{ Session::set_flash('user_message', "<div class=\"info_msg\">". $message . "</div>"); }	
		else	
			{ Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>"); }
		
		url::redirect( 'region/view/' . $char -> position_id );
	}
	
}
