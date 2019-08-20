<?php defined('SYSPATH') OR die('No direct access allowed.');

class Shop_Controller extends Template_Controller
{

	// Imposto il nome del template da usare
	public $template = 'template/gamelayout';
	
	/**
	* Elenca i diversi tipi di bottega
	* @param none
	* @return none	
	**/
	
	public function index()	
	{
	
		$view = new view('shop/index');
		$sheets  = array('gamelayout'=>'screen', 'submenu'=>'screen', 'structure'=>'screen');				
		$character = Character_Model::get_info( Session::instance()->get('char_id') );		
		$region = ORM::factory('region', $character -> position_id );
		$shops = ORM::factory("structure_type") 
			-> where ( array( 
				'parenttype' => 'shop' , 
				'level' => 1 ) ) -> find_all ();
		
		$view -> char = $character;
		$view -> region = $region;
		$view -> shops = $shops;
		$this -> template -> content = $view;
		$this -> template -> sheets = $sheets;
		
	}
	
	/**
	* Permette di gestire la struttura
	* @param structure_id ID struttura
	* @return none
	*/
	
	public function manage( $structure_id ) 
	{
	
		$view = new view('shop/manage');
		$section_upgradehourlywage = new view('structure/section_upgradehourlywage');
		$section_description = new view('structure/section_description');		

		$sheets  = array('gamelayout'=>'screen', 'submenu'=>'screen');		
		$character = Character_Model::get_info( Session::instance()->get('char_id') );
		$structure = StructureFactory_Model::create( null, $structure_id);
		
		if ( ! $structure->allowedaccess( $character, $structure -> getParentType() , $message, 'private', 'manage' ) )
		{
			Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
			url::redirect( 'region/view/' );
		}
		
		$submenu = new View( 'structure/' . $structure -> getSubmenu() );
		$submenu -> id = $structure -> id;
		$submenu -> action = 'manage';
		$view -> submenu = $submenu;
		$section_upgradehourlywage -> structure = $structure;
		$section_upgradehourlywage -> upgradehourlywage = $structure -> getUpgradehourlywage();		
		$view -> section_upgradehourlywage = $section_upgradehourlywage;		
		$section_description -> structure = $structure;
		$view -> section_description = $section_description;
		$view -> structure = $structure;
		$this -> template -> content = $view ;
		$this -> template -> sheets = $sheets; 
	
	}
	
	
	/**
	 * Permette di upgradare il negozio
	 * @param type tipo di upgrade
     * @param structure_id id struttura	 
	 * @return none
	*/
	
	function upgrade( $type = 'level', $structure_id = null) 
	{
		$view = new View ( '/shop/upgrade'. $type ); 
		$sheets  = array('gamelayout'=>'screen', 'submenu'=>'screen');		
		$character = Character_Model::get_info( Session::instance()->get('char_id') );
		$subm    = new View ('template/submenu');
		
		if ( ! $_POST ) 
		{
			$structure = StructureFactory_Model::create( null, $structure_id );
			
			if ( ! $structure -> allowedaccess( $character, $structure -> getParentType(), $message ) )
			{
				Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
				url::redirect('region/view/');
			}	
		
		}	
		else
		{
			
			$structure = StructureFactory_Model::create( null, $this -> input -> post('structure_id') );
			if ( $this -> input -> post('upgradeinventory' ) )
			{
				
				$message = "";			
				$ca = Character_Action_Model::factory("upgradestructureinventory");				
				$par[0] = $structure;
				$par[1] = $character; 
		
				if ( $ca->do_action( $par,  $message ) )
				{
					Session::set_flash('user_message', "<div class=\"info_msg\">". $message . "</div>"); 
					url::redirect( '/shop/upgrade/inventory/' . $structure -> id ); 
				}	
				else	
				{ 
					Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>"); 
					url::redirect( '/shop/upgrade/inventory/' . $structure -> id ); 
				}
			}
			
			if ( $this -> input -> post('upgradelevel' ) )
			{
				$message = "";			
				$ca = Character_Action_Model::factory("upgradestructurelevel");								
				$par[0] = $structure;
				$par[1] = $character; 
				$par[2] = $this -> input -> post('hours'); 
		
				if ( $ca->do_action( $par,  $message ) )
				{
					Session::set_flash('user_message', "<div class=\"info_msg\">". $message . "</div>"); 
					url::redirect( '/shop/upgrade/level/' . $structure -> id ); 
				}	
				else	
				{ 
					Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>"); 
					url::redirect( '/shop/upgrade/level/' . $structure -> id ); 
				}
			}
			
			$structure_id = $this -> input -> post( 'structure_id' ) ; 
	
	}	
	
	$levelupgradeworkerhours = Structure_Model::get_stat_d( $structure -> id, 'levelupgradeworkerhours' );
	$view -> levelupgradeworkerhours = is_null ( $levelupgradeworkerhours ) ? 0 : $levelupgradeworkerhours -> value;
	$lnkmenu = $structure -> get_horizontalmenu( 'upgradeinventory' );
	$subm -> submenu = $lnkmenu;
	$view -> submenu = $subm;
	$view -> structure = $structure ; 
	$this -> template -> content = $view ; 
	$this -> template->sheets = $sheets;

}

}
