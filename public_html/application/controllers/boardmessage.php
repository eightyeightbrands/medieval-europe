<?php defined('SYSPATH') OR die('No direct access allowed.');

class Boardmessage_Controller extends Template_Controller
{
	
	
	public $template = 'template/gamelayout';
	
	/**
	* lista messaggi
	* @param structure_id id struttura
	* @param category categoria
	* @return none
	*/
	
	function index( $category = 'job', $status = 'ALL' )
	{
		
		$db = Database::instance();
		$auth = Auth::instance();			
	
		$sheets  = array('gamelayout'=>'screen', 'submenu'=>'screen');		
		$submenu  = new View ('template/submenu_boardmessage');
		$character = Character_Model::get_info( Session::instance()->get('char_id') );
		$currentposition = ORM::factory('region', $character -> position_id );
		
		$c = BoardMessage_Model::factory( $category );	
		$params[0] = $currentposition -> kingdom_id ;
		$params[1] = $category;	
		$params[2] = $character; 
		$params[3] = $status;
		
		$sql = $c -> get_sql( $params );
		$view = $c -> get_view('index');
		$limit = $c -> get_limit();
		
		$messages = $db -> query( $sql );
		
		$this->pagination = new Pagination(array(
			'base_url' => 'boardmessage/index/' . $category . '/' . $status, 
			'uri_segment' => 'index',
			'query_string' => 'page',
			'total_items' => $messages -> count(),
			'items_per_page'=> $limit ));
		
		$sql .= " order by starpoints desc, id desc ";
		$sql .= " limit $limit offset " . $this -> pagination -> sql_offset ;		
		//var_dump($sql);exit;
		$messages = $db -> query( $sql );
				
		$view -> auth = $auth;		
		$view -> reader = $character;
		$view -> currentposition = $currentposition;		
		$view -> status = $status;
		$view -> pagination = $this -> pagination;
		$view -> messages = $messages;
		$submenu -> category = $category;
		$view -> category = $category;
		$view -> submenu = $submenu;			
		$this -> template -> sheets = $sheets;
		$this -> template -> content = $view; 		
	}	

	/**
	* aggiunge messaggio
	* @param category categoria del messaggio	
	* @return none
	*/
	
	function add( $category = 'job' ) 
	{
					
		$sheets  = array('gamelayout'=>'screen', 'submenu'=>'screen');
		$character = Character_Model::get_info( Session::instance()->get('char_id') );		
		$currentposition = ORM::factory('region', $character -> position_id );
		$auth = Auth::instance();	
		$db = Database::instance(); 
		$c = BoardMessage_Model::factory( $category );
		$view = $c -> get_view( 'add' );			
		
		if ( Character_Model::is_traveling( $character -> id ))
		{
			url::redirect('map/view');
			return;			
		}
		
		if ( !$_POST )
		{
			
			if ( ! $c -> is_commandallowed( 'add' ) )
			{
				Session::set_flash('user_message', "<div class=\"error_msg\">" . 
					kohana::lang('global.operation_not_allowed') . "</div>" );
				url::redirect( 'boardmessage/index/' . $category );
			}						
			
			// costruisco il form specifico
			
			$form = $c -> get_form( 'add' ) ; 
		}
		else
		{
			//var_dump($_POST);exit;
			$form = $c -> get_form( 'add' ) ; 
			
			if ( ! $c -> is_commandallowed( 'add' ) )
			{
				Session::set_flash('user_message', "<div class=\"error_msg\">" . 
					kohana::lang('global.operation_not_allowed') . "</div>" );
				url::redirect( 'boardmessage/index/' . $category );
			}
			
			$params[0] = $this -> input -> post();
			$params[1] = $character;
			$params[2] = $currentposition;	
			$params[3] = $auth;
			
			//var_dump($c); exit;
			
			$rc = $c -> add( $params, $message ); 
			
			if ( $rc )
			{				
				Session::set_flash('user_message', "<div class=\"info_msg\">" . $message . "</div>" );
				url::redirect( 'boardmessage/index/' . $category);

			}
			else
			{
				$form = arr::overwrite( $form, $params[0] ); 
			
				Session::set_flash('user_message', "<div class=\"error_msg\">" . $message . "</div>" );
			}
			
			
		}
		
		$view -> character = $character; 
		$view -> form = $form;
		$view -> currentposition = $currentposition;
		$this -> template -> sheets = $sheets;
		$this -> template -> content = $view; 
	
	}
	
	/**
	* modifica messaggio
	* @param message_id id messaggio
	* @return none
	*/
	
	function edit( $message_id ) 
	{		
			
		$sheets  = array('gamelayout'=>'screen', 'submenu'=>'screen');
		$character = Character_Model::get_info( Session::instance()->get('char_id') );
		$currentposition = ORM::factory('region', $character -> position_id );				
		$auth = Auth::instance();			
		$view = null;
		$form = null;
		
		if ( Character_Model::is_traveling( $character -> id ))
		{
			url::redirect('map/view');
			return;			
		}
		
		// messaggio non esistente
		
		if ( !$_POST )
		{
			// carica messaggio
			$message = ORM::factory('boardmessage', $message_id );
			
			// Il messaggio esiste?
			
			if ( ! $message -> loaded )
			{
				Session::set_flash('user_message', "<div class=\"error_msg\">" . kohana::lang('global.operation_not_allowed') . "</div>" );
				url::redirect( 'boardmessage/index/' );
			}	
			
			// solo gli admin possono editare o aggiungere annunci di tipo suggestion
			
			if ( $message -> category == 'suggestion' and ! $auth -> logged_in('admin') )
			{
					Session::set_flash('user_message', "<div class=\"error_msg\">" . 
						kohana::lang('global.operation_not_allowed') . "</div>" );
					url::redirect( 'boardmessage/index/' . $message -> category );
			}	
			
			$c = BoardMessage_Model::factory( $message -> category );
			if ( ! $c -> is_commandallowed( 'edit' ) )
			{
				Session::set_flash('user_message', "<div class=\"error_msg\">" . 
					kohana::lang('global.operation_not_allowed') . "</div>" );
				url::redirect( 'boardmessage/index/' . $message -> category );
			}
			
			$form = $c -> get_form( 'edit' );
			$form = arr::overwrite($form, $message -> as_array());				 			
		}
		else
		{
		
			$message = ORM::factory('boardmessage', $this -> input -> post('id') );
			
			// Il messaggio esiste?			
			if ( ! $message -> loaded )
			{
				Session::set_flash('user_message', "<div class=\"error_msg\">" . kohana::lang('global.operation_not_allowed') . "</div>" );
				url::redirect( 'boardmessage/index' );
			}	
			
			// solo gli admin possono editare o aggiungere annunci di tipo suggestion			
			if ( $message -> category == 'suggestion' and ! $auth -> logged_in('admin') )
			{
					Session::set_flash('user_message', "<div class=\"error_msg\">" . 
						kohana::lang('global.operation_not_allowed') . "</div>" );
					url::redirect( 'boardmessage/index/' . $category );
			}	
			
			$c = BoardMessage_Model::factory( $message -> category );
			if ( ! $c -> is_commandallowed( 'edit' ) )
			{
				Session::set_flash('user_message', "<div class=\"error_msg\">" . 
					kohana::lang('global.operation_not_allowed') . "</div>" );
				url::redirect( 'boardmessage/index/' . $message -> category );
			}
			
			$form = $c -> get_form( 'edit' );
			$params[0] = $this -> input -> post();
			$params[1] = $character;
			$params[2] = $currentposition;	
			$params[3] = $message;
			$params[4] = $auth;
			
			$rc = $c -> edit( $params, $m ); 
			
			if ( $rc )
			{				
				Session::set_flash('user_message', "<div class=\"info_msg\">" . $m . "</div>" );
				url::redirect('boardmessage/index/' . $message -> category );
			}
			else
			{
				$form = arr::overwrite( $form, $params[0] ); 
				//var_dump( $form ); exit; 
				Session::set_flash('user_message', "<div class=\"error_msg\">" . $m . "</div>" );
			}
		}				
		
		
		$view = $c -> get_view( 'edit' );		
		$view -> message = $message;
		$view -> character = $character; 
		$view -> form = $form;
		$view -> currentposition = $currentposition;
		$this -> template -> sheets = $sheets;
		$this -> template -> content = $view; 
	
	}
	
	/**
	* Cambia la visibilità del messaggio
	* @param structure_id id struttura
	* @param message_id id messaggio
	* @return none
	*/
	
	function give_globalvisibility( $message_id )
	{	
		
		$message = ORM::factory('boardmessage', $message_id );		
		$character = Character_Model::get_info( Session::instance()->get('char_id') );
		$currentposition = ORM::factory('region', $character -> position_id );
		
		if ( Character_Model::is_traveling( $character -> id ))
		{
			url::redirect('map/view');
			return;			
		}
		
		//messaggio non trovato
		if ( ! $message -> loaded )
		{
			Session::set_flash('user_message', "<div class=\"error_msg\">" . 
				kohana::lang('global.operation_not_allowed') . "</div>" );
			url::redirect( 'boardmessage/index/' );
		}
		
		$c = BoardMessage_Model::factory( $message -> category );
		if ( ! $c -> is_commandallowed( 'give_globalvisibility' ) )
		{
			Session::set_flash('user_message', "<div class=\"error_msg\">" . 
				kohana::lang('global.operation_not_allowed') . "</div>" );
			url::redirect( 'boardmessage/index/' . $message -> category );
		}
		$params[0] = $currentposition;
		$params[1] = $character;
		$params[2] = $message;
		
		$rc = $c -> give_globalvisibility($params, $m);
		
		if ( $rc )
		{				
			Session::set_flash('user_message', "<div class=\"info_msg\">" . $m . "</div>" );			
		}
		else
		{			
			Session::set_flash('user_message', "<div class=\"error_msg\">" . $m . "</div>" );
		} 
		
		url::redirect('boardmessage/index/' . $message -> category );
				
		
	}
	
	/**
	* Bump dell' annuncio
	* @param structure_id id struttura
	* @param message_id id messaggio
	* @return none
	*/
	
	function bump_up( $message_id )
	{
		
		$message = ORM::factory('boardmessage', $message_id );
		$character = Character_Model::get_info( Session::instance()->get('char_id') );
		$currentposition = ORM::factory('region', $character -> position_id );
		
		if ( Character_Model::is_traveling( $character -> id ))
		{
			url::redirect('map/view');
			return;			
		}
		
		// messaggio non esistente		
		if ( ! $message -> loaded )
		{
			Session::set_flash('user_message', "<div class=\"error_msg\">" . 
				kohana::lang('global.operation_not_allowed') . "</div>" );
			url::redirect( 'boardmessage/index/');
		}
	
		$c = BoardMessage_Model::factory( $message -> category );
		if ( ! $c -> is_commandallowed( 'bump_up' ) )
		{
			Session::set_flash('user_message', "<div class=\"error_msg\">" . 
				kohana::lang('global.operation_not_allowed') . "</div>" );
			url::redirect( 'boardmessage/index/' . $message -> category );
		}
		$params[0] = $currentposition;
		$params[1] = $character;
		$params[2] = $message;
		
		$rc = $c -> bump_up($params, $m);
		
		if ( $rc )
		{				
			Session::set_flash('user_message', "<div class=\"info_msg\">" . $m . "</div>" );			
		}
		else
		{			
			Session::set_flash('user_message', "<div class=\"error_msg\">" . $m . "</div>" );
		} 
		
		url::redirect('boardmessage/index/' . $message -> category );
		
	}
	
	/**
	* Delete dell' annuncio
	* @param structure_id id struttura
	* @param message_id id messaggio
	* @return none
	*/
	
	function delete( $message_id )
	{
		
		$message = ORM::factory('boardmessage', $message_id );
		$character = Character_Model::get_info( Session::instance()->get('char_id') );
		$currentposition = ORM::factory('region', $character -> position_id );
		$auth = Auth::instance();		
		
		if ( Character_Model::is_traveling( $character -> id ))
		{
			url::redirect('map/view');
			return;			
		}
		
		// messaggio non esistente
		if ( ! $message -> loaded )
		{
			Session::set_flash('user_message', "<div class=\"error_msg\">" . 
				kohana::lang('global.operation_not_allowed') . "</div>" );
			url::redirect( 'boardmessage/index' );
		}
		
		$c = BoardMessage_Model::factory( $message -> category );
		if ( ! $c -> is_commandallowed( 'delete_message' ) )
		{
			Session::set_flash('user_message', "<div class=\"error_msg\">" . 
				kohana::lang('global.operation_not_allowed') . "</div>" );
			url::redirect( 'boardmessage/index/' . $message -> category );
		}
		$params[0] = $currentposition;
		$params[1] = $character;
		$params[2] = $message;
		$params[3] = $auth;
		
		$category = $message -> category;
		
		$rc = $c -> delete_message($params, $m);
		
		if ( $rc )
		{				
			Session::set_flash('user_message', "<div class=\"info_msg\">" . $m . "</div>" );			
		}
		else
		{			
			Session::set_flash('user_message', "<div class=\"error_msg\">" . $m . "</div>" );
		} 
		
		url::redirect('boardmessage/index/' . $category );
				
	}
	
	/**
	* Segnala un annuncio	
	* @param message_id id messaggio
	* @return none
	*/
	
	function report( $message_id )
	{
		
		
		$character = Character_Model::get_info( Session::instance()->get('char_id') );
		$currentposition = ORM::factory('region', $character -> position_id );
		$sheets  = array('gamelayout'=>'screen', 'submenu'=>'screen');
		
		if ( Character_Model::is_traveling( $character -> id ))
		{
			url::redirect('map/view');
			return;			
		}
		
		// messaggio non esistente
		
		
		if ( !$_POST )
		{
		
			$message = ORM::factory('boardmessage', $message_id );
			if ( ! $message -> loaded )
			{
				Session::set_flash('user_message', "<div class=\"error_msg\">" . 
					kohana::lang('global.operation_not_allowed') . "</div>" );
				url::redirect( 'boardmessage/index' );
			}		
			
			$c = BoardMessage_Model::factory( $message -> category );
			if ( ! $c -> is_commandallowed( 'report' ) )
			{
				Session::set_flash('user_message', "<div class=\"error_msg\">" . 
					kohana::lang('global.operation_not_allowed') . "</div>" );
				url::redirect( 'boardmessage/index/' . $message -> category );
			}

			$form = $c -> get_form('report');
			$form = arr::overwrite($form, $message -> as_array());				 			

		}
		else
		{
			$message = ORM::factory('boardmessage', $this -> input -> post('id') );
			if ( ! $message -> loaded )
			{
				Session::set_flash('user_message', "<div class=\"error_msg\">" . 
					kohana::lang('global.operation_not_allowed') . "</div>" );
				url::redirect( 'boardmessage/index' );
			}	
			
			$c = BoardMessage_Model::factory( $message -> category );
			if ( ! $c -> is_commandallowed( 'report' ) )
			{
				Session::set_flash('user_message', "<div class=\"error_msg\">" . 
					kohana::lang('global.operation_not_allowed') . "</div>" );
				url::redirect( 'boardmessage/index/' . $message -> category );
			}
			$form = $c -> get_form('report');
			
			$params[0] = $currentposition;
			$params[1] = $character;
			$params[2] = $this -> input -> post();
			$params[3] = $message;
		
			$rc = $c -> report( $params, $m );
		
			if ( $rc )
			{				
				Session::set_flash('user_message', "<div class=\"info_msg\">" . $m . "</div>" );
				url::redirect( 'boardmessage/index/' . $message -> category );
			}
			else
			{			
				$form = arr::overwrite($form, $params[2]);				
				Session::set_flash('user_message', "<div class=\"error_msg\">" . $m . "</div>" );
			}				
		}
		
		$view = $c -> get_view('report');		
		$view -> form = $form;
		$this -> template -> sheets = $sheets;
		$this -> template -> content = $view; 
		
	}
	
	/**
	* Visualizza il messaggio	
	* @param message_id id messaggio
	* @return none
	*/
	
	function view( $message_id )
	{
		
		$message = ORM::factory('boardmessage', $message_id );
		$character = Character_Model::get_info( Session::instance()->get('char_id') );
		$currentposition = ORM::factory('region', $character -> position_id );
		$c = BoardMessage_Model::factory( $message -> category );
		$view = $c -> get_view( 'view' );
		$sheets  = array('gamelayout'=>'screen', 'submenu'=>'screen');
		$auth = Auth::instance();			
		
		if ( ! $message -> loaded )
		{
			Session::set_flash('user_message', "<div class=\"error_msg\">" . 
				kohana::lang('global.operation_not_allowed') . "</div>" );
			url::redirect( 'boardmessage/index' );
		}
		
		
		
		if ( ! $c -> is_commandallowed( 'view' ) )
		{
			Session::set_flash('user_message', "<div class=\"error_msg\">" . 
				kohana::lang('global.operation_not_allowed') . "</div>" );
			url::redirect( 'boardmessage/index/' . $message -> category );
		}
			
		$view = $c -> get_view('view');		
		
		$params[0] = $currentposition;
		$params[1] = $character;
		$params[2] = $message;
		$params[3] = $auth;
		
		$rc = $c -> view( $params, $m );
		
		if ( $rc )
		{				
			;		
		}
		else
		{			
			Session::set_flash('user_message', "<div class=\"error_msg\">" . $m . "</div>" );
			url::redirect('boardmessage/index/' . $category );
		} 
		
		$view -> auth = $auth;
		$view -> reader = $character; 
		$view -> message = $message;
		$view -> currentposition = $currentposition;
		$view -> poster = ORM::factory('character', $message -> character_id );
		$this -> template -> sheets = $sheets;
		$this -> template -> content = $view; 
		$view -> category = $message -> category;	
			
	}
	
}
