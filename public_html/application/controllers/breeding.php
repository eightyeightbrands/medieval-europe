<?php defined('SYSPATH') OR die('No direct access allowed.');

class Breeding_Controller extends Template_Controller
{

	public $template = 'template/gamelayout';
	
	/**
	* Compra Animali (Allevamento
	* @return none
	*/
	
	function buyanimals( $structuretype )
	{
		
		$view = new view('breeding/buyanimals');
		$sheets  = array('gamelayout'=>'screen', 'submenu'=>'screen');		
		$character = Character_Model::get_info( Session::instance()->get('char_id') );
		$subm    = new View ('template/submenu');
			
		$currentregion = ORM::factory('region', $character -> position_id );
		
		if ( !$_POST )
		{		
			// check if the region has such resource
						
			$structure = $currentregion -> get_structure( $structuretype, 'type' );
			$structureinstance = StructureFactory_Model::create( $structuretype );	
			$price = $structureinstance -> getPrice( $character, $currentregion );
			
			if ( is_null( $structure ) )
			{
				Session::set_flash('user_message', "<div class=\"error_msg\">" . kohana::lang('global.operation_not_allowed') . "</div>");
				url::redirect('region/view');
			}
		
		}
		else
		{			
			
			$ca = Character_Action_Model::factory('buyanimals');				
			
			$par[0] = $character;
			$par[1] = $this -> input -> post('type'); 
				
			if ( $ca -> do_action( $par, $message ) )
			{
				Session::set_flash('user_message', "<div class=\"info_msg\">". $message . "</div>");
			}	
			else	
			{ 
				Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>"); 
			}
			
			url::redirect('region/view/');
		
		}
		
		$view -> structureinstance = $structureinstance;		
		$view -> price = $price;		
		$this -> template -> content = $view ;
		$this -> template -> sheets = $sheets; 
	
	}
	
	/**
	* Raccolta materiali
	* @param int $structure_id ID struttura
	* @return none
	*/
	
	public function gather( $structure_id )
	{
		
		$message = "";
		
		$character = Character_Model::get_info( Session::instance()->get('char_id') );
		$structure = StructureFactory_Model::create( null, $structure_id );
		
		if ( ! $structure -> allowedaccess( $character, $structure -> getParentType(), $message, 'private', 'gather' ) )
		{
			Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
			url::redirect( 'region/view/' );
		}
		
		// Istanzio l'azione "gather"		
		$ca = Character_Action_Model::factory("gather");				

		$par[0] = $structure;
		$par[1] = $character;

		if ( $ca->do_action( $par,  $message ) )
		{ Session::set_flash('user_message', "<div class=\"info_msg\">". $message . "</div>"); }
		else	
		{ Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>"); }
				
		url::redirect('structure/manage/' . $structure -> id );
	
	}	

	/**
	* Butchering
	* @param structure_id ID struttura
	* @return none
	*/
	
	public function butcher( $structure_id )
	{
	
		$character = Character_Model::get_info( Session::instance()->get('char_id') );
		$structure = StructureFactory_Model::create( null, $structure_id );
		$message = "";
	
		if ( ! $structure -> allowedaccess( $character, $structure -> getParentType(), $message, 'private', 'butcher' ) )
		{
			Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
			url::redirect( 'region/view/' );
		}
		
		// Istanzio l'azione "butcher"		
		$ca = Character_Action_Model::factory("butcher");		
		
		$par[0] = $structure;
		$par[1] = $character;

		// Controllo se la struttura esiste
		if ( $par[0]->loaded )
		{
			if ( $ca->do_action( $par,  $message ) )
			{ Session::set_flash('user_message', "<div class=\"info_msg\">". $message . "</div>"); }
			else	
			{ Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>"); }
		}
		else
		{	Session::set_flash('user_message', "<div class=\"error_msg\">". kohana::lang('structures.milking_error1') . "</div>"); } 

		url::redirect('structure/manage/' . $structure -> id );

	}

	/**
	* Alimenta gli animali
	* @param structure_id ID struttura
	* @return none
	*/
	
	public function feed( $structure_id )
	{
		
		$character = Character_Model::get_info( Session::instance()->get('char_id') );
		$structure = StructureFactory_Model::create( null, $structure_id );		
		$message = "";
		
		if ( ! $structure -> allowedaccess( $character, $structure -> getParentType(), $message, 'private', 'feed' ) )
		{
			Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
			url::redirect( 'region/view/' );
		}
		
		// Istanzio l'azione "feed"		
		$ca = Character_Action_Model::factory("feed");		
		
		$par[0] = $structure;
		$par[1] = $character;

		if ( $ca->do_action( $par,  $message ) )
			{ Session::set_flash('user_message', "<div class=\"info_msg\">". $message . "</div>"); }
		else	
			{ Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>"); }
			
		url::redirect('structure/manage/' . $structure -> id );
	}
		
}
