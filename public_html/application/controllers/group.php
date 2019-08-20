<?php defined('SYSPATH') OR die('No direct access allowed.');

class Group_Controller extends Template_Controller
{
	public $template = 'template/gamelayout';
	const GROUPMEMBER_LIMIT = 50;
	const GROUPMEMBER_LIMIT_KING = 200;

	/**
	* Visualizza tutti i gruppi del gioco
	* @param none
	* @return none
	*/
	
	function listall()
	{
		
		$char = Character_Model::get_info( Session::instance()->get('char_id') );
		$limit = 20;
		
		// Prelevo tutti i gruppi del gioco
		$db = Database::instance();
		$sql = "select g.*, c.id char_id, c.name char_name
		        from groups g, characters c
		        where secret = 0
						and g.character_id = c.id" ;
		
		// criteria: tipo
		if ( $this -> input -> get('type') )
			$sql .= " and g.type = 'groups." . $this -> input -> get('type') . "'" ;
		
		// criteria: nome
		if ( $this -> input -> get('name') )
			$sql .= " and g.name like '%" . $this -> input -> get('name') . "%'"; 
	
		// ordinamento
		if ( $this -> input -> get('orderby') )
		{
			list($orderby, $direction) = explode(':', $this->input->get('orderby'));
			$sql .= " order by $orderby $direction " ;		
		}
		
		$groups = $db -> query( $sql );
		
		$this->pagination = new Pagination(array(
				'base_url'=>'group/listall',
				'uri_segment'=>'group',
				'style'=>'extended',
				'query_string' => 'page',
				'total_items'=> $groups -> count(),
				'items_per_page'=> $limit ));	
		
		$sql .= " limit $limit offset " . $this -> pagination -> sql_offset ;
		
		$groups = $db -> query( $sql );
		
		$view    = new View ('group/listall');
		$subm    = new View ('template/submenu');
		$sheets  = array('gamelayout' => 'screen', 'character'=>'screen', 'pagination'=>'screen', 'submenu'=>'screen');		
		
		$submenu = new View("character/submenu");
		$submenu -> action = 'mygroups';		
		$view -> groups = $groups;
		$view -> secondarymenu = Group_Model::get_groupmenu( 'listall' );		
		$view -> submenu = $submenu;
		$view -> pagination = $this->pagination; 
		$this -> template->content = $view;
		$this -> template->sheets = $sheets;
	}
	
	
	/**
	* Visualizza i gruppi gestiti dal char
	* @param: none
	* @return:none
	*/	

	function mygroups()
	{
		$char = Character_Model::get_info( Session::instance()->get('char_id') );
		$view    = new View ('group/mygroups');
		$sheets  = array('gamelayout' => 'screen', 'character'=>'screen', 'pagination'=>'screen', 'submenu'=>'screen');		
		
		$view -> secondarymenu = Group_Model::get_groupmenu( 'mygroups' );		
		$submenu = new View("character/submenu");
		$submenu -> action = 'mygroups';
		$view -> submenu = $submenu;
		$view->character = $char;
		$view -> groups = Character_Model::get_info( Session::instance() -> get('char_id') ) -> get_my_groups();		
		$this->template->content = $view;
		$this->template->sheets = $sheets;
	}

	/** 
	* Crea un gruppo
	* @param none
	* @return none
	*/
	
	function create()
	{
		
		$view = new View ('group/create');
		$subm    = new View ('template/submenu');
		$sheets  = array('gamelayout'=>'screen', 'submenu'=>'screen', 'character'=>'screen');
		
		$form = array (
		'group_name' => '',
		'group_description' => '',
		'type' => 'military',
		'secret' => 0 );
		
		$char = Character_Model::get_info( Session::instance()->get('char_id') );
		
		// Controllo che il char non abbia giÃ  3 gruppi (max consentito)
		
		$totgroups = ORM::factory('group')->where( array( 'character_id' => Session::instance()->get('char_id') ));				
		
		if ($totgroups -> count_all() == 3)
		{
			Session::set_flash('user_message', "<div class=\"error_msg\">". kohana::lang('groups.error-no_more_groups') . "</div>");
			url::redirect( '/group/mygroups/' );				
		}		
		
		$errors = $form;

		// Inizializzo la combo per il tipo gruppo
		
		$view -> combo_type = array(
			'mercenary' => Kohana::lang('groups.mercenary'), 
			'military' => Kohana::lang('groups.military'), 
			'other' => Kohana::lang('groups.other')
		);
		
		// Inizializzo la combo per il campo secret
		
		$view -> combo_secret = array('1'=>Kohana::lang('global.yes'), '0'=>Kohana::lang('global.no'));
		
		if ( $_POST)
		{
			$post = new Validation($_POST);			
			$post->pre_filter('trim', 'group_name', 'group_description');
			$post->add_rules('group_name','required', 'length[5,60]');
			$post->add_rules('group_description','required', 'length[3,255]');

			$post->add_callbacks('group_name', array($this, '_groupnameisunique'));

			if ($post->validate() )
			{
				$group = ORM::factory('group');
				$group -> character_id = Session::instance()->get('char_id');
				$group -> name = str_replace( ';', ' ', $post['group_name'] );
				$group -> description = $post['group_description'];
				$group -> type = 'groups.'.$post['type'];
				$group -> classification = $post['type']; 
				$group -> secret = $post['secret'];
				$group -> date = time();
				$group -> save();
				
				Session::set_flash('user_message', "<div class=\"info_msg\">". kohana::lang('groups.info-groupcreated') . "</div>");
				url::redirect('/group/mygroups');
			}
			else
			{
				// Traduco gli errori con gli errori custom internazionalizzati
				$errors = $post->errors('form_errors');                             
				$view->bind('errors', $errors);
				$form = arr::overwrite($form, $post->as_array());
			}
		}
		$view -> secondarymenu = Group_Model::get_groupmenu( 'mygroups' );
		$lnkmenu = $char -> get_details_submenu( 'group_create' ); 		
		$subm->submenu = $lnkmenu;		
		$view->submenu = $subm;
		$view->bind('form', $form);
		$this->template->content = $view;
		$this->template->sheets = $sheets;
	
	}


	// Funzione: Callback di validazione per verificare se il nome è giÃ  stato preso
	
	public function _groupnameisunique(Validation $array, $field)
	{
		$group = ORM::factory('group')->where( array( 'name' => ucwords($array['group_name'])))->find();
		if ( $group->loaded ) $array->add_error($field, 'groupname_exists'); 
	}
	
	/* 
	* Consente di caricare una coat of arms
	* Consente di associare una immagine/stemma al gruppo
	* @param int $group_id ID Gruppo 
	*/
	
	function upload_image ($group_id)
	{
	
		$view = new View( 'group/upload_image' );
		$sheets = array(
			'gamelayout' => 'screen', 
			'character'=>'screen', 
			'pagination'=>'screen', 
			'submenu'=>'screen');	
		
		$character = Character_Model::get_info( Session::instance()->get('char_id') );
		$group = ORM::factory('group', $group_id);
		
		// Controllo che il char sia il proprietario del gruppo
		if ( !($group->character_id == $character->id) )
		{
			Session::set_flash('user_message', "<div class=\"error_msg\">". kohana::lang('groups.error-char_not_owner') . "</div>");
			url::redirect( 'group/mygroups/' );			
		}
		
		$form = array ('group_image' => '');		
		$errors = $form;
		
		if ( ! $_FILES )		
			$view->form = $form;			
		else
		{		
			$files = Validation::factory($_FILES)
				->add_rules(
					'group_image', 
					'upload::valid', 
					'upload::required', 
					'upload::type[png]', 
					'upload::size[300K]');				
			
			if ($files->validate())
			{			
				// Temporary file name
				$filename = upload::save('group_image');
				// Resize, sharpen, and save the image
				Image::factory($filename)
					-> resize(100, 100, Image::NONE )
					-> save(DOCROOT.'media/images/groups/'. $group_id . '.png');
 				// Remove the temporary file
				unlink($filename);
				
				Session::set_flash('user_message', "<div class=\"info_msg\">". kohana::lang('groups.image_changed') . "</div>");
				url::redirect( '/group/mygroups' );	
			}
			else
			{
				// Traduco gli errori con gli errori custom internazionalizzati
				$errors = $files->errors('form_errors'); 
				$view->bind('errors', $errors);
				$form = arr::overwrite($form, $files->as_array());
			}
		}
		
		$submenu = new View("character/submenu");
		$submenu -> action = 'mygroups';		
		$view -> secondarymenu = Group_Model::get_groupmenu( 'upload_image' );
		$view -> group = $group;
		$view->submenu = $submenu;
		$this->template->content = $view;
		$this->template->sheets = $sheets;		
	}


	/**
	* Visualizza le informazioni di un gruppo
	* @param int group_id ID del gruppo
	* @return none
	*/
	
	function view( $group_id )
	{
		
		$limit = 15;
		$view = new View( 'group/view' );
		$subm  = new View ('template/submenu');
		$sheets = array('gamelayout' => 'screen', 'character'=>'screen', 'pagination'=>'screen', 'submenu'=>'screen');		
		$group = ORM::factory('group', $group_id);
		$character = Character_Model::get_info( Session::instance()->get('char_id') );
		$istutor = Character_Model::has_merole($character, 'newborntutor');
		
		// Controllo se esiste il gruppo richiesto
		
		if (! $group->loaded)
		{
			Session::set_flash('user_message', "<div class=\"error_msg\">". 
				kohana::lang('groups.error-group_not_found') . "</div>");
			url::redirect( 'group/mygroups/' );
		}
		
		$form = array ('group_charname' => '');		
		$errors = $form;
			
		// Se il gruppo è segreto 
		if ( $group -> secret ) 
		{
			// Le informazioni del gruppo possono essere visualizzate 
			// solo dal proprietario o da uno dei membri
						
			if ( ! $group->search_a_member($character->id) )
			{
			Session::set_flash('user_message', "<div class=\"error_msg\">". kohana::lang('groups.error-secret_char_not_owner') . "</div>");
			url::redirect( 'group/mygroups/' );		
			}
		}

		if ( $_POST)
		{
			// Validazione del campo membro (campo richiesto e nome esistente)
			$post = Validation::factory($_POST)->add_callbacks('group_charname', array($this, '_checkrecipient'));
			
			if ($post->validate() )
			{
				// Se supero la validazione:
				// Estraggo le informazioni del giocatore
				
				$char_to_add = ORM::factory('character')->where( array('name' => $this->input->post('group_charname')))->find();
				// Controllo che il char non abbia giÃ  una richiesta pendente per questo gruppo
				if ( Group_model::check_pendent_request($group_id, $char_to_add->id ) )
				{
					Session::set_flash('user_message', "<div class=\"error_msg\">". kohana::lang('groups.error-char_has_request') . "</div>");
					url::redirect( 'group/view/'.$group_id );
				}
				// Controllo che il giocatore non appartenga giÃ  al gruppo
				if ( $group->search_a_member($char_to_add->id) )
				{
					Session::set_flash('user_message', "<div class=\"error_msg\">". kohana::lang('groups.error-char_already_in_group') . "</div>");
					url::redirect( 'group/view/'.$group_id );
				}
				// Controllo che il gruppo non abbia giÃ  raggiunto il massimo dei giocatori
				
				$members = Group_model::get_all_members("all", $group_id);
				$currentrole = $character -> get_current_role();
				
				if (!is_null($currentrole) and $currentrole -> tag == 'king')
					$grouplimit = self::GROUPMEMBER_LIMIT_KING;
				else
					$grouplimit = self::GROUPMEMBER_LIMIT;				
				
				if ( $members -> count() >= $grouplimit )
				{
					Session::set_flash('user_message', "<div class=\"error_msg\">". kohana::lang('groups.error-reached_max_chars') . "</div>");
					url::redirect( 'group/view/'.$group_id );
				}
				
				// Controllo che la richiesta di aggiunta provenga solo dal gestore del gruppo
				
				if ($group->character_id != $character->id)
				{
					Session::set_flash('user_message', "<div class=\"error_msg\">". kohana::lang('groups.error-char_not_owner') . "</div>");
					url::redirect( 'group/view/'.$group_id );
				}

				// SUPERO TUTTI I CHECKS
				
				// Aggiungo il giocatore come pendente nella lista
				
				Group_model::add_member($group_id, $char_to_add->id);
				
				// Invio la richiesta di adesione al giocatore tramite evento
				Character_Event_Model::addrecord( 
					$char_to_add -> id, 
					'normal', 
					'__events.char_invite_join_group'.
					';' . $character -> name.
					';' . $group -> name .
					';' . url::base(true) . 'event/accept_invite/' . $group_id,						
					'normal'
					);
			}
			else
			{      
				// Traduco gli errori con gli errori custom internazionalizzati
				$errors = $post->errors('form_errors');                             
				$view->bind('errors', $errors);
				$form = arr::overwrite($form, $post->as_array());
				
			}
		}
		
		$members = ORM::factory('group_character')
			-> where( 
				array( 
					'group_id' => $group_id, 
					'joined' => true) )->find_all();
		
		$this -> pagination = new Pagination(array(
			'base_url'=>'group/view/' . $group_id,
			'uri_segment'=> $group_id,						
			'total_items' => $members -> count(),
			'items_per_page' => $limit));		
			
		$members = ORM::factory('group_character')
			-> where( 
				array( 
					'group_id' => $group_id, 
					'joined' => true) )
						-> find_all($limit, $this->pagination->sql_offset);	
		
		$view -> secondarymenu = Group_Model::get_groupmenu( 'mygroups' );
		
		$submenu = new View("character/submenu");
		$submenu -> action = 'mygroups';
		$view -> submenu = $submenu;		
		$view -> members =  $members;
		$view -> pagination = $this -> pagination;
		$view -> pendents = $group -> get_all_members("pendent", $group_id);
		$view -> group = $group;
		$view -> character = $character;
		$view -> bind('form', $form);
		$view -> istutor = $istutor;
		$this -> template -> content = $view;
		$this -> template -> sheets = $sheets;
	}


	// Callback: verifica che l' utente abbia precisato il recipient e se esiste nel gioco
	// @Input  Validation  $array   oggetto Validation
	// @Input  string      $field   nome del campo che deve essere validato	 
	
	public function _checkrecipient( Validation $array, $field)
	{
		if ( empty($array[$field]))
		{
			$array->add_error($field, 'required');
			return false;
		}
		 
		$char = ORM::factory('character')->where( array('name' => $array[$field]))->find();
		if ( !$char->loaded ) 
		{
			$array->add_error( $field, 'char_not_exist'); 
			return false;
		}

		return true;
	}

	/**
	* Rimuove un membro dalgruppo
	* @param group_id id gruppo
	* @param charid id del char da rimuovere
	* @return none
	*/
	
	function remove( $group_id, $char_id )
	{
		$character = Character_Model::get_info( Session::instance() -> get('char_id') );
		$group = ORM::factory('group', $group_id);
		$char_to_remove = ORM::factory('character', $char_id);
		
		// Controllo che il giocatore appartenga effettivamente al gruppo		
		if ( ! $group -> search_a_member( $char_to_remove->id ) )
		{
			Session::set_flash('user_message', "<div class=\"error_msg\">". kohana::lang('groups.error-char_not_in_group') . "</div>");
			url::redirect( 'group/view/'.$group_id );
		}		
		
		// Controllo che il char sia il proprietario del gruppo
		if ( $group -> character_id != $character -> id)
		{
			Session::set_flash('user_message', "<div class=\"error_msg\">". kohana::lang('groups.error-char_not_owner') . "</div>");
			url::redirect( 'group/mygroups/' );			
		}
		
		Group_Model::remove_a_member($group_id, $char_id);
		
		// Eventi
		Character_Event_Model::addrecord ( 		
			$char_to_remove -> id, 
			'normal', 
			'__events.charremovedfromgroupremoved'.
			';' . $character -> name .
			';' . $group -> name,
			'normal'
			);
		
		Character_Event_Model::addrecord ( 		
			$character -> id,
			'normal', 
			'__events.charremovedfromgroupleader'.
			';' . $char_to_remove -> name .
			';' . $group -> name,
			'normal'
			);
	
		Session::set_flash('user_message', "<div class=\"info_msg\">". kohana::lang('groups.info-memberremoved') . "</div>");
			
		url::redirect( 'group/view/'.$group_id );
	}
	
	
	/**
	* Modifica le informazioni del gruppo
	* @param int $group_id: ID del gruppo
	* @return none
	*/
	
	function edit( $group_id = null )
	{
		
		$char = Character_Model::get_info( Session::instance()->get('char_id') );		
		$view = new View( 'group/edit' );
		$sheets  = array('gamelayout' => 'screen', 'character'=>'screen', 'pagination'=>'screen', 'submenu'=>'screen');		
		$form = array (
				'group_description' => '',
				'group_name' => '' );	
		
		
		if ( ! $_POST )
		{
			$group = ORM::factory('group', $group_id);
			
			// Controllo che il char sia il proprietario del gruppo			
		
			if ( $group -> character_id != $char->id )
			{
				Session::set_flash('user_message', "<div class=\"error_msg\">". kohana::lang('groups.error-char_not_owner') . "</div>");
				url::redirect( 'group/mygroups/' );			
			}
			
			$form['group_description'] = $group -> description;
			$form['group_name'] = $group -> name;
			
			
		}
		else
		{
			//var_dump( $_POST ); exit; 
			
			$group = ORM::factory('group', $this -> input -> post( 'group_id' )); 
			
			if ( strlen( $this -> input -> post('group_name' )) < 5 )
			{
				Session::set_flash('user_message', "<div class=\"error_msg\">". kohana::lang('groups.error-groupnametooshort') . "</div>");
				$form = arr::overwrite( $form, $this -> input -> post() );				
				url::redirect( 'group/edit/' . $this -> input -> post( 'group_id' ));
			}
			
			if ( strlen( $this -> input -> post('group_description' ) ) < 15 )
			{
				Session::set_flash('user_message', "<div class=\"error_msg\">". kohana::lang('groups.error-groupdescriptiontooshort') . "</div>");
				$form = arr::overwrite( $form, $this -> input -> post() );				
				url::redirect( 'group/edit/' . $this -> input -> post( 'group_id' ));
			}
			
			// controlliamo che il gruppo non esista giÃ .
			$egroup = ORM::factory('group') -> where ( 'name', $this -> input -> post('group_name') ) -> find();
			if ( $egroup -> loaded and $egroup -> id != $group -> id )
			{
				Session::set_flash('user_message', "<div class=\"error_msg\">". kohana::lang('groups.error-groupalreadyexisting',
					$this -> input -> post('group_name' ) ) . "</div>");
				$form = arr::overwrite( $form, $this -> input -> post() );				
				url::redirect( 'group/edit/' . $this -> input -> post( 'group_id' ));
			}
			
			$group -> name = $this -> input -> post('group_name' );
			$group -> description = $this -> input -> post('group_description');
			$group -> save();
			
			Session::set_flash('user_message', "<div class=\"info_msg\">". kohana::lang('groups.info-groupmodified') . "</div>");			
			
			url::redirect('/group/mygroups');
			
		}
		
		$view -> secondarymenu = Group_Model::get_groupmenu( 'mygroups' );
		$view -> form = $form;
		$submenu = new View("character/submenu");
		$submenu -> action = 'mygroups';		
		$view -> group = $group;
		$view -> submenu = $submenu;
		$this -> template->content = $view;
		$this -> template->sheets = $sheets;
	}
	

	/*
	* Cancella un gruppo
	* @param int $group_id ID Gruppo
	* @return none
	*/
	
	function delete ($group_id)
	{
		
		$owner = Character_Model::get_info( Session::instance()->get('char_id') );
		$group = ORM::factory('group', $group_id);
		
		// Controllo che il char sia il proprietario del gruppo
		if ($group->character_id != $owner->id)
		{
			Session::set_flash('user_message', "<div class=\"error_msg\">". kohana::lang('groups.error-char_not_owner') . "</div>");
			url::redirect( 'group/mygroups/' );			
		}
		
		// Elimino tutti gli utenti del gruppo
		
		$members = $group -> get_all_members("all", $group_id);
		
		foreach ( $members as $member )
		{
			// Se l'utente non è pendente invio una notifica
			if ($member->joined)
			{
				
				Character_Event_Model::addrecord( 
				$member->character_id, 
				'normal', 
				'__events.cancelling_group'.
				';'.$owner->name.
				';'.$group->name,
				'normal'
				);
			}		
			$member->delete();
		}
		
		// Elimino l'immagine del gruppo
		
		$file = "/media/images/groups/" . $group -> id . ".png";
		if ( file_exists( $file) )
			unlink ($file);

		// Elimino il gruppo dalla tabella
		$group -> delete();
		Session::set_flash('user_message', "<div class=\"info_msg\">". kohana::lang('groups.info-groupdeleted') . "</div>");
			
		url::redirect( 'group/mygroups/' );	
		
	}
	
	
	/**
	* Manda un messaggio massivo a tutto il gruppo
	* @param int $group_id ID gruppo
	* @return none
	*/
	
	function message( $group_id )
	{	
	
		$char  = Character_Model::get_info( Session::instance()->get('char_id') );
		$group = ORM::factory('group', $group_id);
		
		$view = new View( 'group/message' );
		$sheets = array('gamelayout' => 'screen', 'character'=>'screen', 'pagination'=>'screen', 'submenu'=>'screen');	

		$form = array (
			'group_subject' => '',
			'group_message' => ''
		);		
		
		$errors = $form;

		if ( ! $_POST )		
			$view->form = $form;			
		else
		{
			
			$post = new Validation($_POST);
			
			$post -> pre_filter('trim', 'group_message');
			$post -> add_rules('group_subject','required', 'length[1, 255]');
			$post -> add_rules('group_message','required', 'length[1, 12288]');

			if ($post -> validate() )
			{
				
				$members = $group -> get_all_members("joined", $group_id);
				
				foreach ( $members as $member )
				{
					$m = new Message_Model();
					$sender = $char;
					$recipient = ORM::factory('character', $member->character_id);
					$subject = '[' . $group -> name . ']:' . $this->input->post('group_subject');
					$body = $this->input->post('group_message') .
						"\r\n" . $sender -> signature;
					$ret = $m -> send( $sender, $recipient, $subject, $body, false, false, false );
				}
				
				// Spedisco il messaggio anche al proprietario del gruppo
				
				$m = new Message_Model();
				$sender = $char;
				$recipient = ORM::factory('character', $group -> character_id);
				$subject = '[' . $group -> name . ']:' . $this->input->post('group_subject');
				$body = $this -> input -> post('group_message') . "\r\n" . $sender -> signature;
				$ret = $m -> send( $sender, $recipient, $subject, $body, false, false, false );
				
				Session::set_flash('user_message', "<div class=\"info_msg\">".Kohana::lang('message.message_success')."</div>" );				
				url::redirect('/group/mygroups');
			}
			else
			{
				// Traduco gli errori con gli errori custom internazionalizzati
				
				$errors = $post->errors('form_errors');                             
				$view->bind('errors', $errors);
				$form = arr::overwrite($form, $post->as_array());
				$view->form = $form;
				
			}
		}

		$view -> secondarymenu = Group_Model::get_groupmenu( 'mygroups' );
		$submenu = new View("character/submenu");
		$submenu -> action = 'mygroups';		
		$view->submenu = $submenu;
		$view->group = $group;
		$view->bind('form', $form);
		$this->template->content = $view;
		$this->template->sheets = $sheets;	
	
	}
	
	/** 
	* Trasferisce la leadership
	* @param int $group_id id gruppo
	* @return none
	*/
	
	function transfer_leadership ( $group_id = null)
	{
	
		$char  = Character_Model::get_info( Session::instance()->get('char_id') );
		$view = new View( 'group/transfer_leadership' );
		$sheets  = array('gamelayout' => 'screen', 'character'=>'screen', 'pagination'=>'screen', 'submenu'=>'screen');
		$form = array ('group_charname' => '');		
		$errors = $form;

		// carica gruppo
		if ( !$_POST )
			$group = ORM::factory('group', $group_id );		
		if ( $_POST )
			$group = ORM::factory('group', $this -> input -> post('group_id') );		
		
		/////////////////////////////////////////
		// il gruppo esiste?		
		/////////////////////////////////////////
		
		if ( !$group -> loaded )
		{
			Session::set_flash('user_message', "<div class=\"error_msg\">". kohana::lang('groups.error-group_not_found') . "</div>");
			url::redirect('/group/mygroups');
		}
		
		/////////////////////////////////////////
		// il char è il capo del gruppo?				
		/////////////////////////////////////////
		
		if ( $group -> character_id != $char -> id )
		{
			Session::set_flash('user_message', "<div class=\"error_msg\">". kohana::lang('groups.error-secret_char_not_owner') . "</div>");
			url::redirect('/group/mygroups');
		}
		
		
		if ( !$_POST ) 
			$view -> form = $form;
		else
		{
		
			$post = new Validation($_POST);			
			$post->pre_filter('trim', 'group_char');
			$post->add_rules('group_charname','required', 'length[1,50]');
			
			if ($post->validate() )
			{
				
				$newleader = ORM::factory('character') -> where ( array( 'name' => $this ->input -> post('group_charname') ) ) -> find();
								
				/////////////////////////////////////////
				// il char designato esiste?
				/////////////////////////////////////////
				
				if ( !$newleader -> loaded )
				{
					Session::set_flash('user_message', "<div class=\"error_msg\">". kohana::lang('groups.error-secret_char_not_owner') . "</div>");
					url::redirect('/group/mygroups');
				}
				
				
				///////////////////////////////////////////////////
				// togli il nuovo leader dal gruppo
				///////////////////////////////////////////////////
				
				$group -> remove_a_member( $group -> id, $newleader -> id ); 
							
				///////////////////////////////////////////////////
				// aggiungi il vecchio leader al gruppo
				///////////////////////////////////////////////////
				
				$group -> add_member( $group_id, $char -> id, true );
				
				$group -> character_id = $newleader -> id ;
				$group -> save();
				
				$members = $group -> get_all_members("joined", $group_id);				
				
				foreach ( $members as $member )
				{
										
					$m = new Message_Model();
					$sender = $char;
					$recipient = ORM::factory('character', $member -> character_id);
					$subject = kohana::lang('groups.massive_message_subject', $group -> name);
					$body = kohana::lang('groups.transfered_leadership', $group -> name, $newleader -> name ); 
					$ret = $m -> send( $sender, $recipient, $subject, $body );
				}
				
				// eventi
				
				Character_Event_Model::addrecord( $char -> id, 
					'normal',
					'__events.groupleadershiptransferedoldleader;' . $group -> name . ';' . $newleader -> name					
				);
				
				Character_Event_Model::addrecord( $newleader -> id, 
					'normal',
					'__events.groupleadershiptransferednewleader;' . $char -> name . ';' . $group -> name		
				);
				
				Session::set_flash('user_message', "<div class=\"info_msg\">".Kohana::lang('groups.transfered_leadership'
				, $group -> name, $newleader ->name )."</div>" );				
				url::redirect('/group/mygroups');
			}
			else
			{
				// Traduco gli errori con gli errori custom internazionalizzati
				$errors = $post->errors('form_errors');                             
				$view->bind('errors', $errors);
				$form = arr::overwrite($form, $post->as_array());
				$view->form = $form;
			}
		
		}
		$view -> secondarymenu = Group_Model::get_groupmenu( 'mygroups' );
		$submenu = new View("character/submenu");
		$submenu -> action = 'mygroups';		
		$view->submenu = $submenu;
		$view->group = $group;
		$view->bind('form', $form);
		$this->template->content = $view;
		$this->template->sheets = $sheets;	
	
	}
	
	/**
	* leave a group
	*/
	
	function leave( $group_id )
	{
	
		$char = Character_Model::get_info( Session::instance()->get('char_id') );
		$group = ORM::factory('group', $group_id );		
		
		/////////////////////////////////////////
		// il gruppo esiste?		
		/////////////////////////////////////////
		
		if ( !$group -> loaded )
		{
			Session::set_flash('user_message', "<div class=\"error_msg\">". kohana::lang('groups.error-group_not_found') . "</div>");
			url::redirect('/group/mygroups');
		}
		
		/////////////////////////////////////////
		// il char è il capo del gruppo?				
		/////////////////////////////////////////
		
		if ( $group -> character_id == $char -> id )
		{
			Session::set_flash('user_message', "<div class=\"error_msg\">". kohana::lang('groups.error-charisowner') . "</div>");
			url::redirect('/group/mygroups');
		}
		
		/////////////////////////////////////////
		// il char appartiene al gruppo?
		/////////////////////////////////////////
		if ( ! $group -> search_a_member( $char -> id ) )		
		{
			Session::set_flash('user_message', "<div class=\"error_msg\">". kohana::lang('groups.error-notamembergroup') . "</div>");
			url::redirect('/group/mygroups');
		}
				
		// evento al leader
		$m = new Message_Model();
		$sender = $group -> character;
		$recipient = $group -> character;
		$subject = kohana::lang('groups.massive_message_subject', $group -> name);
		$body = kohana::lang('groups.charleftgroup', $char -> name ); 
		$ret = $m -> send( $sender, $recipient, $subject, $body );
		
		// rimuovi dal gruppo		
		$group -> remove_a_member( $group -> id, $char -> id ); 
		
		
		Session::set_flash('user_message', "<div class=\"info_msg\">".Kohana::lang('groups.leftgroup_success')."</div>" );				
		url::redirect('/group/mygroups');
	
	}
	
}
