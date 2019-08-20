<?php defined('SYSPATH') OR die('No direct access allowed.');

class Barracks_Controller extends Template_Controller
{
	
	public $template = 'template/gamelayout';			
	
	/**
	* Fornisce accesso all' armeria
	* @param int $structure_id ID Struttura
	* @return none
	*/
	
	function armory( $structure_id )
	{
		
		$character = Character_Model::get_info( Session::instance()->get('char_id') );
		$view = new View ('barracks/armory');
		$sheets  = array('gamelayout' => 'screen', 'submenu' => 'screen', 'character'=>'screen' );		
		
		if ( !$_POST ) 
		{			
			$structure = StructureFactory_Model::create( null, $structure_id );

			if ( ! $structure -> allowedaccess( $character, $structure -> getParentType(), $message, 
				'private', 'armory' ) )
			{
				Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
				url::redirect('region/view/');
			}	
		}
		else
		{
			$structure = StructureFactory_Model::create( null, $this -> input -> post('structure_id') );
			if ( ! $structure -> allowedaccess( $character, $structure -> getParentType(), $message, 
				'private', 'armory' ) )
			{
				Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
				url::redirect('region/view/');
			}	
		}
		
		$submenu = new View( 'structure/' . $structure -> getSubmenu() );
		$submenu -> id = $structure -> id;
		$submenu -> action = 'armory';
		$view->submenu = $submenu;
		
		$view -> bonus = $structure -> get_premiumbonus( 'armory' );
		$items = Structure_Model::inventory( $structure -> id ); 
		$view -> items = $items;
		$view -> structure = $structure;
		$this -> template -> content = $view;
		$this -> template -> sheets = $sheets;	
	
	}

	function manageprisoners( $structure_id ) 
	{
	
		$structure = StructureFactory_Model::create( null, $structure_id);
		$character = Character_Model::get_info( Session::instance()->get('char_id') );
		$subm    = new View ('template/submenu');
		$sheets  = array('gamelayout' => 'screen', 'submenu' => 'screen', 'character'=>'screen' );
		$view = new View ('barracks/manageprisoners');
		
		if ( ! $structure -> allowedaccess( $character, $structure -> getParentType(), $message, 'private', 'manageprisoners') )
		{
			Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
			url::redirect('region/view/');
		}			
		
		$prisoners = ORM::factory("character_sentence")
			-> where( array (
				'prison_id' => $structure_id,
				'imprisonment_start is not' => null,
				'status' => 'executing' ) )-> find_all();		
		
		$submenu = new View( 'structure/' . $structure -> getSubmenu() );
		$submenu -> id = $structure -> id;
		$submenu -> action = 'manageprisoners';
		
		$view -> submenu = $submenu;
		$view -> prisoners = $prisoners;
		$view -> structure = $structure;
		$this -> template -> content = $view;
		$this -> template -> sheets = $sheets;	
	
	}
	
	/*
	* Libera un prigioniero
	* @param none
	* @return none
	*/
	
	function freeprisoner()
	{
				
		$structure = StructureFactory_Model::create( null, $this -> input -> post('structure_id') );
		$character = Character_Model::get_info( Session::instance()->get('char_id') );
				
		if ( ! $structure -> allowedaccess( $character, $structure -> getParentType(), $message
		, 'private', 'freeprisoner') )
		{
			Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
			url::redirect('region/view/');
		}
		
		$ca = Character_Action_Model::factory("freeprisoner");		
		$par[0] = ORM::factory("character", Session::instance()->get('char_id')); 
		$par[1] = ORM::factory("character", $this->input->post('imprisoned_id') );
		$par[2] = $this->input->post('reason');
		$par[3] = $structure;
		$par[4] = ORM::factory("character_sentence", $this->input->post('sentence_id'));

	
		if ( $ca->do_action( $par,  $message ) )
		{ 
			Session::set_flash('user_message', "<div class=\"info_msg\">". $message . "</div>"); 
			url::redirect( '/barracks/manageprisoners/' . $this->input->post('structure_id'));
		}	
		else	
		{ 
			Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
			url::redirect( '/barracks/manageprisoners/' . $this->input->post('structure_id'));
		}		
		
		return;
		
	}
	/**
	* Pulisce le prigioni
	* @param qta parametro code
	* @return none
	*/
	
	function clean($qta = 1)
	{
		$ca = Character_Action_Model::factory("cleanprisons");				
		$par[0] = Character_Model::get_info( Session::instance()->get('char_id') );
		$par[1] = ORM::factory("region", $par[0]->position_id );
		$par[2] = $qta;
		
		if ( $ca->do_action( $par,  $message ) )
		 	Session::set_flash('user_message', "<div class=\"info_msg\">". $message . "</div>");		
		else			
			Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");					
		
		url::redirect('region/view/');
		
	}
	
	/**
	* blocca un char per un massimo di 48 hr.
	* @param none
	* @return none
	*/
	
	public function restrain( $structure_id = null )
	{
	
		$view = new View ('/barracks/restrain');
		$sheets  = array('gamelayout'=>'screen');		
		$character = Character_Model::get_info( Session::instance()->get('char_id') );

		$form = array(
			'target' => '',
			'reason' => '',  			
			'hours' => '',			
			);	
				
		if  (!$_POST)
		{		
			$structure = StructureFactory_Model::create( null, $structure_id);
			if ( ! $structure -> allowedaccess( $character, $structure -> getParentType(), $message,
				'private', 'restrain') )
			{
				Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
				url::redirect('region/view/');
			}
		}
		else
		{
		
			$structure = StructureFactory_Model::create( null, $this -> input -> post('structure_id'));
			
			if ( ! $structure->allowedaccess( $character, $structure -> getParentType(), $message,
				'private', 'restrain') )
			{
				Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
				url::redirect('region/view/');
			}
			
			$par[0] = $character;
			$par[1] = ORM::factory('character') -> where ( array( 'name' => $this->input->post('target') ) ) -> find(); 
			$par[2] = $this->input->post('hours');
			$par[3] = $this->input->post('reason');
			
			$form['target'] = $this->input->post('target');
			$form['reason'] = $this->input->post('reason');			
			$form['hours'] = $this->input->post('hours');		
			
			$ca = Character_Action_Model::factory("restrain");		
			if ( $ca->do_action( $par,  $message ) )
			{ 				
				Session::set_flash('user_message', "<div class=\"info_msg\">". $message . "</div>");				
				url::redirect ( 'barracks/managerestrained/' . $structure -> id);
			}	
			else	
			{ 
				$view -> hours =  $this -> input -> post('hours');
				$view -> reason = $this -> input -> post('reason');
				Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
			}
		
		}
		
		$submenu = new View( 'structure/' . $structure -> getSubmenu() );
		$submenu -> id = $structure -> id;
		$submenu -> action = 'managerestrained';
		
		$view -> submenu = $submenu;
		$this -> template->content = $view;
		$this -> template->sheets = $sheets;	
		$view -> structure = $structure;
		$view -> form = $form;
	}
	
	/*
	* Lista ordini di restrizione
	* @param $structure_id id struttura
	* @return none
	*/
	
	function managerestrained( $structure_id = null )
	{
		
		$character = Character_Model::get_info( Session::instance()->get('char_id') );		
		$view = new View ( '/barracks/managerestrained');		
		$sheets  = array('gamelayout' => 'screen', 'submenu' => 'screen', 'character'=>'screen' );
		
		if ( ! $_POST )
		{
			$structure = StructureFactory_Model::create( null, $structure_id);
		
			// controllo permessi		
			if ( ! $structure->allowedaccess( $character, $structure -> getParentType(), $message,
				'private', 'managerestrained') )
			{
				Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
				url::redirect('region/view/');
			}
			
			$db = Database::instance();
			
			$sql = "select c.name, c.id character_id, ca.* 
			from character_actions ca, characters c 
			where ca.action = 'restrain' 
			and    ca.character_id = c.id 
			and    ca.status = 'running' 
			and    ca.param1 = " . $character -> region_id ; 
								
			$rset = $db -> query( $sql ); 
			
			$view -> rset = $rset ; 
			$view -> structure = $structure; 
		
		}
		else
		{
			
			$structure = StructureFactory_Model::create( null, $this -> input -> post('structure_id') );

			// controllo permessi		
			if ( ! $structure->allowedaccess( $character, $structure -> getParentType(), $message,
				'private', 'managerestrained') )
			{
				Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
				url::redirect('region/view/');
			}
			
			$par[0] = $character;
			$par[1] = ORM::factory ( 'character', $this -> input -> post('character_id' ) ); 
			$par[2] = ORM::factory ( 'character_action', $this -> input -> post( 'action_id' ) ); 
			$par[3] = $this -> input -> post( 'reason' ); 
			
			$ca = Character_Action_Model::factory("cancelrestrain");		
			
			if ( $ca->do_action( $par,  $message ) )
			{ 				
				Session::set_flash('user_message', "<div class=\"info_msg\">". $message . "</div>");				
				url::redirect ( 'barracks/managerestrained/' . $structure -> id );
				return;
			}	
			else	
			{ 
				Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");				
				url::redirect ( 'barracks/managerestrained/' . $structure -> id );
				return;
			}
		}
		$submenu = new View( 'structure/' . $structure -> getSubmenu() );
		$submenu -> id = $structure -> id;
		$submenu -> action = 'managerestrained';
		$view -> submenu = $submenu;
		$this -> template -> content = $view; 
		$this -> template -> sheets = $sheets;	
	
	}	
	
	/*
	* Arresta un criminale
	* @param structure_id ID struttura
	* @return none
	*/
	
	function arrest( $criminal_id )
	{	
		
		$char = Character_Model::get_info( Session::instance()->get('char_id') );							
		$criminal = ORM::factory('character', $criminal_id ); 
		
		$par[0] = $char;
		$par[1] = $criminal;
		
		$ca = Character_Action_Model::factory("arrest");		

		if ( $ca->do_action( $par,  $message ) )
		{ 				
			Session::set_flash('user_message', "<div class=\"info_msg\">". $message . "</div>");				
			url::redirect ( 'region/view' );				
		}	
		else	
		{ 
			Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");	
			url::redirect ( 'region/view');
			
		}
		
	}
	
	/**
	* Lend selected items
	* @param none
	* @return none
	**/
	
	function lend ( )
	{
	
		$character = Character_Model::get_info( Session::instance()->get('char_id') );							
		$structure = StructureFactory_Model::create( null, $this -> input -> post('structure_id'));		
		
		// controllo permessi		
		if ( ! $structure->allowedaccess( $character, $structure -> getParentType(), $message,
			'private', 'lend') )
		{
			Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
			url::redirect('region/view/');
		}
		
		$par[0] = $character;
		$par[1] = $structure;
		$par[2] = ORM::factory('character') -> where ( 'name' , $this -> input -> post('target' ) ) -> find();
		$par[3] = $this -> input -> post( 'armoryitems' );
		
		$ca = Character_Action_Model::factory("lendarmoryitem");		

		if ( $ca->do_action( $par,  $message ) )
		{ 				
			Session::set_flash('user_message', "<div class=\"info_msg\">". $message . "</div>");				
			url::redirect ( 'barracks/armory/' . $structure -> id );				
		}	
		else	
		{ 
			Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");	
			url::redirect ( 'barracks/armory/' . $structure -> id );				
			
		}
		
	}
	
	/**
	* Visualizza il report prestiti
	* @param structure_id ID struttura
	* @return none
	**/
	
	function viewlends ( $structure_id )	
	{
	
		$character = Character_Model::get_info( Session::instance()->get('char_id') );							
		$structure = StructureFactory_Model::create( null, $structure_id );
		$view = new View ( '/barracks/viewlends');		
		$subm    = new View ('template/submenu');
		$sheets  = array('gamelayout' => 'screen', 'submenu' => 'screen', 'character'=>'screen' );
		$limit = 25;
	
		// controllo permessi		
		if ( ! $structure->allowedaccess( $character, $structure -> getParentType(), $message, 
		'private', 'viewlends' ) )
		{
			Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
			url::redirect('region/view/');
		}

		// find lent items (excluding the cloned 1 for send)
		
		$sql = 'select sl.id, c.name char_name, ci.name item_name, sl.lendtime, sl.deliverytime, sl.lender, sl.returnedtime 
		from structure_lentitems sl, characters c, structures s, items i, cfgitems ci
		where sl.structure_id = s.id
		and   sl.target_id = c.id 
		and   sl.id = i.lend_id 
		and   i.character_id != -1 
		and   i.cfgitem_id = ci.id 
		and   s.id = ' . $structure_id ; 
		
		$lends = Database::instance() -> query( $sql );
		
		$this -> pagination = new Pagination(array(
		'base_url'=>'barracks/viewlends/' . $structure -> id ,
		'uri_segment'=>'viewlends',
		'query_string' => 'page',
		'total_items'=> $lends -> count(),
		'items_per_page' => $limit ));		
	
		//var_dump( $lends ); exit; 
		
		$sql .= ' order by sl.id desc ';
		$sql .= " limit $limit offset " . $this -> pagination -> sql_offset ;
		
		$lends = Database::instance() -> query( $sql );		
		$submenu = new View( 'structure/' . $structure -> getSubmenu() );
		$submenu -> id = $structure -> id;
		$submenu -> action = 'armory';
		$view->submenu = $submenu;
		$view -> bonus = $structure -> get_premiumbonus( 'armory' );	
		$view -> pagination = $this -> pagination;
		$view -> lends = $lends ;
		$view -> structure = $structure;
		$this -> template -> content = $view; 
		$this -> template -> sheets = $sheets;	
		
	}
	
	/**
	* Delegate access to armory
	* @param structure_id ID struttura
	* @return none
	**/
	
	function givearmoryaccess( $structure_id = null )
	{
	
		$character = Character_Model::get_info( Session::instance() -> get('char_id') );							
		$view = new View ( '/barracks/givearmoryaccess');		
		$subm    = new View ('template/submenu');
		$sheets  = array('gamelayout' => 'screen', 'submenu' => 'screen', 'character'=>'screen' );
		$delegated = array();
		
		if ( !$_POST )
		{
			$structure = StructureFactory_Model::create( null, $structure_id );

			// controllo permessi		
			
			if ( ! $structure->allowedaccess( $character, $structure -> getParentType(), $message, 'private', 'armory' ) )
			{
				Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
				url::redirect('region/view/');
			}			
		}
		
		else
		{
			
			$structure = StructureFactory_Model::create( null, $this -> input -> post('structure_id'));
			// controllo permessi		
			
			if ( ! $structure->allowedaccess( $character, $structure -> getParentType(), $message, 'private', 'armory' ) )
			{
				Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
				url::redirect('region/view/');
			}
			
			
			$target = ORM::factory('character') -> where ( 'name', $this -> input -> post( 'target' ) ) -> find();
			
			$par[0] = $character;
			$par[1] = $structure;
			$par[2] = $target;	
			
			$ca = Character_Action_Model::factory("givearmoryaccess");		

			if ( $ca -> do_action( $par,  $message ) )
			{ 				
				Session::set_flash('user_message', "<div class=\"info_msg\">". $message . "</div>");				
				url::redirect ( 'barracks/givearmoryaccess/' . $structure -> id );				
			}	
			else	
			{ 
				Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");	
				url::redirect ( 'barracks/givearmoryaccess/' . $structure -> id );				
				
			}			

		}		
		$submenu = new View( 'structure/' . $structure -> getSubmenu() );
		$submenu -> id = $structure -> id;
		$submenu -> action = 'armory';
		$view->submenu = $submenu;
		$view -> bonus = $structure -> get_premiumbonus( 'armory' );					
		$view -> structure = $structure;
	
		$this -> template -> content = $view; 
		$this -> template -> sheets = $sheets;	
	
	}
	
	/**
	* Delegate access to armory
	* @param structure_id ID struttura
	* @return none
	**/
	
	function revokearmoryaccess( $structure_id, $target )
	{
	
		$character = Character_Model::get_info( Session::instance()->get('char_id') );									
		$structure = StructureFactory_Model::create( null, $structure_id);
		$target = ORM::factory('character') -> where( 'name', $target ) -> find(); 
		
		// controllo permessi		
		if ( ! $structure -> allowedaccess( $character, $structure -> getParentType(), $message, 'private', 'armory' ) )
		{
			Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
			url::redirect('region/view/');
		}			
						
		$par[0] = $structure;
		$par[1] = $target;	
		$par[2] = 'captain_assistant';
		
		$ca = Character_Action_Model::factory("revokestructuregrant");		

		if ( $ca->do_action( $par,  $message ) )
		{ 				
			Session::set_flash('user_message', "<div class=\"info_msg\">". $message . "</div>");				
			url::redirect ( 'barracks/givearmoryaccess/' . $structure -> id );				
		}	
		else	
		{ 
			Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");	
			url::redirect ( 'barracks/givearmoryaccess/' . $structure -> id );				
			
		}
		
	}

	function assign_rolerp( $structure_id )
	{
	
		$view   = new View ( 'barracks/assign_rolerp' );
		$sheets = array('gamelayout'=>'screen', 'submenu'=>'screen');
		$subm   = new View ('template/submenu');
		
		// Inizializzo le form
		$formroles = array
		( 
			'role'        => 'lieutenant',		
			'region'      => null,
			'nominated'   => null,
			'place'       => null,
			);

		$roles = array
		( 
			'lieutenant'   => kohana::lang('global.lieutenant_m')
		);

		$character = Character_Model::get_info( Session::instance()->get('char_id') );

		if ( !$_POST ) 
		{
			$structure = StructureFactory_Model::create( null, $structure_id );

			// controllo permessi		
			if ( ! $structure -> allowedaccess( $character, $structure -> getParentType(), $message, 
				'private', 'assign_rolerp' ) )
			{
				Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
				url::redirect('region/view/');
			}			
		}
		else
		{				
			$structure = StructureFactory_Model::create( null, $this -> input -> post('structure_id') );
			
			// controllo permessi		
			if ( ! $structure -> allowedaccess( $character, $structure -> getParentType(), $message, 
				'private', 'assign_rolerp' ) )
			{
				Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
				url::redirect('region/view/');
			}
			$ca = Character_Action_Model::factory("assignrolerp");		
			$par[0] = $character;
			$par[1] = ORM::factory( 'character' )->where( array('name' => $this->input->post('nominated')) )->find(); 
			$par[2] = $this->input->post( 'role' );
			$par[3] = ORM::factory( 'region', $this->input->post( 'region_id' ) ); 
			$par[4] = ORM::factory( 'structure', $this->input->post( 'structure_id' ) );
			$par[5] = $this->input->post( 'place' );
			
			if ( $ca->do_action( $par,  $message ) )
			{
				Session::set_flash('user_message', "<div class=\"info_msg\">". $message . "</div>");
				url::redirect('barracks/manage/' . $structure->id);
			}	
			else	
			{ 
				Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>"); 
				url::redirect ( 'barracks/assign_rolerp/' . $structure->id );
			}
		}
		
		$submenu = new View( 'structure/' . $structure -> getSubmenu() );
		$submenu -> id = $structure -> id;
		$submenu -> action = 'assign_rolerp';
		$view -> submenu = $submenu;
		$view -> structure = $structure; 
		$view -> formroles = $formroles;
		$view -> roles = $roles;
		$this->template->content = $view;
		$this->template->sheets = $sheets;
				
	}	
}
