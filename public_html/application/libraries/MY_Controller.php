<?php
class Controller extends Controller_Core
{
	
	protected $disabledmodules;
	
	public function __construct()
	{
			parent::__construct();
			$db = Database::instance();
			$db -> query("select '--my_controller--'");
			$this -> set_template();
				
			//$this -> char = Character_Model::get_info( Session::instance()->get('char_id') );
			//$this -> currentregion = ORM::factory('region', $this -> char -> position_id);
			//kohana::log('debug', "Char: {$this -> char -> name}, Position: {$this -> currentregion -> name}");
			$this -> disabledmodules = Configuration_Model::get_disabledmodules();
			$db -> query("select '--my_controller--'");
	}

	public function set_template()
	{			
		
		$char_id = Session::instance()->get('char_id');		
		$skinstat = Character_Model::get_stat_d( $char_id, 'skin');
		if (!$skinstat -> loaded )
			$skin = 'classic';
		else
			$skin = $skinstat -> stat1;
		//kohana::log('debug', "-> Current Skin: [{$skin}]");
		
		//var_dump(Uri::segment(1));
		
		if ( uri::segment(1) == 'banner' and uri::segment(2) == 'display' )
			$this -> template = 'template/blank';
		elseif ($skin == 'new')
			if ( uri::segment(1) == 'region' and uri::segment(2) == 'view' )
				$this -> template = "template/{$skin}/regionviewlayout";
			elseif ( uri::segment(1) == 'map' and uri::segment(2) == 'view' )
				$this -> template = "template/{$skin}/mapviewlayout";
			else
				$this -> template = "template/{$skin}/layout";
		else
			$this -> template = 'template/gamelayout';
		kohana::log('debug', "-> Template Set to: [{$this->template}]");
		
		//kohana::log('debug', "-> Template Set to: [{$this->template}]");
		
	}
}
?>