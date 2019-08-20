<?php defined('SYSPATH') OR die('No direct access allowed.');

class ST_Breeding_Silkworm_Model extends ST_Breeding_Model
{

	function get_horizontalmenu( $structure, $selected)
	{
		return array(	
			'/structure/manage/' . $structure->id => 
				array( 'name' => 	kohana::lang('global.manage'),	'htmlparams' => array( 'class' => 
				( $selected == 'manage' ) ? 'selected' : '' )),
			'/structure/inventory/' . $structure->id => 
				array( 'name' => 	kohana::lang('global.inventory'),	'htmlparams' => array( 'class' => 
				( $selected == 'inventory' ) ? 'selected' : '' )),					
			'/breeding/feed/' . $structure->id => 
				array( 'name' => 	kohana::lang('structures.breeding_feed'),	'htmlparams' => array( 'class' => 
				( $selected == 'feed' ) ? 'selected' : '' )),		
			'/breeding/butcher/' . $structure->id => 
				array( 'name' => 	kohana::lang('structures.breeding_butcher'),	'htmlparams' => array( 'class' => 
				( $selected == 'inventory' ) ? 'selected' : '' )) );		

	}

}
