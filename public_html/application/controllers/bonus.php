<?php defined('SYSPATH') OR die('No direct access allowed.');

class Bonus_Controller extends Template_Controller
{
	// Imposto il nome del template da usare
	public $template = 'template/gamelayout';					
	
	function index( $tabindex = 1)
	{	
		$view = new View('bonus/index');
		$sheets  = array('gamelayout'=>'screen', 'submenu'=>'screen');
		$char = Character_Model::get_info( Session::instance()->get('char_id') );		
		$view -> tabindex = $tabindex;		
		$view -> user_id = $char -> user -> id;
		$view -> char = $char;
		$this -> template -> content = $view;
		$this -> template -> sheets = $sheets;
		
	}		
	
	public function getdoubloons()
	{

		$view = new View('bonus/getdoubloons');
		$sheets  = array('gamelayout'=>'screen', 'submenu'=>'screen');
		$char = Character_Model::get_info( Session::instance()->get('char_id') );
		
		$view -> sw_apphash = Kohana::config( 'medeur.sw_apphash');
		$view -> char = $char;		
		$this -> template->content = $view;
		$this -> template->sheets = $sheets;
		
	}
	
	public function getdoubloons_crypto() {
		$view = new View('bonus/getdoubloons_crypto');
		$sheets  = array('gamelayout'=>'screen', 'submenu'=>'screen');
		$char = Character_Model::get_info( Session::instance()->get('char_id') );
		$view -> char = $char;
		$this -> template->content = $view;
		$this -> template->sheets = $sheets;
	}	
	
	function buy( )
	{
		
	//	var_dump($this -> input -> post());exit;
		
		$char = Character_Model::get_info( Session::instance()->get('char_id') );		
		$pb = PremiumBonus_Factory_Model::create( $this -> input -> post ('name') );
		$message = '';
		$par = array();
		$structure = null;
		
		
		if ( $this -> input -> post('targetchar') != '' )
			$targetchar = ORM::factory('character') -> 
				where ('name', $this -> input -> post('targetchar')) -> find();	
		else
			$targetchar = $char;
		
		/////////////////////////////////////////////
		// for Armory bonus, let's find barracks id.
		/////////////////////////////////////////////
		
		if ( $this -> input -> post( 'name' ) == 'armory' )
		{
			$region = ORM::factory('region') 
				-> where ( 'name', 'regions.' . strtolower($this -> input -> post('region_name')) ) -> find();	
			
			if (!$region -> loaded )
			{
				Session::set_flash('user_message', "<div class=\"error_msg\">". kohana::lang('global.error-regionunknown
') . "</div>");	
				url::redirect('bonus/index/?tabindex=2');
			}
			
			$structure = $region -> get_structure( 'barracks');
			
		}
		
		/////////////////////////////////////////////
		// for atelier license, let's pass parameters
		/////////////////////////////////////////////
		
		if (strpos ( $this -> input -> post( 'name' ), 'atelier-license') !== false)
		{
			$par[0] = $targetchar -> sex;
			$par[1] = $this -> input -> post ('cut');	
			$par[2] = $this -> input -> post ('section');			
			$par[3] = $this -> input -> post ('subsection');					
			$par[4] = $this -> input -> post ('itemname');			
		}
		
		$rc = $pb -> add( $targetchar, $structure, $this -> input -> post ('cut'), $par, $message );		
		
		if ( $rc == true )
		{ 				
			Session::set_flash('user_message', "<div class=\"info_msg\">". kohana::lang($message) . "</div>");
		}	
		else	
		{ 
			Session::set_flash('user_message', "<div class=\"error_msg\">". kohana::lang($message) . "</div>");
		}
		
		url::redirect(request::referrer());
		
	}
	
}
