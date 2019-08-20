<?php defined('SYSPATH') OR die('No direct access allowed.');

class Royalpalace_Controller extends Template_Controller
{
	// Imposto il nome del template da usare
	public $template = 'template/gamelayout';


	/*
	* Gestione delle tasse legate alla struttura
	*/

	public function taxes( $structure_id = null )
	{

		$sheets  = array('gamelayout'=>'screen', 'submenu'=>'screen');
		$view = new View( 'royalpalace/taxes');
		$character = Character_Model::get_info( Session::instance()->get('char_id') );
		$this -> template -> sheets = $sheets;

		if ( ! $_POST )
		{
			$structure = StructureFactory_Model::create( null, $structure_id );
			//controllo accesso

			if ( ! $structure->allowedaccess( $character, $structure -> getParentType(), $message,
				'private', 'taxes') )
			{
				Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
				url::redirect('region/view/');
			}
		}
		else
		{
			$structure = StructureFactory_Model::create( null, $this -> input -> post('structure_id') );


			if ( ! $structure->allowedaccess( $character, $structure -> getParentType(), $message,
				'private', 'taxes') )
			{
				Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
				url::redirect('region/view/');
			}

			if ( $this -> input -> post('distributiontax') < 0 or $this -> input -> post('distributiontax') > 100 )
			{
				Session::set_flash('user_message', "<div class=\"error_msg\">" .
					Kohana::lang('taxes.error-taxvaluesnotcorrect') . "</div>");
				url::redirect('royalpalace/taxes/' . $structure -> id );
			}

			$tax = $structure -> region -> kingdom -> get_tax( 'distributiontax', $structure -> region -> kingdom_id );
			$tax -> citizen = $this -> input -> post('distributiontax');
			$tax -> save();

			Session::set_flash('user_message', "<div class=\"info_msg\">". Kohana::lang('taxes.info-taxesupdated') . "</div>");

		}

		$view -> distributiontax = $structure -> region -> kingdom -> get_tax( 'distributiontax',
			$structure -> region -> kingdom_id );
		$submenu = new View( 'structure/' . $structure -> getSubmenu() );
		$submenu -> id = $structure -> id;
		$submenu -> action = 'viewlaws';
		$view -> submenu = $submenu;
		$view -> structure = $structure ;
		$this -> template -> content = $view;
	}

	/**
	* Visualizza una lista di possibili candidati alla nomina a Vassallo.
	* @param int $structure_id ID Strutturs
	*/

	function assign_roles( $structure_id = null )
	{

		$limit = 25 ;// numero record per pagina

		$view = new View ( 'royalpalace/assign_roles' );
		$sheets  = array('gamelayout'=>'screen', 'submenu'=>'screen');
		$structure = StructureFactory_Model::create( null, $structure_id );
		$character = Character_Model::get_info( Session::instance()->get('char_id') );

		// controllo permessi

		if ( ! $structure -> allowedaccess( $character, $structure -> getParentType(), $message, 'private', 'assign_roles' ) )
		{
			Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
			url::redirect('region/view/');
		}
		else
		{

			// carica tutti i residenti dei nodi del regno che non hanno un incarico

			$db = Database::instance();

			$sql = "
			select c.*, r.name region
			from characters c, regions r
			where c.region_id = r.id
			and c.type != 'npc'
			and region_id in
			( select id from regions where kingdom_id = " . $character -> region -> kingdom -> id  . " )
			and c.id not in
			( select character_id from character_roles where current = 1 and gdr = false ) ";

			//kohana::log('debug', "sql = $sql");

			// li conto ( necessario per paginazione )
			$result = $db->query( $sql );
			//print kohana::debug ($result);exit;

			$this -> pagination = new Pagination(array(
			 'base_url'=>'royalpalace/assign_roles/' . $structure_id,
			 'uri_segment'=> $structure_id,
			 'total_items' => $result -> count(),
			 'items_per_page'=> $limit ));

			$sql .= " order by c.name asc
			limit $limit offset " . $this -> pagination -> sql_offset ;

			// trovo tutte le regioni che hanno un castello senza vassallo

			$regionswithfreecastle = null;

			foreach ( $character -> region -> kingdom -> regions as $region )
			{
				$castle = $region -> get_structure('castle' );

				if ( !is_null( $castle ) and is_null( $castle -> character_id ) )
				{
					$regionswithfreecastle[$region->id] = kohana::lang($region->name) ;
				}
			}

			$submenu = new View( 'structure/' . $structure -> getSubmenu() );
			$submenu -> id = $structure -> id;
			$submenu -> action = 'assign_roles';
			$view->submenu = $submenu;
			$view -> regionswithfreecastle = $regionswithfreecastle;
			$result = $db->query( $sql );
			$view->structure = $structure;
			$view->pagination = $this->pagination;
			$view->candidates = $result;

		}


		$this->template->content = $view;
		$this->template->sheets = $sheets;

	}

	/**
	* Nomina un Vassallo
	*/

	function appoint()
	{

		$structure = StructureFactory_Model::create( null, $this -> input -> post('structure_id') );
		$character = Character_Model::get_info( Session::instance()->get('char_id') );
		$db = Database::instance();

		if ( ! $structure -> allowedaccess( $character, $structure -> getParentType(), $message,
			'private', 'appoint') )
		{
			Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
			url::redirect('region/view/');
		}

		$par[0] = Character_Model::get_info( Session::instance()->get('char_id') );
		$par[1] = ORM::factory("character", $this -> input -> post( 'character_id' ) );
		$par[2] = 'vassal';
		$par[3] = ORM::factory('region', $this -> input -> post( 'region_id' ) );
		$par[4] = ORM::factory('structure', $this -> input -> post( 'structure_id' ) );

		$ca = Character_Action_Model::factory("assignrole");
		$rc = $ca -> do_action( $par, $message );

		if ( $rc )
		{
			Session::set_flash('user_message', "<div class=\"info_msg\">". $message . "</div>");
			url::redirect ( 'royalpalace/list_roles/' . $par[4] -> id );
		}
		else
		{
			Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
			url::redirect ( 'royalpalace/list_roles/' . $par[4] -> id );
		}

	}

	/**
	* Permette di revocare un ruolo
	* @param structure_id ID struttura controllante
	* @param character_id ID char che si vuole dismettere
	*/

	function revoke_role( $structure_id, $character_id )
	{

		$roleowner = ORM::factory("character", $character_id );
		$role = $roleowner -> get_current_role();
		$character = Character_Model::get_info( Session::instance()->get('char_id') );
		$ca = Character_Action_Model::factory("revokerole");
		$structure = StructureFactory_Model::create( null, $structure_id );

		if ( ! $structure -> allowedaccess( $character, $structure -> getParentType(), $message,
			'private', 'revoke_role') )
		{
			Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
			url::redirect('region/view/');
		}

		$par[0] = $character;
		$par[1] = $roleowner;
		$par[2] = $role -> tag;
		$par[3] = $structure;

		$rec = $ca->do_action( $par, $message );

		if ( $rec )
		{
			Session::set_flash('user_message', "<div class=\"info_msg\">". $message . "</div>");
			url::redirect( 'royalpalace/assign_roles/' . $structure -> id );
		}
		else
		{
			Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
			url::redirect( 'royalpalace/assign_roles/' . $structure -> id );
		}

		$view -> role = $role;
		$view -> roleowner = $roleowner;
		$this -> template->content = $view ;
		$this -> template->sheets = $sheets;
	}

	/**
	* Visualizza annuncio di benvenuto
	* @param none
	* @return none
	*/

	function showwelcomeannouncement()
	{
		$view = new View ('royalpalace/showwelcomeannouncement');
		$sheets  = array('gamelayout'=>'screen', 'submenu'=>'screen');
		$character = Character_Model::get_info( Session::instance()->get('char_id') );
		$capital = Kingdom_Model::get_capitalregion( $character -> region -> kingdom_id );
		//var_dump($capital); exit;
		$welcomeannouncement = ORM::factory('region_announcement')
			-> where ( array(
				'region_id' => $capital -> id,
				'subtype' => 'welcomemessage'))-> find();

		if ( $welcomeannouncement->loaded == false )
		{
			Session::set_flash('user_message', "<div class=\"error_msg\">". kohana::lang('structures_billboard.welcomeannouncementnotfound') . "</div>");
			url::redirect('region/view');
		}

		$view -> welcomeannouncement = $welcomeannouncement;
		$this -> template->sheets = $sheets;
		$this -> template->content = $view;

	}

	/**
	* Welcome announcement
	* @param int $structure_id id struttura
	* @return none
	*/

	function welcomeannouncement( $structure_id = null)
	{

		$view = new View ('royalpalace/welcomeannouncement');
		$sheets  = array('gamelayout'=>'screen', 'submenu'=>'screen');
		$character = Character_Model::get_info( Session::instance()->get('char_id') );
		$welcomemessage = null;
		$form = array( 'title' => '', 'welcomemessage' => '' );

		if ( !$_POST )
		{
			$structure = StructureFactory_Model::create( null, $structure_id );

			if ( ! $structure -> allowedaccess( $character, $structure -> getParentType(), $message,	'private', 'announcements') )
			{
				Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
				url::redirect('region/view/');
			}

			$welcomemessage = ORM::factory('region_announcement' ) ->
				where ( array(
					'region_id' => $structure -> region -> id,
					'type' => 'kingdom',
					'subtype' => 'welcomemessage' ) ) -> find();

			if ( !$welcomemessage -> loaded )
			{
				$title = '';
				$body = '';
			}
			else
			{
				$title = $welcomemessage -> title;
				$body =  $welcomemessage -> text;

			}
		}
		else
		{

			$structure = StructureFactory_Model::create( null, $this -> input -> post('structure_id') );

			if ( ! $structure -> allowedaccess( $character, $structure -> getParentType(), $message,	'private', 'announcements') )
			{
				Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
				url::redirect('region/view/');
			}

			$title = $this -> input -> post ( 'title' );
			$body = $this -> input -> post ( 'body' );
			if ( strlen( $title ) <= 0 or strlen( $title ) > 50 )
			{
				Session::set_flash('user_message', "<div class='error_msg'>". kohana::lang('structures_royalpalace.error-welcomemessagetitle') . "</div>");
			}
			else
			if ( strlen( $body ) <= 0 or strlen( $body ) > 8192 )
			{
				Session::set_flash('user_message', "<div class='error_msg'>". kohana::lang('structures_royalpalace.error-welcomemessagebody') . "</div>");
			}
			else
			{
				$welcomemessage = new Region_Announcement_Model();
				$welcomemessage -> region_id = $structure -> region -> id;
				$welcomemessage -> character_id = $character -> id;
				$welcomemessage -> type = 'kingdom';
				$welcomemessage -> title = $title;
				$welcomemessage -> text = $body;
				$welcomemessage -> signature = null;
				$welcomemessage -> timestamp = time();
				$welcomemessage -> subtype = 'welcomemessage';
				$welcomemessage -> save();

				Session::set_flash('user_message', "<div class='info_msg'>". kohana::lang('structures_royalpalace.info-welcomemessagesaved') . "</div>");
			}


		}
		$submenu = new View( 'structure/' . $structure -> getSubmenu() );
		$submenu -> id = $structure -> id;
		$submenu -> action = 'welcomeannouncement';
		$view->submenu = $submenu;
		$view -> title = $title;
		$view -> body = $body;
		$view -> structure = $structure;
		$this -> template -> content = $view;
		$this -> template->content = $view;
		$this -> template->sheets = $sheets;
	}

	/**
	* Add a Kingdom Slogan
	* @param int $structure_id ID structure
	* @return none
	*/

	function add_slogan( $structure_id = null)
	{

		$view = new View ('royalpalace/addslogan');
		$sheets  = array('gamelayout'=>'screen', 'submenu'=>'screen');
		$character = Character_Model::get_info( Session::instance()->get('char_id') );
		$subm    = new View ('template/submenu');
		$form = array( 'slogan' => '' );

		if ( !$_POST )
		{
			$structure = StructureFactory_Model::create( null, $structure_id );

			if ( ! $structure -> allowedaccess( $character, $structure -> getParentType(), $message,	'private', 'announcements') )
			{
				Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
				url::redirect('region/view/');
			}

			$res = Database::instance() -> query("select * from kingdoms_v where id = {$structure -> region -> kingdom_id}") -> as_array();

			$slogan = $res[0] -> slogan;

		}
		else
		{

			$structure = StructureFactory_Model::create( null, $this -> input -> post('structure_id') );

			if ( ! $structure -> allowedaccess( $character, $structure -> getParentType(), $message,	'private', 'announcements') )
			{
				Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
				url::redirect('region/view/');
			}

			$kingdom = ORM::factory('kingdom', $structure -> region -> kingdom_id);
			$slogan = $this -> input -> post('slogan');

			if ($kingdom -> loaded )
			{
				if ( strlen($slogan) > 255 )
				{
					Session::set_flash('user_message', "<div class='error_msg'>". kohana::lang('structures_royalpalace.error-slogantoolong') . "</div>");

					url::redirect('royalpalace/addslogan/' . $structure -> id);

				}

				$kingdom -> slogan = $slogan;
				$kingdom -> save();
			}

			Session::set_flash('user_message', "<div class='info_msg'>".
				kohana::lang('structures_royalpalace.info-slogansaved') . "</div>");

		}
		$submenu = new View( 'structure/' . $structure -> getSubmenu() );
		$submenu -> id = $structure -> id;
		$submenu -> action = 'welcomeannouncement';
		$view->submenu = $submenu;
		$view -> slogan = $slogan;
		$view -> structure = $structure;
		$this -> template -> content = $view;
		$this -> template -> content = $view;
		$this -> template -> sheets = $sheets;
	}

/**
	* Informazioni sul Regno
	* @param int $structure_id id struttura
	* @return none
	*/

	function infoannouncement( $structure_id = null)
	{

		$view = new View ('royalpalace/infoannouncement');
		$sheets  = array('gamelayout'=>'screen', 'submenu'=>'screen');
		$character = Character_Model::get_info( Session::instance()->get('char_id') );
		$subm    = new View ('template/submenu');
		$infomessage = null;
		$form = array( 'id' => null, 'title' => '', 'body' => '' );


		if ( !$_POST )
		{
			$structure = StructureFactory_Model::create( null, $structure_id );

			if ( ! $structure -> allowedaccess( $character, $structure -> getParentType(), $message,	'private', 'announcements') )
			{
				Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
				url::redirect('region/view/');
			}

		}
		else
		{

			$structure = StructureFactory_Model::create( null, $this -> input -> post('structure_id') );

			if ( ! $structure -> allowedaccess( $character, $structure -> getParentType(), $message,	'private', 'announcements') )
			{
				Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
				url::redirect('region/view/');
			}

			if ( $this -> input -> post('configureinformativetext'))
			{
				$title = $this -> input -> post ( 'title' );
				$body = $this -> input -> post ( 'body' );
				if ( strlen( $title ) <= 0 or strlen( $title ) > 50 )
				{
					Session::set_flash('user_message', "<div class='error_msg'>". kohana::lang('structures_royalpalace.error-infomessagetitle') . "</div>");
				}
				else
				if ( strlen( $body ) <= 0 or strlen( $body ) > 4096 )
				{
					Session::set_flash('user_message', "<div class='error_msg'>". kohana::lang('structures_royalpalace.error-infomessagebody') . "</div>");
				}
				else
				{
					$infomessage = ORM::factory('region_announcement', $this -> input -> post('id'));
					$infomessage -> region_id = $character -> region -> kingdom_id ;
					$infomessage -> character_id = $character -> id;
					$infomessage -> type = 'kingdom';
					$infomessage -> title = $title;
					$infomessage -> text = $body;
					$infomessage -> signature = Utility_Model::bbcode($character->signature);
					$infomessage -> timestamp = time();
					$infomessage -> subtype = 'infomessage';
					$infomessage -> save();



					Session::set_flash('user_message', "<div class='info_msg'>". kohana::lang('structures_royalpalace.info-infomessagesaved') . "</div>");
				}
			}

			if ( $this -> input -> post('configurelanguages'))
			{
				$language1 = $this -> input -> post('language1');
				$language2 = $this -> input -> post('language2');

				$structure -> region -> kingdom -> language1 = $language1;
				$structure -> region -> kingdom -> language2 = $language2;
				$structure -> region -> kingdom -> save();
				Session::set_flash('user_message', "<div class='info_msg'>". kohana::lang('structures_royalpalace.info-languagessaved') . "</div>");
			}
		}

		// carica linguaggi

		$languages = array(
			'Arabic' => 'Arabic',
			'Bulgarian' => 'Bulgarian',
			'Dutch' => 'Dutch',
			'English' => 'English',
			'French' => 'French',
			'German' => 'German',
			'Greek' => 'Greek',
			'Hungarian' => 'Hungarian',
			'Israelian' => 'Israelian',
			'Italian' => 'Italian',
			'Portuguese' => 'Portuguese',
			'Romanian' => 'Romanian',
			'Russian' => 'Russian',
			'Spanish' => 'Spanish',
			'Swedish' => 'Swedish',
			'Turkish' => 'Turkish',
		);

		// carica messaggio dal db

		$infomessage = ORM::factory('region_announcement') -> where
			( array(
				'region_id' => $character -> region -> kingdom_id,
				'subtype' => 'infomessage' ) ) -> find();
			if ( $infomessage -> loaded )
			{
				$form['id'] = $infomessage -> id;
				$form['title'] = $infomessage -> title;
				$form['body'] = $infomessage -> text;
			}
		$submenu = new View( 'structure/' . $structure -> getSubmenu() );
		$submenu -> id = $structure -> id;
		$submenu -> action = 'welcomeannouncement';
		$view->submenu = $submenu;
		$view -> form = $form;
		$view -> languages = $languages;
		$view -> structure = $structure;
		$this -> template -> content = $view;
		$this -> template->content = $view;
		$this -> template->sheets = $sheets;
	}

	/**
	* Elenca i vassalli del regno
	* @param int $structure_id id struttura
	* @return none
	*/

	function list_roles( $structure_id )
	{

		$view = new View ('royalpalace/list_roles');
		$sheets  = array('gamelayout'=>'screen', 'submenu'=>'screen');
		$limit = 25 ; // records per page
		$structure = StructureFactory_Model::create( null, $structure_id );
		$character = Character_Model::get_info( Session::instance()->get('char_id') );

		$subm    = new View ('template/submenu');

		// controllo permessi
		if ( ! $structure->allowedaccess( $character, $structure -> getParentType(), $message,
			'private', 'list_roles' ) )
		{
			Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
			url::redirect('region/view/');
		}

		$o = new Kingdom_Model();
		$kingdom = $o->find( $character -> region -> kingdom -> id );

		$db = Database::instance();

		// conto tutti i vassalli del regno (necessario per la paginazione)
		// non aperta ad attacchi SQl-injection perch� i parametri non sono passati via request

		$vassals = $db -> query(
		"select c.id, c.name charname, r.name regionname
		from character_roles cr, characters c, regions r
		where tag = 'vassal'
		and   cr.region_id = r.id
		and   cr.character_id = c.id
		and   cr.current = true
		and   r.kingdom_id = " . $kingdom -> id );

		$this -> pagination = new Pagination(array(
		 'base_url'=>'royalpalace/assign_roles',
		 'uri_segment'=>'assign_roles',
		 'style'=>"extended",
		 'total_items'=> $vassals -> count(),
		 'items_per_page'=>$limit));

		// carico tutti i vassalli con l' offset corretto.

		$vassals = $db -> query( "
		select c.id, c.name charname, r.name regionname, cr.begin
		from character_roles cr, characters c, regions r
		where tag = 'vassal'
		and   cr.region_id = r.id
		and   cr.character_id = c.id
		and   cr.current = true
		and   r.kingdom_id = " . $kingdom -> id .
		" order by charname asc " .
		" limit $limit offset " . $this -> pagination -> sql_offset  ) ;
		$submenu = new View( 'structure/' . $structure -> getSubmenu() );
		$submenu -> id = $structure -> id;
		$submenu -> action = 'assign_roles';
		$view->submenu = $submenu;
		$view->pagination = $this->pagination;
		$view->vassals = $vassals;
		$view->structure = $structure;
		$view->language1 = $structure -> region -> kingdom -> language1;
		$view->language2 = $structure -> region -> kingdom -> language2;
		$this->template->content = $view;
		$this->template->sheets = $sheets;

	}

	/**
	* Declare hostile actions
	* @param int $structure_id ID Struttura Palazzo Reale
	* @return none
	*/

	public function declarehostileaction( $structure_id )
	{

		$view = new View ( 'royalpalace/declarehostileaction' );
		$structure = StructureFactory_Model::create( null, $structure_id );
		$sheets  = array('gamelayout'=>'screen', 'submenu'=>'screen');
		$character = Character_Model::get_info( Session::instance()->get('char_id') );

		if ( isset($this -> disabledmodules['declarehostileaction']) )
		{
			Session::set_flash('user_message', "<div class=\"error_msg\">". kohana::lang('charactions.error-moduleisdisabled') . "</div>");	url::redirect('royalpalace/manage/' . $structure -> id );
			url::redirect('region/view/');
		}

		// controllo permessi

		if ( ! $structure -> allowedaccess( $character, $structure -> getParentType(),
			$message, 'private', 'declarehostileaction' ) )
		{
			Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
			url::redirect('region/view/');
		}

		$kingdoms = Configuration_Model::getcfg_kingdoms();
		foreach($kingdoms as $kingdomname => $kingdomdata)
			$kingdomlist[$kingdomdata -> id] = kohana::lang($kingdomname);

		$submenu = new View( 'structure/' . $structure -> getSubmenu() );
		$submenu -> id = $structure -> id;
		$submenu -> action = 'declarehostileaction';
		$view -> submenu = $submenu;
		$view -> kingdomlist = $kingdomlist;
		$view -> kingdomwars = Kingdom_Model::get_kingdomwars( $character -> region -> kingdom_id, 'running');
		$view -> structure = $structure;
		$this -> template->sheets = $sheets;
		$this -> template->content = $view;

		return;

	}

	/**
	* Declare war
	* @param none
	* @return none
	*/

	function declarewar()
	{
		$structure = ORM::factory('structure', $this -> input -> post('structure_id'));
		$character = Character_Model::get_info( Session::instance()->get('char_id') );

		if ( isset($this -> disabledmodules['declarewar']) )
		{
			Session::set_flash('user_message', "<div class=\"error_msg\">". kohana::lang('charactions.error-moduleisdisabled') . "</div>");
			url::redirect('region/view/' );
		}

		// controllo permessi

		if ( ! $structure->allowedaccess( $character, $structure -> getParentType(), $message, 'private', 'declarewar' ) )
		{
			Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
			url::redirect('region/view/');
		}

		$ca = Character_Action_Model::factory("declarewar");
		$par[0] = Character_Model::get_info( Session::instance()->get('char_id') );
		$par[1] = ORM::factory('kingdom', $this -> input -> post('kingdom'));
		$par[2] = $structure;

		if ( $ca -> do_action( $par,  $message ) )
		{
			Session::set_flash('user_message', "<div class=\"info_msg\">". $message . "</div>");
			url::redirect('royalpalace/declarehostileaction/' . $structure -> id );
		}
		else
		{
			Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
			url::redirect('royalpalace/declarehostileaction/' . $structure -> id );
		}

	}

	/**
	* Finish a war
	* @param none
	* @param none
	*/

	function finishwar()
	{

		$ca = Character_Action_Model::factory("finishwar");
		$par[0] = Character_Model::get_info( Session::instance()->get('char_id') );
		$par[1] = $this -> input -> post('war_id');

		if ( $ca -> do_action( $par,  $message ) )
		{
			Session::set_flash('user_message', "<div class=\"info_msg\">". $message . "</div>");
			url::redirect('royalpalace/declarehostileaction/' . $this -> input -> post('structure_id'));
		}
		else
		{
			Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");url::redirect('royalpalace/declarehostileaction/' . $this -> input -> post('structure_id'));
		}

	}

	/**
	* Declare hostile actions
	* @param $structure_id id structure
	* @return none
	*/

	function declarewaraction($structure_id = null)
	{
		$view = new View('royalpalace/declarewaraction');
		$sheets  = array('gamelayout'=>'screen', 'submenu'=>'screen');
		$character = Character_Model::get_info( Session::instance()->get('char_id') );
		$form = array(
			'city' => '',
			'attackowner' => '',
			'region_id' => '' );

		if ( isset($this -> disabledmodules['declarewar']) )
		{
			Session::set_flash('user_message', "<div class=\"error_msg\">". kohana::lang('charactions.error-moduleisdisabled') . "</div>");
			url::redirect('region/view/' );
		}

		if ( !$_POST )
		{

			$structure = StructureFactory_Model::create( null, $structure_id );
			// controllo permessi
			if ( ! $structure -> allowedaccess( $character, $structure -> getParentType(), 	'private', 'declarewaraction', $message ) )
			{
				Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
				url::redirect('region/view/');
			}

			$view->form = $form;
			$view->structure = $structure;
			$this->template->content = $view;
			$this->template->sheets = $sheets;
		}
		else
		{

			$post = Validation::factory($this->input->post())
				->pre_filter('trim', TRUE)
				->add_rules('city','required');

			$structure = StructureFactory_Model::create( null, $this -> input -> post('structure_id') );

			// controllo permessi
			if ( ! $structure->allowedaccess( $character, $structure -> getParentType(), 	'private', 'declarewaraction', $message ) )
			{
				Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
				url::redirect('region/view/');
			}

			if ($post->validate() )
			{
				$par[0] = $character;
				$par[1] = ORM::factory( 'region', $structure -> region -> id );
				$par[2] = $this -> input -> post( 'attacktype');
				$par[3] = ORM::factory( 'region', $this -> input -> post( 'region_id' ) );
				$ca = Character_Action_Model::factory("declarewaraction");
				if ( $ca->do_action( $par,  $message ) )
				{
					Session::set_flash('user_message', "<div class=\"info_msg\">". $message . "</div>");
					url::redirect('region/view/');
				}
				else
				{
					Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
					$view->structure = $structure;
					$form = arr::overwrite($form, $post->as_array());
					$view->form = $form;
					$this->template->content = $view;
					$this->template->sheets = $sheets;
				}
			}
			else
			{
				$view->structure = $structure;
				$errors = $post->errors('form_errors');
				$view->bind('errors', $errors);
				//ripopolo la form
				$form = arr::overwrite($form, $post->as_array());
				$view->form = $form;
				$this->template->content = $view;
				$this->template->sheets = $sheets;
			};

		}
	}

	/**
	* Funzione che Visualizza la sala del trono
	* param structure_id ID struttura
	* return none
	*/

	public function throne_room( $structure_id )
	{
		$view = new view ('royalpalace/throne_room');
		$sheets  = array('gamelayout'=>'screen', 'submenu'=>'screen');
		$char = Character_Model::get_info( Session::instance()->get('char_id') );

		// Carico la struttura "Palazzo Reale"
		$structure = StructureFactory_Model::create( null, $structure_id );

		$regionnames = array();
		foreach ($structure->region->kingdom->regions as $region)
			$regionnames[] = kohana::lang($region->name);

		// Calcolo il costo per divenire sovrano
		$cost = $structure->region->kingdom->get_regent_cost();
		$view->char = $char;
		$view->regionnames = $regionnames;
		$view->structure = $structure;
		$this->template->content = $view;
		$this->template->sheets = $sheets;

	}

	/**
	* Funzione che permette di diventare sovrano
	* param none
	* return none
	**/

	function become_king()
	{

		$view = new view ('royalpalace/throne_room');
		$sheets  = array('gamelayout'=>'screen', 'submenu'=>'screen');

		$par[0] = Character_Model::get_info( Session::instance()->get('char_id') );
		$par[1] = ORM::factory('structure', $this->input->post('structure_id'));

		$regionnames = array();
		foreach ($par[1]->region->kingdom->regions as $region)
			$regionnames[] = kohana::lang($region->name);

		$ca = Character_Action_Model::factory("becomeking");
		if ( $ca -> do_action( $par,  $message ) )
		{
			Session::set_flash('user_message', "<div class=\"info_msg\">". $message . "</div>");
			url::redirect( 'region/view' );
		}
		else
		{
			Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
		}

		$view->regionnames = $regionnames;
		$view->structure = $par[1];
		$this->template->content = $view;
		$this->template->sheets = $sheets;

	}

	/**
	* Cancelleria reale
	*/

	function manage( $structure_id = null  )
	{

		$view = new View('royalpalace/manage');
		$sheets  = array('gamelayout'=>'screen', 'submenu'=>'screen');
		$structure = StructureFactory_Model::create( null, $structure_id );
		$character = Character_Model::get_info( Session::instance()->get('char_id') );
		$subm    = new View ('template/submenu');
		$structureheader = new View('template/structureheader');

		if ( ! $structure -> allowedaccess( $character, $structure -> getParentType(), $message, 'private', 'manage') )
		{
			Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
			url::redirect('region/view/');
		}

		$lnkmenu = $structure -> get_horizontalmenu( 'manage' );

		$structureheader -> structure = $structure;
		$view -> structureheader = $structureheader;

		$subm -> submenu = $lnkmenu;
		$view -> submenu = $subm;

		$view -> structure = $structure;
		$this -> template -> content = $view;
		$this -> template -> sheets = $sheets;
	}

	/*
	* Report delle risorse contenute nel regno
	* @param int $structure_id ID struttura
	* @return none
	*/

	function resourcereport( $structure_id )
	{

		$view = new View('royalpalace/resourcereport' );
		$sheets  = array('gamelayout'=>'screen', 'submenu'=>'screen');
		$structure = StructureFactory_Model::create( null, $structure_id );
		$character = Character_Model::get_info( Session::instance()->get('char_id') );
		$subm    = new View ('template/submenu');

		// controllo permessi
		if ( ! $structure->allowedaccess( $character, $structure -> getParentType(), $message, 'private', 'resourcereport' ) )
		{
			Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
			url::redirect('region/view/');
		}

		$sql = "
		SELECT sum(i.quantity) number, c.tag itemtag, c.name itemname, st.supertype structuretype, st.name structurename, n.name city
		FROM items i,
		cfgitems c,
		structures s,
		structure_types st,
		regions n
		WHERE i.structure_id = s.id
		AND i.structure_id Is not null
		and s.structure_type_id = st.id
		AND i.cfgitem_id = c.id
		AND n.id = s.region_id
		and n.id > 0
		AND st.subtype = 'government'
		and n.kingdom_id = " . $structure -> region -> kingdom -> id . "
		GROUP BY itemname, structurename, city ";

		$db = Database::instance();
		$res = $db->query( $sql );
		// build the megaarray

		$report = array();

		$cfgitems = ORM::factory('cfgitem')->find_all();

		$report = array();

		foreach ( $cfgitems as $c)
			foreach ( $structure->region->kingdom->regions as $n )
			{

				$report[$c->tag][$n->name]['royalpalace'] = '-';
				$report[$c->tag][$n->name]['castle'] = '-';
				$report[$c->tag][$n->name]['court'] = '-';
				$report[$c->tag][$n->name]['barracks'] = '-';
				$report[$c->tag][$n->name]['academy'] = '-';
				$report[$c->tag][$n->name]['trainingground'] = '-';
				$report[$c->tag][$n->name]['watchtower'] = '-';
			}

		$i=0;

		foreach ( $res as $r )
		{
				$report[$r->itemtag][$r->city][ $r -> structuretype ] = $r->number;
		}

		$submenu = new View( 'structure/' . $structure -> getSubmenu() );
		$submenu -> id = $structure -> id;
		$submenu -> action = 'resourcereport';
		$view->submenu = $submenu;

		$view->regions = $structure->region->kingdom->regions;
		$view->cfgitems = $cfgitems;
		$view->structure = $structure;
		$view->report = $report;
		$this->template->content = $view;
		$this->template->sheets = $sheets;

	}

	/*
	* Report delle propriet� possedute
	* @param int $structure_id ID struttura
	* @return none
	*/

	function propertyreport( $structure_id )
	{

		$view = new View('royalpalace/propertyreport' );
		$sheets  = array('gamelayout'=>'screen', 'submenu'=>'screen');
		$structure = StructureFactory_Model::create( null, $structure_id );
		$character = Character_Model::get_info( Session::instance()->get('char_id') );
		$role = $character -> get_current_role();
		$subm    = new View ('template/submenu');

		// controllo permessi
		if ( ! $structure->allowedaccess( $character, $structure -> getParentType(), $message, 'private', 'propertyreport' ) )
		{
			Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
			url::redirect('region/view/');
		}

		$controlledregions = $character -> get_controlledregions();

		foreach ( $controlledregions as $region )
		{
			$sql = "
				select c.name charname, c.id character_id, n2.name residence, k.name kingdomname, st.name structurename, n1.name regionname
				from structures s, regions n1, characters c, regions n2, kingdoms_v k, structure_types st
				where s.region_id = " . $region -> id . "
				and s.region_id = n1.id
				and st.supertype not in ( 'royalpalace', 'castle', 'court', 'barracks', 'religion_1', 'religion_2', 'religion_3', 'religion_4' )
				and s.structure_type_id = st.id
				and s.character_id = c.id
				and c.region_id = n2.id
				and n2.kingdom_id = k.id
				order by c.name";

			$db = Database::instance();
			$res = $db->query( $sql ) -> as_array();
			// build the megaarray
			$report[$region->name] = $res ;
		}

		$submenu = new View( 'structure/' . $structure -> getSubmenu() );
		$submenu -> id = $structure -> id;
		$submenu -> action = 'resourcereport';
		$view->submenu = $submenu;


		$view->structure = $structure;
		$view->report = $report;
		$view->role = $role;
		$this->template->content = $view;
		$this->template->sheets = $sheets;
	}

	/***
	 * Dichiara Razzia
	 * @param int $structure_id id struttura da dove si lancia il comando
	 * @return none
	*/

	public function raid( $structure_id = null )
	{

		$view = new View ( 'royalpalace/raid' );
		$sheets  = array('gamelayout'=>'screen', 'submenu'=>'screen');
		$subm    = new View ('template/submenu');
		$form = array(
			'attackedregion' => '',
			'attackedregion_id' => null,
			'maxattackers' => 20);

		$character = Character_Model::get_info( Session::instance()->get('char_id') );

		if ( !$_POST )
		{
			$structure = StructureFactory_Model::create( null, $structure_id );


			// controllo permessi

			if ( ! $structure->allowedaccess( $character, $structure -> getParentType(), $message, 'private', 'raid' ) )
			{
				Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
				url::redirect('region/view/');
			}

		}
		else
		{

			$structure = StructureFactory_Model::create( null, $this -> input -> post('structure_id') );

			// controllo permessi

			if ( ! $structure->allowedaccess( $character, $structure -> getParentType(), $message, 'private', 'raid' ) )
			{
				Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
				url::redirect('region/view/');
			}

			$par[0] = Character_Model::get_info( Session::instance()->get('char_id') );
			$par[1] = $structure -> region;
			$par[2] = 'raid';
			$par[3] = ORM::factory( 'region') ->
								where (
									'name', strtolower('regions.' . $this->input->post( 'attackedregion' ) )
								) -> find();
			$par[4] = null;
			$par[5] = $this -> input -> post('maxattackers');
			$par[6] = $this -> input -> post('relictoraid');

			$ca = Character_Action_Model::factory("declarewaraction");

			if ( $ca->do_action( $par,  $message ) )
			{
				Session::set_flash('user_message', "<div class=\"info_msg\">". $message . "</div>");
				url::redirect('royalpalace/declarehostileaction/' . $structure -> id );
			}
			else
			{
				$form = arr::overwrite( $form, $this -> input -> post());
				Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
			}

		}

		$submenu = new View( 'structure/' . $structure -> getSubmenu() );
		$submenu -> id = $structure -> id;
		$submenu -> action = 'declarehostileaction';
		$view->submenu = $submenu;
		$view -> form = $form;

		$view -> structure = $structure;
		$this -> template -> content = $view;
		$this -> template -> sheets = $sheets;

	}


	/*
	* Conquista Regione indipendente
	* @param int $structure_id ID Struttura
	* @return none
	*/

	public function conquer_ir ( $structure_id = null )
	{
		$view = new View ( 'royalpalace/conquer_ir' );
		$sheets  = array('gamelayout'=>'screen', 'submenu'=>'screen');
		$form = array( 'captain' => '', 'independentregion' => '', 'notes' => '');
		$character = Character_Model::get_info( Session::instance()->get('char_id') );

		if ( !$_POST )
		{
			$structure = StructureFactory_Model::create( null, $structure_id );

			// controllo permessi

			if ( ! $structure->allowedaccess( $character, $structure -> getParentType(), $message, 'private', 'conquer_ir' ) )
			{
				Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
				url::redirect('region/view/');
			}
		}
		else
			{

			$structure = StructureFactory_Model::create( null, $this -> input -> post('structure_id') );

			// controllo permessi

			if ( ! $structure->allowedaccess( $character, $structure -> getParentType(), $message, 'private', 'conquer_ir' ) )
			{
				Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
				url::redirect('region/view/');
			}

			$ca = Character_Action_Model::factory("orderconquerir");
			$par[0] = Character_Model::get_info( Session::instance()->get('char_id') );
			$par[1] = $this -> input -> post('captain');
			$par[2] = $this -> input -> post('independentregion');
			$par[3] = $this -> input -> post('notes');

			if ( $ca -> do_action( $par,  $message ) )
			{
				Session::set_flash('user_message', "<div class=\"info_msg\">". $message . "</div>");
				url::redirect('royalpalace/conquer_ir/' . $structure -> id );
			}
			else
			{
				Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
				$form = arr::overwrite( $form, $this -> input -> post());

			}


		}


		$submenu = new View( 'structure/' . $structure -> getSubmenu() );
		$submenu -> id = $structure -> id;
		$submenu -> action = 'declarehostileaction';
		$view -> submenu = $submenu;
		$view -> structure = $structure;
		$view -> form = $form;
		$this -> template -> content = $view;
		$this -> template->sheets = $sheets;

	}


	/*
	* Conquista regione posseduta da un Regno
	* @param int $structure_id id struttura da dove si lancia l' attacco
	* @return none
	*/

	public function conquer_r ( $structure_id = null )
	{

		$view = new View ( 'royalpalace/conquer_r' );
		$sheets  = array('gamelayout'=>'screen', 'submenu'=>'screen');
		$subm    = new View ('template/submenu');
		$form = array(
			'region' => '',
			'attackedregion' => '',
			'attackedregion_id' => '',
			'maxattackers' => 20,
			'kingcandidate' => '',
			'kingcandidate_id' => '' );

		$character = Character_Model::get_info( Session::instance()->get('char_id') );

		if ( !$_POST )
		{
			$structure = StructureFactory_Model::create( null, $structure_id );

			// controllo permessi

			if ( ! $structure->allowedaccess( $character, $structure -> getParentType(), $message, 'private', 'conquer_r' ) )
			{
				Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
				url::redirect('region/view/');
			}

		}
		else
		{

			$structure = StructureFactory_Model::create( null, $this -> input -> post('structure_id') );

			// controllo permessi

			if ( ! $structure->allowedaccess( $character, $structure -> getParentType(), $message, 'private', 'conquer_r' ) )
			{
				Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
				url::redirect('region/view/');
			}
			$par[0] = $character;
			$par[1] = $structure -> region;
			$par[2] = 'conquer_r';
			$par[3] = ORM::factory( 'region') ->
				where (
					'name', strtolower('regions.' . $this->input->post( 'attackedregion' ) )
				) -> find();
			$par[4] = ORM::factory( 'character' ) -> where ( 'name', $this -> input -> post( 'kingcandidate' ) ) -> find();
			$par[5] = $this -> input -> post( 'maxattackers' );
			$par[6] = null;

			$ca = Character_Action_Model::factory('declarewaraction');

			$structure = StructureFactory_Model::create( null, $this -> input -> post('structure_id') );

			if ( $ca->do_action( $par,  $message ) )
			{
				Session::set_flash('user_message', "<div class=\"info_msg\">". $message . "</div>");
				url::redirect('royalpalace/declarehostileaction/' . $structure -> id );
			}
			else
			{
				$form = arr::overwrite( $form, $this -> input -> post());
				Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
			}

		}

		$view->form = $form;
		$submenu = new View( 'structure/' . $structure -> getSubmenu() );
		$submenu -> id = $structure -> id;
		$submenu -> action = 'declarehostileaction';
		$view->submenu = $submenu;
		$view -> structure = $structure;
		$this -> template -> content = $view;
		$this -> template -> sheets = $sheets;

	}

	/*
	 * Callback che verifica l' esistenza della regione
	 *
	 * @param  Validation  $array   oggetto Validation
	 * @param  string      $field   nome del campo che deve essere validato
	 */

	public function _regionexists(Validation $array, $field)
	{
	 // controllo il db
	 $region =  ORM::factory( 'region', $array['region_id'] );

	 if ( !$region -> loaded )
	 {
		 // aggiungo l' errore
		 $array->add_error($field, 'doesnotexist');
				 return false;
	 }

	}

/*
* Assegna una regione ad un Vassallo
*
* @param int $structure_id id struttura (re)
* @param int $vassal_id id vassallo
* @return none
*/

public function assign_region( $structure_id, $vassal_id )
{

	$view = new View ( 'royalpalace/assign_region' );
	$sheets  = array('gamelayout'=>'screen', 'submenu'=>'screen');
	$character = Character_Model::get_info( Session::instance()->get('char_id') );
	$form = array ('region' => null );
	$assignableregions = array();

	if ( !$_POST )
	{

		$structure = StructureFactory_Model::create( null, $structure_id );

		// controllo permessi
		if ( ! $structure -> allowedaccess( $character, $structure -> getParentType(), $message, 'private', 'assign_region' ) )
		{
			Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
			url::redirect('region/view/');
		}

		$assignableregions = $character -> region -> kingdom -> get_assignableregions();
		$vassal = ORM::factory( "character", $vassal_id );
	}
	else
	{

		$structure = StructureFactory_Model::create( null, $this -> input -> post('structure_id') );
		// controllo permessi
		if ( ! $structure -> allowedaccess( $character, $structure -> getParentType(), $message, 'private', 'assign_region' ) )
		{
			Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
			url::redirect('region/view/');
		}

		$par[0] = Character_Model::get_info( Session::instance()->get('char_id') );
		$par[1] = ORM::factory( 'region', $this -> input -> post( 'region_id' ) );
		$par[2] = ORM::factory( 'character', $this->input->post( 'sourcevassal_id' ) );
		$par[3] = ORM::factory( 'character', $this->input->post( 'destvassal_id' ) );

		$ca = Character_Action_Model::factory("assignregion");
		//var_dump( $this -> input -> post ( 'structure_id' ) ); exit;

		if ( $ca -> do_action( $par,  $message ) )
		{
			Session::set_flash('user_message', "<div class=\"info_msg\">". $message . "</div>");
			url::redirect('/royalpalace/assign_region/' . $structure -> id . '/' . $par[3] -> id );
		}
		else
		{
			//var_dump( '/royalpalace/assign_region/' . $par[2] -> id . '/' . $par[1] -> id ); exit;
			Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
			url::redirect('/royalpalace/assign_region/' . $structure -> id . '/' . $par[3] -> id );
		}


	}

	$submenu = new View( 'structure/' . $structure -> getSubmenu() );
	$submenu -> id = $structure -> id;
	$submenu -> action = 'assign_roles';
	$view->submenu = $submenu;
	$view -> structure = $structure;
	$view -> vassal = $vassal;
	$view -> form = $form;
	$view -> assignableregions = $assignableregions;

	$this -> template -> sheets = $sheets;
	$this -> template -> content = $view;

}


/*
 * Callback che verifica l' esistenza della candidato al Re.
 *
 * @param  Validation  $array   oggetto Validation
 * @param  string      $field   nome del campo che deve essere validato
 */

  public function _kingcandidateexists(Validation $array, $field)
  {
     // controllo il db
		 $region = ORM::factory('region', $array['region_id'] );
		 if ( $region -> loaded and $region -> capital == false )
			return;

     $kingcandidate =  ORM::factory( 'character', $array['kingcandidate_id'] );

     if ( ! $kingcandidate -> loaded  )
     {
         // aggiungo l' errore
         $array->add_error($field, 'doesnotexist');
				 return false;
     }

  }

	/**
	* Dichiara una rivolta
	*/

	function declarerevolt( $structure_id = null )
	{
		// Il modulo � disabilitato?

		if ( isset($this -> disabledmodules['declarerevolt']) )
		{
			Session::set_flash('user_message', "<div class=\"error_msg\">". kohana::lang('charactions.error-moduleisdisabled') . "</div>");
			url::redirect('region/view/' );
		}

		$view = new View ('royalpalace/declarerevolt');
		$sheets  = array('gamelayout'=>'screen', 'submenu'=>'screen');
		$char = Character_Model::get_info( Session::instance()->get('char_id') );

		if ( !$_POST )
		{
			$structure = StructureFactory_Model::create( null, $structure_id );

		}
		else
		{

			$structure = StructureFactory_Model::create( null, $this -> input -> post('structure_id') );
			$ca = Character_Action_Model::factory("declarerevolt");

			$par[0] = $char;
			$par[1] = $structure;

			if ( $ca -> do_action( $par,  $message ) )
			{
				Session::set_flash('user_message', "<div class=\"info_msg\">". $message . "</div>");
				url::redirect( 'region/view/' );
			}
			else
			{
				Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
			}
		}

		$cost = Battle_Revolt_Model::compute_costs( $structure -> region -> kingdom );
		$view -> cost = $cost;
		$view -> structure = $structure;
		$view -> char = $char;
		$this -> template->content = $view ;
		$this -> template->sheets = $sheets;

	}

/**
* Genera report risorse base
* @param int $structure_id ID struttura palazzo reale
* @return none
**/

function basicresourcereport( $structure_id )
{

	$view = new View('royalpalace/basicresourcereport' );
	$sheets  = array('gamelayout'=>'screen', 'submenu'=>'screen');
	$structure = StructureFactory_Model::create( null, $structure_id );

	$character = Character_Model::get_info( Session::instance()->get('char_id') );
	$role = $character -> get_current_role();

	// controllo permessi

	if ( ! $structure->allowedaccess( $character, $structure -> getParentType(), $message, 'private', 'basicresourcereport' ) )
	{
		Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
		url::redirect('region/view/');
	}

	$controlledregions = $character -> get_controlledregions();

	foreach ( $controlledregions as $region )
		$regions[] = $region -> id;
	$where = 'in ( ' . implode ( ',', $regions ) . ' )' ;

	$sql = "select st.name structure_name, r.name region_name, sr.resource, sr.max, sr.current
	from structures s, structure_types st, regions r, structure_resources sr
	where s.structure_type_id = st.id
	and   s.region_id = r.id
	and   sr.structure_id = s.id
	and   s.region_id " . $where ;

	$db = Database::instance();
	$res = $db -> query( $sql ) -> as_array();

	foreach ( $res as $r )
		$report[$r->region_name][$r->structure_name][$r->resource] = round(($r->current/$r->max)*100,0);

	$submenu = new View( 'structure/' . $structure -> getSubmenu() );
	$submenu -> id = $structure -> id;
	$submenu -> action = 'armory';
	$view->submenu = $submenu;

	$view -> structure = $structure;
	$view -> report = $report;
	$view -> role = $role;
	$this -> template->content = $view;
	$this -> template->sheets = $sheets;

}


	/**
	* Elenca leggi
	* @param int $structure_id ID Struttura
	* @return none
	*/

	public function viewlaws( $structure_id )
	{

		$view = new view('royalpalace/viewlaws');
		$sheets  = array('gamelayout'=>'screen', 'submenu'=>'screen');
		$structure = StructureFactory_Model::create( null, $structure_id );
		$character = Character_Model::get_info( Session::instance()->get('char_id') );

		$limit = 5	;

		// controllo permessi
		if ( ! $structure -> allowedaccess( $character, $structure -> getParentType(), $message,
			'private', 'viewlaws') )
		{
			Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
			url::redirect( 'region/view' );
		}

		$laws = ORM::factory( 'law' )	-> where( array ( 'kingdom_id' => $character -> region -> kingdom -> id ) ) -> find_all();

		$this -> pagination = new Pagination(array(
			'base_url' => 'royalpalace/viewlaws/' . $structure_id,
			'uri_segment' => $structure_id,
			'style' => "extended",
			'total_items' => $laws -> count(),
			'items_per_page' => $limit));

		$laws = ORM::factory( 'law' )
			-> where( array ( 'kingdom_id' => $character -> region -> kingdom -> id ) )->find_all($limit, $this->pagination->sql_offset);


		$submenu = new View( 'structure/' . $structure -> getSubmenu() );
		$submenu -> id = $structure -> id;
		$submenu -> action = 'viewlaws';
		$view->submenu = $submenu;
		$view -> laws = $laws;
		$view -> pagination = $this -> pagination ;
		$view -> structure = $structure;
		$this -> template->content = $view;
		$this -> template->sheets = $sheets;

	}

	/**
	* Cancella una legge
	*/

	public function deletelaw( $structure_id, $law_id )
	{

		$structure = StructureFactory_Model::create( null, $structure_id );
		$character = Character_Model::get_info( Session::instance()->get('char_id') );

		if ( ! $structure->allowedaccess( $character, $structure -> getParentType(), $message, 'private', 'deletelaw' ) )
		{
			Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
			Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
			url::redirect( 'region/view' );
		}

		$ca = Character_Action_Model::factory("deletelaw");
		$par[0] = ORM::factory("character", Session::instance()->get("char_id") );
		$par[1] = $structure;
		$par[2] = ORM::factory("law", $law_id );

		if ( $ca->do_action( $par,  $message ) )
		{
			Session::set_flash('user_message', "<div class=\"info_msg\">". $message . "</div>");
			url::redirect ( 'royalpalace/viewlaws/' . $structure -> id );
		}
		else
		{
			Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
		}

		url::redirect ( 'royalpalace/viewlaws/' . $structure -> id );

	}

	/**
	* Aggiunge una legge
	* @param int $structure_id: id struttura
	* @return none
	*/

	public function addlaw( $structure_id = null )
	{
		$view = new view('royalpalace/addlaw');
		$sheets  = array('gamelayout'=>'screen', 'submenu'=>'screen');
		$character = Character_Model::get_info( Session::instance()->get('char_id') );

		if ( !$_POST )
		{

			// controllo permessi
			$structure = StructureFactory_Model::create( null, $structure_id );
			if ( ! $structure->allowedaccess( $character, $structure -> getParentType(), $message, 'private', 'addlaw' ) )
			{
				Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
				url::redirect( 'region/view' );
			}

			$form = array( 'law_name' => '', 'law_desc' => '' );
			$view->bind('form',$form);

		}
		else
		{

			$form = array( 'law_name' => '', 'law_desc' => '');
			$structure = StructureFactory_Model::create( null, $this -> input -> post('structure_id') );

			// controllo permessi
			$structure = StructureFactory_Model::create( null, $this -> input -> post('structure_id') );

			if ( ! $structure->allowedaccess( $character, $structure -> getParentType(), $message, 'private', 'addlaw' ) )
			{
				Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
				url::redirect( 'region/view' );
			}

			$post = Validation::factory($this->input->post())
				->pre_filter('trim', TRUE)
				->add_rules('law_name','required', 'length[3,50]');

			if ($post->validate() )
			{
				$par[0] = $character;
				$par[1] = $structure;
				$par[2] = $this->input->post( 'law_name');
				$par[3] = $this->input->post( 'law_desc');

				$ca = Character_Action_Model::factory("addlaw");
				if ( $ca -> do_action( $par,  $message ) )
				{
					Session::set_flash('user_message', "<div class=\"info_msg\">". $message . "</div>");
					url::redirect ( 'royalpalace/viewlaws/' . $structure->id );
				}
				else
				{
					Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
					url::redirect ( 'royalpalace/viewlaws/' . $structure->id );
				}
			}
			else
			{
				$errors = $post->errors('form_errors');
				$view->bind('errors', $errors);
				//ripopolo la form
				$form = arr::overwrite($form, $post->as_array());
				//print kohana::debug( $form ); exit();

			}


		}

		$submenu = new View( 'structure/' . $structure -> getSubmenu() );
		$submenu -> id = $structure -> id;
		$submenu -> action = 'viewlaws';
		$view->submenu = $submenu;
		$view->form = $form;
		$view->structure = $structure;
		$this->template->sheets = $sheets;
		$this->template->content = $view;

	}


	/**
	* Modifica una legge
	* @param int $structure_id: id struttura
	* @param it $law_id: id legge
	* @return none
	*/

	public function editlaw( $structure_id = null, $law_id = null )
	{

		$view = new view('royalpalace/editlaw');
		$sheets  = array('gamelayout'=>'screen', 'submenu'=>'screen');
		$character = Character_Model::get_info( Session::instance()->get('char_id') );
		$subm = new View ('template/submenu');

		if ( !$_POST )
		{
			$structure = StructureFactory_Model::create( null, $structure_id );
			if ( ! $structure -> allowedaccess( $character, $structure -> getParentType(), $message,	'private', 'editlaw') )
			{
				Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
				url::redirect( 'region/view' );
			}

			$law = ORM::factory("law", $law_id );

			// se 24 ore sono passate non � pi� possibile editarla

			if ( time() > ($law -> timestamp + ( 3 * 24 * 3600 ) ) )
			{
				$message = kohana::lang( 'ca_editlaw.error-lawtooold');
				Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
				url::redirect('/royalpalace/viewlaws/' . $structure_id );
			}

			$form = array( 'law_name' => $law->name, 'law_desc' => $law->description );
			$view->bind('form',$form);

		}
		else
		{

			$form = array( 'law_name' => '', 'law_desc' => '');
			$structure = StructureFactory_Model::create( null, $this -> input -> post('structure_id') );
			if ( ! $structure -> allowedaccess( $character, $structure -> getParentType(), $message,	'private', 'editlaw') )
			{
				Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
				url::redirect( 'region/view' );
			}
			$law = ORM::factory("law", $this->input->post( 'law_id') );

			$post = Validation::factory($this->input->post())
				->pre_filter('trim', TRUE)
				->add_rules('law_name','required', 'length[3,50]');

			if ($post -> validate() )
			{

				$par[0] = $character;
				$par[1] = $structure;
				$par[2] = $law;
				$par[3] = $this->input->post( 'law_name');
				$par[4] = $this->input->post( 'law_desc');

				$ca = Character_Action_Model::factory("editlaw");
				if ( $ca -> do_action( $par,  $message ) )
				{
					Session::set_flash('user_message', "<div class=\"info_msg\">". $message . "</div>");
					url::redirect('royalpalace/viewlaws/' . $structure_id );
				}
				else
				{
					Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
				}

			}
			else
			{
				$errors = $post->errors('form_errors');
				$view->bind('errors', $errors);
				$form = arr::overwrite($form, $post->as_array());
			}

		}

		$submenu = new View( 'structure/' . $structure -> getSubmenu() );
		$submenu -> id = $structure -> id;
		$submenu -> action = 'viewlaws';
		$view->submenu = $submenu;
		$view -> law = $law;
		$view -> structure = $structure;
		$this -> template -> content = $view;
		$this -> template -> sheets = $sheets;
		$view -> form = $form;

	}


	/**
	* Assegna i titoli e gli incarichi reali ai giocatori
	* @param int $structure_id    id del palazzo reale
	* @return none
	**/

	function assign_rolerp( $structure_id )
	{

		$view   = new View ( 'royalpalace/assign_rolerp' );
		$sheets = array('gamelayout'=>'screen', 'submenu'=>'screen');
		$subm   = new View ('template/submenu');

		// Inizializzo le form
		$formroles = array
		(
		'role'        => 'seneschal',
		'region'      => null,
		'nominated'   => null,
		'place'       => null,
		);

		$formtitles = array
		(
		'title'       => 'prince',
		'region'      => null,
		'nominated'   => null,
		'place'       => null,
		);

		// Definisco gli incarichi reali
		// assegnabili
		$roles = array
		(
		'seneschal'   => kohana::lang('global.seneschal_m'),
		'constable'   => kohana::lang('global.constable_m'),
		'chancellor'  => kohana::lang('global.chancellor_m'),
		'chamberlain' => kohana::lang('global.chamberlain_m'),
		'treasurer'   => kohana::lang('global.treasurer_m'),
		'ambassador'  => kohana::lang('global.ambassador_m'),
		'chaplain'    => kohana::lang('global.chaplain_m'),
		);

		// Definisco i titoli reali
		$titles = array
		(
		'prince'   => kohana::lang('global.prince_m'),
		'duke'     => kohana::lang('global.duke_m'),
		'marquis'  => kohana::lang('global.marquis_m'),
		'earl'     => kohana::lang('global.earl_m'),
		'viscount' => kohana::lang('global.viscount_m'),
		'baron'    => kohana::lang('global.baron_m')
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
				url::redirect( 'royalpalace/assign_rolerp/' . $structure->id);
			}
			else
			{
				Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
				url::redirect ( 'royalpalace/assign_rolerp/' . $structure->id );
			}
		}

		$submenu = new View( 'structure/' . $structure -> getSubmenu() );
		$submenu -> id = $structure -> id;
		$submenu -> action = 'assign_rolerp';
		$view->submenu = $submenu;


		$view -> structure = $structure;
		$view -> formroles = $formroles;
		$view -> formtitles = $formtitles;
		$view -> roles = $roles;
		$view -> titles = $titles;
		$this->template->content = $view;
		$this->template->sheets = $sheets;

	}

	/**
	* Visualizza lo stato diplomatico con i diversi regni
	* @param int $structure_id ID Struttura
	* @param none
	*/

	public function diplomacy( $structure_id )
	{

		$view   = new View ( 'royalpalace/diplomacy' );
		$sheets = array('gamelayout'=>'screen', 'submenu'=>'screen');
		$structure = StructureFactory_Model::create( null, $structure_id );
		$character = Character_Model::get_info( Session::instance()->get('char_id') );

		// controllo permessi
		if ( ! $structure -> allowedaccess( $character, $structure -> getParentType(), $message,	'private', 'diplomacy' ) )
		{
			Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
			url::redirect('region/view/' );
		}

		// trova proposte

		$view -> proposals = ORM::factory('diplomacy_proposal')
			-> where( array(
				'sourcekingdom_id' => $character -> region -> kingdom_id,
				'status' => 'new') )
			-> orwhere( 'targetkingdom_id', $character -> region -> kingdom_id)
			-> find_all();

		$kingdoms = Database::instance() -> query("select * from kingdoms_v");
		$relations = Configuration_Model::get_cfg_diplomacyrelations();
		$submenu = new View( 'structure/' . $structure -> getSubmenu() );
		$submenu -> id = $structure -> id;
		$submenu -> action = 'diplomacy';
		$view -> submenu = $submenu;
		$view -> kingdoms = $kingdoms;
		$view -> region = $character -> region;
		$view -> relations = $relations;
		$view -> structure = $structure;
		$view -> character = $character;
		$this -> template -> content = $view;
		$this -> template -> sheets = $sheets;

	}

	/**
	* Modifica lo stato diplomatico
	* @param int $structure_id ID Struttura di comando
	* @param int $diplomacystatus_id ID patto diplomazia
	* @return none
	*/

	public function modifydiplomacystatus( $structure_id = null, $diplomacystatus_id = null )
	{
		$view   = new View ( 'royalpalace/modifydiplomacystatus' );
		$sheets = array('gamelayout'=>'screen', 'submenu'=>'screen');
		$subm   = new View ('template/submenu');
		$character = Character_Model::get_info( Session::instance()->get('char_id') );
		$form = array('type' => '', 'description' => '' );

		if ( !$_POST )
		{
			$structure = StructureFactory_Model::create( null, $structure_id );

			// controllo permessi
			if ( ! $structure -> allowedaccess( $character, $structure -> getParentType(), $message, 'private', 'modifydiplomacystatus' ) )
			{
				Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
				url::redirect('royalpalace/diplomacy/' . $structure -> id );
			}

			// controllo stato relazione

			$diplomacystatusinfo = Diplomacy_Relation_Model::get_info( $diplomacystatus_id );

			if ( is_null( $diplomacystatusinfo ) )
			{
				Session::set_flash('user_message', "<div class=\"error_msg\">". kohana::lang('global.operation_not_allowed') . "</div>");
				url::redirect('royalpalace/diplomacy/' . $structure -> id );
			}

		}
		else
		{

			$diplomacystatusinfo = Diplomacy_Relation_Model::get_info( $this -> input -> post('diplomacystatus_id') );
			$structure = StructureFactory_Model::create( null, $this -> input -> post('structure_id') );

			if ( ! $structure -> allowedaccess( $character, $structure -> getParentType(), $message, 'private', 'modifydiplomacystatus' ) )
			{
				Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
				url::redirect('royalpalace/diplomacy/' . $structure -> id );
			}

			$par[0] = $character;
			$par[1] = $structure;
			$par[2] = $diplomacystatusinfo;
			$par[3] = $this -> input -> post('type');
			$par[4] = $this -> input -> post('description');
			$par[5] = false;

			$ca = Character_Action_Model::factory("modifydiplomacystatus");

			if ( $ca->do_action( $par, $message ) )
			{
				Session::set_flash('user_message', "<div class=\"info_msg\">". $message . "</div>");
				url::redirect('royalpalace/diplomacy/' . $structure -> id);
			}
			else
			{
				$form['type'] = $this -> input -> post('type');
				$form['description'] = $this -> input -> post('description');
				Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");

			}


		}
		$submenu = new View( 'structure/' . $structure -> getSubmenu() );
		$submenu -> id = $structure -> id;
		$submenu -> action = 'diplomacy';
		$view->submenu = $submenu;
		$view -> diplomacystatusinfo = $diplomacystatusinfo;
		$view -> structure = $structure;
		$view -> form = $form;
		$this -> template->content = $view;
		$this -> template->sheets = $sheets;

	}

	/**
	* Accetta o rifiuta una proposta diplomatica
	* @param str $feedback esito risposta
	* @param int $proposal_id ID Proposta
	* @return none
	*/

	function diplomacyproposalfeedback ( $feedback, $proposal_id )
	{

		$character = Character_Model::get_info( Session::instance()->get('char_id') );
		$structure = $character -> region -> kingdom -> get_structure('royalpalace');
		$proposal = ORM::factory('diplomacy_proposal', $proposal_id);
		$diplomacystatusinfo = Diplomacy_Relation_Model::get_info( $proposal -> diplomacy_relation_id );


		if ( !$proposal -> loaded )
		{
			Session::set_flash('user_message', "<div class=\"error_msg\">" . kohana::lang('diplomacy.error-cannotfindproposal') . "</div>");
			url::redirect('royalpalace/diplomacy/' . $structure -> id);
		}

		if ($feedback == 'accept' )
		{

			$par[0] = $character;
			$par[1] = $structure;
			$par[2] = $diplomacystatusinfo;
			$par[3] = $proposal -> diplomacyproposal;
			$par[4] = $feedback;
			$par[5] = true;

			$ca = Character_Action_Model::factory("modifydiplomacystatus");

			if ( $ca->do_action( $par, $message ) )
			{
				Session::set_flash('user_message', "<div class=\"info_msg\">". $message . "</div>");
				url::redirect('royalpalace/diplomacy/' . $structure -> id);
			}
			else
			{
				Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
				url::redirect('royalpalace/diplomacy/' . $structure -> id);
			}
		}
		else
		{
			// Invia evento a Re proponente

			$sourcekingdom = ORM::factory('kingdom', $diplomacystatusinfo -> sourcekingdom_id);
			$sourceking = $sourcekingdom -> get_king();

			if (!is_null($sourceking))
				Character_Event_Model::addrecord(
					$sourceking -> id,
					'normal',
					'__events.diplomacyproposalrefused'.
					';__' .	$character -> region -> kingdom -> name,
					'evidence'
					);

			// cancella proposta e non cambiare relazione diplomatica

			$proposal -> status = 'declined';
			$proposal -> save();

			Session::set_flash('user_message', "<div class=\"info_msg\">".
				kohana::lang('diplomacy.info-diplomacyproposaldeclined') . "</div>");
			url::redirect('royalpalace/diplomacy/' . $structure -> id);
		}

	}

	/**
	* calcola i costi di un attacco
	* @param none
	* @return none
	*/

	public function computeattackcosts ()
	{
		$this -> auto_render = false;
		kohana::Log('info', kohana::debug( $this -> input -> post() ) );

		if ( $this -> input -> post ('attacktype' ) == 'conquer_r' )
			$cost = Battle_Conquer_R_Model::compute_costs( $this -> input -> post( 'maxattackers' ) );
		if ( $this -> input -> post ('attacktype' ) == 'raid' )
			$cost = Battle_Raid_Model::compute_costs( $this -> input -> post( 'maxattackers' ) );

		echo $cost;

	}

	/**
	* Assegna il permesso di accesso al Regno ad un singolo player
	* @param int $structure_id id struttura di comando
	* @return none
	*/

	public function giveaccesspermit( $structure_id = null )
	{
		$view   = new View ( 'royalpalace/giveaccesspermit' );
		$sheets = array('gamelayout'=>'screen', 'submenu'=>'screen');
		$subm   = new View ('template/submenu');
		$character = Character_Model::get_info( Session::instance()->get('char_id') );
		$form = array( 'character' => '' );

		if ( !$_POST )
		{
			$structure = StructureFactory_Model::create( null, $structure_id );

			// controllo permessi
			if ( ! $structure -> allowedaccess( $character, $structure -> getParentType(), $message,	'private', 'giveaccesspermit' ) )
			{
				Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
				url::redirect('royalpalace/diplomacy/' . $structure -> id );
			}

		}
		else
		{

			//var_dump( $_POST ); exit;
			$structure = StructureFactory_Model::create( null, $this -> input -> post('structure_id') );
			// controllo permessi
			if ( ! $structure -> allowedaccess( $character, $structure -> getParentType(), $message,	'private', 'giveaccesspermit' ) )
			{
				Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
				url::redirect('royalpalace/diplomacy/' . $structure -> id );
			}
			$par[0] = $this -> input -> post('character');
			$par[1] = $structure;

			$ca = Character_Action_Model::factory("giveaccesspermit");

			if ( $ca->do_action( $par, $message ) )
			{
				Session::set_flash('user_message', "<div class=\"info_msg\">". $message . "</div>");
				url::redirect('royalpalace/diplomacy/' . $structure -> id);
			}
			else
			{
				$form['type'] = $this -> input -> post('type');
				$form['description'] = $this -> input -> post('description');
				Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");

			}

		}

		$submenu = new View( 'structure/' . $structure -> getSubmenu() );
		$submenu -> id = $structure -> id;
		$submenu -> action = 'diplomacy';
		$view->submenu = $submenu;
		$view -> structure = $structure;
		$view -> form = $form;
		$this -> template->content = $view;
		$this -> template->sheets = $sheets;

	}

	/**
	* Assegna i titoli e gli incarichi reali ai giocatori
	* @param   $structure_id    id del palazzo reale
	* @return  none
	**/

	function customizenobletitles( $structure_id )
	{
		// Carico la struttura

		$structure = StructureFactory_Model::create( null, $structure_id );

		// Carico il character
		$character = Character_Model::get_info( Session::instance()->get('char_id') );

		// Controllo permessi di accesso alla struttura
		if (! $structure->allowedaccess( $character, $structure -> getParentType(), $message, 'private', 'customizenobletitles' ))
		{
			Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
			url::redirect('region/view/');
		}

		// Controlli di accesso superati
		// Verifico se la pagina � in visualizzazione oppure
		// sono stati inviati dei dati dalle forms
		if ( !$_POST )
		{
			// Titoli nobiliari originali
			$originaltitles = array
			(
				'prince',
				'marquis',
				'duke',
				'earl',
				'viscount',
				'baron',
				'lord',
				'knight'
			);

			// Inizializzo la vista per customizzare i titoli
			$view = new View ( 'royalpalace/customizenobletitles' );

			// Passo l'elenco dei titoli nobiliari originali alla vista
			$view -> originaltitles = $originaltitles;

			// Prelevo dal database gli eventuali titoli nobiliari
			// che sono stati modificati
			$modifiedtitles = Kingdom_Nobletitle_Model::get_customisedtitles($structure->region->kingdom_id);
			// Passo i titoli alla vista
			$view -> modifiedtitles = $modifiedtitles;
			$submenu = new View( 'structure/' . $structure -> getSubmenu() );
			$submenu -> id = $structure -> id;
			$submenu -> action = 'assign_rolerp';
			$view -> structure = $structure;
			$view -> submenu = $submenu;

			// Passo la vista al template
			$this->template->content = $view;

			// Assegno al template i css
			$sheets = array('gamelayout'=>'screen', 'submenu'=>'screen');
			$this->template->sheets = $sheets;
		}
		else
		{
			// Inizializzo la char action per customizzare i titoli
			$ca = Character_Action_Model::factory("customizenobletitles");
			// Characther che compie l'azione
			$par[0] = $character;
			// Struttura da cui viene lanciata la customize
			$par[1] = $structure;
			// Titolo originale
			$par[2] = $this->input->post('originaltitle');
			// Titolo customizzato maschile
			$par[3] = $this->input->post('customisedtitle_m');
			// Titolo customizzato femminile
			$par[4] = $this->input->post('customisedtitle_f');
			// Immagine custom
			$par[5] = $this->input->post('custon_title_image');

			if ( $ca->do_action( $par,  $message ) )
			{
				Session::set_flash('user_message', "<div class=\"info_msg\">". $message . "</div>");
				url::redirect('royalpalace/customizenobletitles/' . $structure->id);
			}
			else
			{
				Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
				url::redirect('royalpalace/customizenobletitles/' . $structure->id );
			}
		}


	}

}
