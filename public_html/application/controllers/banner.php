<?php defined('SYSPATH') OR die('No direct access allowed.');

class Banner_Controller extends Template_Controller
{
	// Imposto il nome del template da usare
	
	public $template = 'template/blank';
	
	function display( $char_id )
	{				
		
		$view = new View ('user/banner');		
		$img = Utility_Model::create_banner( $char_id );
		$view -> img = $img;
		$this -> template -> content = $view;	
	}
		
}
