<?php defined('SYSPATH') OR die('No direct access allowed.');

class Court_Controller extends Template_Controller
{
	// Imposto il nome del template da usare
	public $template = 'template/gamelayout';
	
	/*
	 * Apre una procedura per un crimine
	 * @param int $structure_id ID struttura
	 * @return none
	*/
	
	public function opencrimeprocedure( $structure_id )	
	{
			
		$form = array(
			'target' => '',
			'summary' => '',  			
			'trialurl' => '',
		);				
		
		$view = new view('court/opencrimeprocedure');
		$sheets  = array('gamelayout'=>'screen', 'submenu'=>'screen');		
		$character = Character_Model::get_info( Session::instance()->get('char_id') );		
		$subm    = new View ('template/submenu');
			
		if ( ! $_POST )
		{
		
			$structure = StructureFactory_Model::create( null, $structure_id );	
			if ( ! $structure->allowedaccess( $character, $structure -> getParentType(), $message,
				'private', 'opencrimeprocedure') )
			{
				Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
				url::redirect('region/view/');
			}
		
		}
		else
		{					
			//var_dump($_POST); exit; 		
			
			$structure = StructureFactory_Model::create( null, $this -> input -> post('structure_id') );
			if ( ! $structure->allowedaccess( $character, $structure -> getParentType(), $message,
				'private', 'opencrimeprocedure') )
			{
				Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
				url::redirect('region/view/');
			}
			
			$ca = Character_Action_Model::factory("opencrimeprocedure");		
			$par[0] = $character;
			$par[1] = ORM::factory("character") -> 
				where ( array ( 'name' => $this -> input -> post ('target') ) ) -> find(); 
			$par[2] = $this -> input -> post('summary' );
			$par[3] = $structure;
			$par[4] = $this -> input -> post('trialurl' );
			
			$form['target'] = $this->input->post('target');
			$form['summary'] = $this->input->post('summary');			
			$form['trialurl'] = $this->input->post('trialurl');			

			if ( $ca->do_action( $par,  $message ) )
			{
				Session::set_flash('user_message', "<div class=\"info_msg\">". $message . "</div>"); 							
				url::redirect('court/listcrimeprocedures/' . $structure -> id);
			}	
			else	
			{ 			
				Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
			}
		
		}
				
		$submenu = new View( 'structure/' . $structure -> getSubmenu() );
		$submenu -> id = $structure -> id;
		$submenu -> action = 'opencrimeprocedure';
		$view->submenu = $submenu;
		$view -> structure = $structure;
		$view -> form = $form;
		$this -> template -> content = $view;
		$this -> template -> sheets = $sheets;
	
	}
	
	/*
	 * Elenca le procedure di incriminazione
	 * @param structure_id ID struttura
	 * @return none
	*/
	
	public function listcrimeprocedures( $structure_id )	
	{
		$limit = 10	;
		$orderby = 'p.id';
		$direction = 'desc';
		
		$view = new view('court/listcrimeprocedures');
		$sheets  = array('gamelayout'=>'screen', 'submenu'=>'screen');		
		$character = Character_Model::get_info( Session::instance()->get('char_id') );		
		$structure = StructureFactory_Model::create( null, $structure_id );
		
		if ( ! $structure->allowedaccess( $character, $structure -> getParentType(), $message,
			'private', 'listcrimeprocedure') )
		{
			Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
			url::redirect('region/view/');
		}
		
		$db = Database::instance();
		$sql = "select c.name, p.id, p.text, p.structure_id, p.issuedate, p.trialurl, p.status 
			from characters c, character_sentences p
			where p.character_id = c.id			
			and   p.structure_id = " . $structure_id ;
		
		$crimeprocedures = $db -> query ( $sql )  -> as_array(); 
		
		$this -> pagination = new Pagination(array(
			'base_url'=>'court/listcrimeprocedures/' . $structure -> id ,
			'uri_segment'=> $structure -> id ,			
			'query_string' => 'page',
			'total_items'=> count( $crimeprocedures ),
			'items_per_page'=> $limit ));				
			
		
		$sql .= " order by $orderby $direction ";
		$sql .= " limit $limit offset " . $this->pagination->sql_offset ;
		$crimeprocedures = $db -> query ( $sql ) -> as_array(); 
		
		$submenu = new View( 'structure/' . $structure -> getSubmenu() );
		$submenu -> id = $structure -> id;
		$submenu -> action = 'listcrimeprocedures';
		$view->submenu = $submenu;
		$view -> pagination = $this -> pagination;		
		$view -> structure = $structure;
		$view -> crimeprocedures = $crimeprocedures;
		$this -> template -> content = $view;
		$this -> template -> sheets = $sheets;
		
	}
	
	/*
	 * Modifica una procedure di incriminazione
	 * @param int $structure_id ID struttura
	 * @param int $crimeprocedure_id ID Procedura
	 * @return none
	*/
	
	public function editcrimeprocedure( $structure_id, $crimeprocedure_id )	
	{
	
		$view = new view('court/editcrimeprocedure');
		$sheets  = array('gamelayout'=>'screen', 'submenu'=>'screen');		
		$character = Character_Model::get_info( Session::instance()->get('char_id') );		
		$subm    = new View ('template/submenu');
		
		$form = array(			
			'summary' => '',  			
			'trialurl' => '',
		);		
		
		if ( !$_POST )
		{
		
			$structure = StructureFactory_Model::create( null, $structure_id );
			
			if ( ! $structure->allowedaccess( $character, $structure -> getParentType(), $message,
				'private', 'editcrimeprocedure') )
			{
				Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
				url::redirect('region/view/');
			}
			
			$crimeprocedure = ORM::factory('character_sentence', $crimeprocedure_id ); 		
			$form['summary'] = $crimeprocedure -> text;
			$form['trialurl'] = $crimeprocedure -> trialurl;			
			
		}
		else
		{
			
			$structure = StructureFactory_Model::create( null, $this -> input -> post('structure_id') );
			
			if ( ! $structure -> allowedaccess( $character, $structure -> getParentType(), $message, 
				'private', 'editcrimeprocedure' ) )	
			{
				Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
				url::redirect('region/view/');
			}
			
			$crimeprocedure = ORM::factory('character_sentence', $this -> input -> post('crimeprocedure_id')); 		
			
			if ( $crimeprocedure -> status != 'new' )
			{
				Session::set_flash('user_message', "<div class=\"error_msg\">". kohana::lang('structures_court.procedurenotvalid'). "</div>");
				url::redirect('/court/listcrimeprocedures/' . $structure -> id ); 
			}
			
			
			$crimeprocedure -> text = $this -> input -> post('summary');
			$crimeprocedure -> trialurl = $this -> input -> post('trialurl');
			$crimeprocedure -> save();
			Session::set_flash('user_message', "<div class=\"info_msg\">". 
				kohana::lang('structures_court.info-modifiedok') . "</div>");			
			url::redirect('/court/listcrimeprocedures/' . $structure_id ); 
		}
				
		$submenu = new View( 'structure/' . $structure -> getSubmenu() );
		$submenu -> id = $structure -> id;
		$submenu -> action = 'listcrimeprocedures';
		$view->submenu = $submenu;
		$target = ORM::factory('character', $crimeprocedure -> character_id ); 
		$view -> target = $target;
		$view -> structure = $structure;
		$view -> crimeprocedure = $crimeprocedure;
		$view -> form = $form;
		$this -> template -> content = $view;
		$this -> template -> sheets = $sheets;
	
	}
	
	/*
	 * Modifica una procedure di incriminazione
	 * @param int $structure_id ID struttura
	 * @oaram int $crimeprocedure_id ID procedura
	 * @return none
	*/
	
	public function cancelcrimeprocedure( $structure_id, $crimeprocedure_id )	
	{		
		
		$form = array(			
			'cancelreason' => '',  						
		);		
		
	
		$view = new view('court/cancelcrimeprocedure');
		$sheets  = array('gamelayout'=>'screen', 'submenu'=>'screen');		
		$character = Character_Model::get_info( Session::instance()->get('char_id') );		
		$subm    = new View ('template/submenu');
		
		if ( !$_POST )
		{
						
			$structure = StructureFactory_Model::create( null, $structure_id );
			if ( ! $structure->allowedaccess( $character, $structure -> getParentType(), $message, 
				'private', 'cancelcrimeprocedure' ) )		
			{
				Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
				url::redirect('region/view/');
			}
			$crimeprocedure = ORM::factory('character_sentence', $crimeprocedure_id ); 		
			$form['cancelreason'] = $crimeprocedure -> cancelreason;
			
		}
		else
		{					
			
			$structure = StructureFactory_Model::create( null, $this -> input -> post('structure_id') );

			if ( ! $structure->allowedaccess( $character, $structure -> getParentType(), $message, 
				'private', 'cancelcrimeprocedure' ) )		
			{
				Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
				url::redirect('region/view/');
			}
			
			
			$crimeprocedure = ORM::factory('character_sentence', $this -> input -> post('crimeprocedure_id')); 		
			
			if ( $crimeprocedure -> status != 'new' )
			{
				Session::set_flash('user_message', "<div class=\"error_msg\">". kohana::lang('structures_court.procedurenotvalid'). "</div>");
				url::redirect('/court/listcrimeprocedures/' . $structure -> id ); 
			}			
			
			$crimeprocedure -> cancelreason = $this -> input -> post('cancelreason' ) ; 
			$crimeprocedure -> status = 'canceled' ; 			
			$crimeprocedure -> save();
			Session::set_flash('user_message', "<div class=\"info_msg\">". 
				kohana::lang('structures_court.info-canceledok') . "</div>");	
			url::redirect('/court/listcrimeprocedures/' . $structure_id ); 
		}
		
		
		$submenu = new View( 'structure/' . $structure -> getSubmenu() );
		$submenu -> id = $structure -> id;
		$submenu -> action = 'listcrimeprocedures';
		$view->submenu = $submenu;
		$target = ORM::factory('character', $crimeprocedure -> character_id ); 
		$view -> target = $target;
		$view -> form = $form;
		$view -> structure = $structure;
		$view -> crimeprocedure = $crimeprocedure;		
		$this -> template -> content = $view;
		$this -> template -> sheets = $sheets;
	
	}
	
	/*
	 * Visualizza una procedura di incriminazione
	 * @param structure_id ID struttura
	 * @oaram crimeprocedure_id ID procedura
	 * @return none
	*/
	
	function viewcrimeprocedure( $structure_id, $crimeprocedure_id )
	{
	
		$view = new view('court/viewcrimeprocedure');
		$sheets  = array('gamelayout'=>'screen', 'submenu'=>'screen');		
		$structure = StructureFactory_Model::create( null, $structure_id );
		$subm    = new View ('template/submenu');
		$character = Character_Model::get_info( Session::instance()->get('char_id') );		

		$crimeprocedure = ORM::factory('character_sentence', $crimeprocedure_id ); 
		if ( ! $structure -> allowedaccess( $character, $structure -> getParentType(), $message, 'private', 'viewcrimeprocedure' ) )
		{
			Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
			url::redirect('region/view/');
		}
		
		if ( !$crimeprocedure -> character -> loaded )
			$criminal = ORM::factory('character', $crimeprocedure -> character_id );		
		else
			$criminal = $crimeprocedure -> character;
			
		$sheriff = ORM::factory('character', $crimeprocedure -> arrested_by );		
		if ( !$sheriff -> loaded )
			$sheriff = ORM::factory('ar_character', $crimeprocedure -> arrested_by );
		
		$lnkmenu = $structure -> get_horizontalmenu( 'listcrimeprocedures' );
		$subm -> submenu = $lnkmenu;
		$view -> submenu = $subm;	
		$view -> structure = $structure;
		$view -> sheriff = $sheriff;		
		$view -> criminal = $criminal;
		$view -> crimeprocedure = $crimeprocedure;
		$this -> template -> content = $view;
		$this -> template -> sheets = $sheets;		
	
	}
	
	
	/*
	 * Modifica una procedure di incriminazione
	 * @param int $structure_id ID struttura
	 * @oaram int $crimeprocedure_id ID procedura
	 * @return none
	*/
	
	function writearrestwarrant( $structure_id, $crimeprocedure_id )
	{
	
		$character = Character_Model::get_info( Session::instance()->get('char_id') );		
		$structure = StructureFactory_Model::create( null, $structure_id );

		
		if ( ! $structure -> allowedaccess( $character, $structure -> getParentType(), $message, 'private', 'writearrestwarrant' ) )
		{
			Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
			url::redirect('region/view/');
		}
		
		$ca = Character_Action_Model::factory("writearrestwarrant");				
		$par[0] = Character_Model::get_info( Session::instance()->get('char_id') ); 
		$par[1] = ORM::factory('structure', $structure_id );
		$par[2] = ORM::factory('character_sentence', $crimeprocedure_id );
		
		if ( $ca -> do_action( $par,  $message ) )
		{
			Session::set_flash('user_message', "<div class=\"info_msg\">". $message . "</div>"); 			
			url::redirect('/court/listcrimeprocedures/' . $structure_id ); 
		}	
		else
		{ 			
			Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
			url::redirect('/court/listcrimeprocedures/' . $structure_id ); 
		}
	
	}	
	
	/*
	 * Imprigiona un criminale
	 * @param int $character_id id del personaggio da imprigionare
	 * @param int $crimeprocedure_id id della procedura di incriminazione
	 * @return none	 
	*/
	
	function imprison( $structure_id = null, $crimeprocedure_id = null )
	{

		$view = new View ('/court/imprison');
		$sheets  = array('gamelayout'=>'screen', 'submenu'=>'screen');		
		$character = Character_Model::get_info( Session::instance()->get('char_id') );
		$form = array( 'hours' => '', 'prison' => '');
		$db = Database::instance();		
		$subm    = new View ('template/submenu');
		
		if ( !$_POST)
		{
			$structure = StructureFactory_Model::create( null, $structure_id );
			if ( ! $structure -> allowedaccess( $character, $structure -> getParentType(), $message, 'private', 'imprison' ) )
			{
				Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
				url::redirect('region/view/');
			}
		
			$crimeprocedure = ORM::factory('character_sentence', $crimeprocedure_id );			
		}
		else
		{			
			$structure = StructureFactory_Model::create( null, $this -> input -> post('structure_id') );
			if ( ! $structure -> allowedaccess( $character, $structure -> getParentType(), $message, 'private', 'imprison' ) )
			{
				Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
				url::redirect('region/view/');
			}
			
			$crimeprocedure = ORM::factory('character_sentence', $this -> input -> post ('crimeprocedure_id' ));			
			$par[0] = $character;
			$par[1] = $crimeprocedure;
			$par[2] = intval($this -> input -> post('hours')); 
			$par[3] = ORM::factory('structure', $this -> input -> post('prison') ); 
			$par[4] = ORM::factory('structure', $crimeprocedure -> structure_id ); 
		
			$form['hours'] = $this -> input -> post('hours'); 
			$form['prison'] = $this -> input -> post('prison'); 
			
			$ca = Character_Action_Model::factory("imprison");		

			if ( $ca -> do_action( $par,  $message ) )
			{ 				
				Session::set_flash('user_message', "<div class=\"info_msg\">". $message . "</div>");				
				url::redirect ( '/court/listcrimeprocedures/' . $par[4] -> id );				
			}	
			else	
			{ 
				Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");												
			}
		
		}
		
		$prisons = $db -> query ( "
			select s.id, r.name
			from structures s, structure_types st, regions r
			where s.structure_type_id = st.id
			and   s.region_id = r.id  
			and   st.supertype = 'barracks'			
			and   s.region_id in ( select id from regions where kingdom_id = " . $character -> region -> kingdom -> id . ")" ) -> as_array();
			
		foreach ( $prisons as $prison )
			$combo_prison[ $prison -> id ] = kohana::lang( $prison -> name );
		
		$submenu = new View( 'structure/' . $structure -> getSubmenu() );
		$submenu -> id = $structure -> id;
		$submenu -> action = 'listcrimeprocedures';
		$view->submenu = $submenu;
		$view -> combo_prisons = $combo_prison;	
		$view -> crimeprocedure = $crimeprocedure;
		$view -> structure = $structure;
		$this -> template -> sheets = $sheets;
		$this -> template -> content = $view;
		$view -> form = $form;
		
	}
	
	/**
	* assign_rolerp
	* Assegna i titoli e gli incarichi reali ai giocatori	
	* @param int $structure_id id struttura
	* @return  none
	**/
	
	function assign_rolerp( $structure_id )
	{
	
		$view   = new View ( 'court/assign_rolerp' );
		$sheets = array('gamelayout'=>'screen', 'submenu'=>'screen');
		$subm   = new View ('template/submenu');
		$character = Character_Model::get_info( Session::instance()->get('char_id') );

		// Inizializzo le form
		$formroles = array
		( 
			'role'        => 'bailiff',		
			'region'      => null,
			'nominated'   => null,
			'place'       => null,
		);

		// Definisco gli incarichi reali assegnabili
		$roles = array
		( 
			'bailiff'   => kohana::lang('global.bailiff_m')
		);


		if ( !$_POST ) 
		{
			$structure = StructureFactory_Model::create( null, $structure_id );
			
			if ( ! $structure->allowedaccess( $character, $structure -> getParentType(), $message, 
				'private', 'assign_rolerp' ) )
			{
				Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
				url::redirect('region/view/');
			}			
		}
		else
		{	
			$structure = StructureFactory_Model::create( null, $this -> input -> post('structure_id') );
			if ( ! $structure -> allowedaccess( $character, $structure -> getParentType(), $message, 
				'private', 'assign_rolerp' ) )
			{
				Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
				url::redirect('region/view/');
			}
			$ca = Character_Action_Model::factory("assignrolerp");		
			//var_dump( $_POST ); exit;
			// Characther che nomina
			$par[0] = $character;
			// Character nominato
			$par[1] = ORM::factory( 'character' )->where( array('name' => $this->input->post('nominated')) )->find(); 
			// Tag ruolo
			$par[2] = $this->input->post( 'role' );
			// Regione dove avviene la nomina
			$par[3] = ORM::factory( 'region', $this->input->post( 'region_id' ) ); 
			// Struttura da dove avviene la nomina
			$par[4] = ORM::factory( 'structure', $this->input->post( 'structure_id' ) );
			// Nome del feudo da associare al titolo
			$par[5] = $this->input->post( 'place' );
			
			if ( $ca->do_action( $par,  $message ) )
			{
				Session::set_flash('user_message', "<div class=\"info_msg\">". $message . "</div>");
				url::redirect('court/assign_rolerp/' . $structure->id);
			}	
			else	
			{ 
				Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>"); 
				url::redirect ( 'court/assign_rolerp/' . $structure->id );
			}
		}
		
		$submenu = new View( 'structure/' . $structure -> getSubmenu() );
		$submenu -> id = $structure -> id;
		$submenu -> action = 'assign_rolerp';
		$view -> submenu = $submenu;	
		$view -> structure = $structure;
		$view -> formroles = $formroles;
		$view -> roles = $roles;
		$this -> template->content = $view;
		$this -> template->sheets = $sheets;
				
	}	
}
