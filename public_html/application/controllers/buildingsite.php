<?php defined('SYSPATH') OR die('No direct access allowed.');

class Buildingsite_Controller extends Template_Controller
{
	// Imposto il nome del template da usare
	public $template = 'template/gamelayout';

	
	/*
	* Costruisce una struttura
	* @param int $structure_id ID Struttura
	*/
	
	function build( $structure_id )
	{
		
		$view = new View ('structure/build');
		$sheets  = array('gamelayout'=>'screen', 'submenu'=>'screen');
		$character = Character_Model::get_info( Session::instance()->get('char_id') );				
		
		if ( !$_POST )
		{
			// trovo il progetto del nodo che ha come target la struttura corrente
			
			$structure = StructureFactory_Model::create( null, $structure_id );
			if ( ! $structure -> allowedaccess( $character, $structure -> getParentType(), $message,
				'public', 'manage' ) )
			{
				Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
				url::redirect('region/view/');
			}
		
			$project = ORM::factory('kingdomproject') ->where ( array( 'structure_id' => $structure_id ) ) -> find() ;
		
		
		}
		else
		{
		
			$structure = StructureFactory_Model::create( null, $this -> input -> post('structure_id') );
			if ( ! $structure -> allowedaccess( $character, $structure -> getParentType(), $message,
				'public', 'manage' ) )
			{
				Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
				url::redirect('region/view/');
			}
			$project = ORM::factory('kingdomproject') ->where ( array( 'structure_id' => $structure->id ) ) -> find() ;
			$info = $project -> get_info();	

			$o = Character_Action_Model::factory("workonproject");
			$par[0] = $character;
			$par[1] = $structure;				
			$par[2] = $this->input->post('hours');
			$par[3] = $project;
			$par[4] = $this->input->post('workingtype');
		
			$rec = $o->do_action( $par, $message );			

			if ( $rec )
			{
				Session::set_flash('user_message', "<div class=\"info_msg\">". $message . "</div>");
				url::redirect('/region/view/' . $character -> position_id );
				return;
				
			}
			else
			{					
				Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");		
			}			
		}
		
		$info = $project -> get_info();	
		
		$view -> workedhours = $info['workedhours'];
		$view -> totalhours = $info['totalhours'];
		$view -> workedhours_percentage = $info['workedhours_percentage'];
		$view -> hourlywage = $structure -> hourlywage;
		$view -> structure = $structure;
		$view -> character = $character;
		// appena un giocatore apre il popup si determina se la struttura è costruibile.
		$view -> isbuildable = $project -> is_buildable();
		$view -> project = $project ;
		$view -> info = $info;
		$this -> template->content = $view;
		$this -> template->sheets = $sheets;

	}

}
