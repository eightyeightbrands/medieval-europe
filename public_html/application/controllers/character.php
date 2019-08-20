<?php defined('SYSPATH') OR die('No direct access allowed.');

class Character_Controller extends Template_Controller
{

	protected $has_many = array('character_actions');

	const SECURITYKEY = 'ro432u4elwfjreljehgrehrekqthkgtqteegq';
	//const MAXAGEFORCA = 30; // Massima età ammessa per ridistribuzione attributi

	// Imposto il nome del template da usare
	public $template = 'template/gamelayout';

	// Se l'utente digita solo character allora
	// viene rediretto al profilo del char

	public function index()
	{
		kohana::log( 'debug', 'generating random names...' . $this -> input -> post('culture') . $this -> input -> post('sex'));
		url::redirect('character/details');
	}

	public function change_language( $lang = 'en_US' )
	{
		User_Model::change_language( $lang );
		url::redirect(request::referrer());
	}

	/**
	* Unequip all items
	* @param none
	* @return none
	*/

	public function unequip_all()
	{
		$message = '';
		$character = Character_Model::get_info( Session::instance()->get('char_id') );
		$rc = Character_Model::unequip_all( $character->id, $message );
		if ($rc)
			Session::set_flash('user_message', "<div class=\"info_msg\">". kohana::lang('charactions.info-unequippedall') . "</div>");
		else
			Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
		url::redirect('character/inventory');
	}

	/**
	* Crea Personaggio
	*
	*@param none
	*@return none
	*/

	public function create()
	{

		$view = new View('character/create');
		$sheets  = array('gamelayout'=>'screen', 'submenu'=>'screen');
		$this -> template->content = $view;
		$this -> template->sheets = $sheets;

		$combo = null;

    $form = array ( 'charname'    => '',
			'charsurname' => null,
			'charsex'     => 'M',
			'charculture' => 'Italian',
			'charspokenlanguage1' => '',
			'charspokenlanguage2' => '',
			'charspokenlanguage3' => '',
			'charpoints'  => 5,
			'charstr'     => 7,
			'chardex'     => 7,
			'charint'     => 7,
			'charborn'    => null,
			'charcost'    => 7,
			'charcar'     => 7 );

		// Combo cultura

		$view -> combo_culture = array(
			'Albanian' => 'Albanian',
			'British/Anglo/Scoto/Norman' => 'British/Anglo/Scoto/Norman',
			'Bulgarian' => 'Bulgarian',
			'Byzantine' => 'Byzantine',
			'Central Europe' => 'Central Europe',
			'Flemish/Dutch' => 'Flemish/Dutch',
			'French' => 'French',
			'Gaelic/Irish/Welsh' => 'Gaelic/Irish/Welsh',
			'German' => 'German',
			'Hungarian' => 'Hungarian',
			'Iberic' => 'Iberic',
			'Italian' => 'Italian',
			'Moor' => 'Moor',
			'Romana' => 'Romana',
			'Scandinavian' => 'Scandinavian',
			'Slavic' => 'Slavic',
			'Turkish' => 'Turkish'
		);

		// combo Linguaggi

		$view -> spokenlanguages = array(
			'' => kohana::lang('global.select'),
			'Bulgarian' => 'Bulgarian',
			'Croatian' => 'Croatian',
			'Czech' => 'Czech',
			'Dutch' => 'Dutch',
			'English' => 'English',
			'French' => 'French',
			'German' => 'German',
			'Italian' => 'Italian',
			'Portuguese' => 'Portuguese',
			'Russian' => 'Russian',
			'Serbian' => 'Serbian',
			'Spanish' => 'Spanish',
		);

		// Inizializzo la combo per il sesso
		$view -> combo_sex = array('M'=>Kohana::lang('character.create_male'), 'F'=>Kohana::lang('character.create_female'));

		// Inizializzo la combo per L'araldica

		$subscribable_kingdoms = Kingdom_Model::get_subscribable_kingdoms();
		$view -> subscribable_kingdoms = $subscribable_kingdoms;

		// Controllo se la form � stata inviata

		if ( $_POST )
		{

			$post = Validation::factory($this->input->post())
					->pre_filter('trim', TRUE)
					->add_rules('choosenkingdom_id','required')
					->add_rules('charname','required', 'length[3,15]')
					->add_rules('charsurname','required', 'length[3,15]')
					->add_rules('charspokenlanguage1','required')
					->add_callbacks('charsurname', array($this, '_nameisunique'))
					->add_callbacks('charname', array($this, '_checknameformat'))
					->add_callbacks('charsurname', array($this, '_checksurnameformat'))
					->add_rules('charpoints', 'chars[0]')
					->add_callbacks('charpoints', array($this, '_total_stats'))
					->add_callbacks('charpoints', array($this, '_range_stats'));

			if ($post -> validate() )
			{

				// controllo che l' utente non abbia già altri char

				$existchar = ORM::factory('character')->where( 'user_id', Session::instance()->get('user_id') ) -> find();
				if ( $existchar -> loaded )
				{
					Session::set_flash('user_message', "<div class=\"error_msg\">". kohana::lang('global.operation_not_allowed') . "</div>");
					url::redirect('character/create');
				}

				// determina la regione del regno scelto pi� popolata

				if (!isset($post['choosenkingdom_id']) )
				{
					Session::set_flash('user_message', "<div class=\"error_msg\">". kohana::lang('character.kingdomnotchosen') . "</div>");
					url::redirect('character/create');
				}

				$region_id = Kingdom_Model::get_destination_region( $post['choosenkingdom_id']);

				if ( is_null( $region_id ) )
				{
						Session::set_flash('user_message', "<div class=\"error_msg\">". kohana::lang('character.kingdomisfull') . "</div>");
						url::redirect('character/create');
				}


				//kohana::log('debug', 'region: ' . $region -> name ); exit();

				$char = ORM::factory('character');
				$char->user_id = Session::instance()->get('user_id');
				$char->name = ucwords(strtolower($post['charname'].' '.$post['charsurname']));
				$char->sex = $post['charsex'];
				$char->region_id = $region_id;
				$char->birth_region_id = $char->region_id;
				$char->position_id = $char->region_id;
				$region = ORM::factory('region', $char -> region_id );

				// Imposto le statistiche
				$char->str = $post['charstr'];
				$char->dex = $post['chardex'];
				$char->intel  = $post['charint'];
				$char->cost  = $post['charcost'];
				$char->car = $post['charcar'];

				// Imposto sazietà, energia e salute
				$char->glut = 50;
				$char->energy = 50;
				$char->health = 100;

				// imposto chiesa
				$nochurch = ORM::factory('church') -> where ( 'name', 'nochurch') -> find();
				$char->church_id = $nochurch -> id ;

				// data di nascita

				$char -> birthdate = time();

				// linguaggi parlati, su user

				$language1 = new User_Language_Model();
				$language1 -> user_id = $char -> user_id;
				$language1 -> position = 1;
				$language1 -> language = $post['charspokenlanguage1'];

				$language2 = new User_Language_Model();
				$language2 -> user_id = $char -> user_id;
				$language2 -> position = 2;
				$language2 -> language = $post['charspokenlanguage2'];

				$language3 = new User_Language_Model();
				$language3 -> user_id = $char -> user_id;
				$language3 -> position = 3;
				$language3 -> language = $post['charspokenlanguage3'];

				$language4 = new User_Language_Model();
				$language4 -> user_id = $char -> user_id;
				$language4 -> position = 4;
				$language4 -> language = '';

				$language5 = new User_Language_Model();
				$language5 -> user_id = $char -> user_id;
				$language5 -> position = 5;
				$language5 -> language = '';

				try
				{

					$db = Database::instance();
					$db -> query("set autocommit = 0");
					$db -> query("start transaction");
					$db -> query("begin");

					$char -> save();
					$char -> user -> save();

					$language1 -> save();
					$language2 -> save();
					$language3 -> save();
					$language4 -> save();
					$language5 -> save();

					///////////////////////////////////////////////////////////////////////
					// Startup Kit
					///////////////////////////////////////////////////////////////////////

					$bread = Item_Model::factory(null, 'bread');
					$bread -> additem( "character", $char -> id, 1);
					$char -> modify_coins(10, 'startup kit');

					///////////////////////////////////////////////////////////////////////
					// Assegno i dobloni se presenti sulla tabella utente
					///////////////////////////////////////////////////////////////////////

					if ( $char -> user -> doubloons > 0 )
					{
						$char -> modify_doubloons( $char -> user -> doubloons);

						// Azzero i dobloni nello user
						$char -> user -> doubloons = 0;
						$char -> user -> save();
					}
					///////////////////////////////////////////////////////////////////////
					// lo rivesto di stracci
					///////////////////////////////////////////////////////////////////////

					if ( $char -> sex == 'M' )
					{
						$rags_shirt = Item_Model::factory(null, "rags_shirt");
						$rags_trousers = Item_Model::factory(null, "rags_trousers");

						$rags_shirt -> additem( "character", $char->id, 1, true);
						$rags_trousers -> additem( "character", $char->id, 1, true);
					}
					else
					{
						$rags_robe = Item_Model::factory(null, "rags_robe");
						$rags_robe -> additem( "character", $char->id, 1, true );
					}

					///////////////////////////////////////////////////////////////////////
					// Istanzio l'azione ciclica della fame
					///////////////////////////////////////////////////////////////////////

					$ca = Character_Action_Model::factory("consumeglut");
					$ca -> character_id = $char -> id;
					$ca -> action = 'consumeglut';
					$ca -> blocking_flag = 0;
					$ca -> cycle_flag = 1;
					$ca -> status = 'running';
					$ca -> starttime = time();
					$ca -> endtime = time();
					$ca -> save();

					$char -> user -> tutorialmode = 'Y' ;
					$char -> user -> save();

					///////////////////////////////////////////////////////////////////////
					// avviso vassallo e re.
					///////////////////////////////////////////////////////////////////////

					$king = $region -> get_roledetails( 'king' );
					$vassal = $region -> get_roledetails( 'vassal' );
					$chancellor = $region -> get_roledetails( 'chancellor' );

					if ( !is_null ( $king ) )
					{
						Character_Event_Model::addrecord(
							$king->character_id,
							'normal',
							'__events.city_newcharacterborn;' .
							'__' . $region->kingdom -> get_name()  . ';__'.$region->name  . ';' . $char->name,
							'evidence');
					}

					if ( !is_null( $vassal) )
					{
						Character_Event_Model::addrecord( $vassal->character_id, 'normal', '__events.city_newcharacterborn;' .
							'__' . $region->kingdom -> get_name()  . ';__'.$region->name  . ';' . $char->name,
							'evidence' );
					}

					// invio il welcome message del RE

					$king = $region -> get_roledetails( 'king' );

					if ( !is_null( $king ) )
					{
						$welcomemessage = ORM::factory('region_announcement' ) ->
							where ( array(
							'region_id' => $king -> character -> region -> id,
							'type' => 'kingdom',
							'subtype' => 'welcomemessage' ) ) -> find();

						if ( $welcomemessage -> loaded )
						{
							$welcomemessagesubject = $welcomemessage -> title;
							$welcomemessagetext = $welcomemessage -> text;
							$m = new Message_Model;
							$m -> send( $king -> character, $char, $welcomemessagesubject, $welcomemessagetext );


						}
					}

					// evento permanente

					Character_Permanentevent_Model::add(
					$char -> id,
					'__permanentevents.birth' . ';' .
					'__' . $region -> name );

					///////////////////////////////////////////////////////////////////////
					// Assegno il Tutor
					///////////////////////////////////////////////////////////////////////

					Character_Model::assign_tutor( $char, $language1 -> language);
					kohana::log('info', '-> createchar ***commit***.');
					$db -> query("commit");

					///////////////////////////////////////////////////////////////////////
					// Inserisco il char_id in sessione
					///////////////////////////////////////////////////////////////////////

					Session::instance()->set( 'char_id', $char->id );
					Session::instance()->set( 'position_id', $char->region_id );

					url::redirect( 'region/view');

				} catch (Kohana_Database_Exception $e)
				{
					kohana::log('error', kohana::debug( $e->getMessage() ));
					kohana::log('error', "An error occurred while creating char: {$char -> name}, error: {$e->getMessage()}, rollbacking.");
					$db -> query("rollback");

					Session::set_flash('user_message', "<div class=\"error_msg\">". Kohana::lang('character.register_createerror') . "</div>");

					url::redirect('character/create');

				}

				$db -> query("set autocommit = 1");

			}
			else
			{
				// traduco gli errori con gli errori custom internazionalizzati

				$errors = $post -> errors('form_errors');
				kohana::log('debug', kohana::debug($errors));
				$view->bind('errors', $errors);
				//ripopolo la form
				$form = arr::overwrite($form, $post->as_array());
			}
		}

		$view -> bind('form', $form );

	}


	/**
	* Callback di validazione per la validazione della form di creazione char
	* Controllo che non ci siano ancora punti da distribuire tra le stats
	*/

	public function _total_stats(Validation $array, $field)
	{
		if ( (int)$array['charstr'] + (int)$array['chardex'] + (int)$array['charint'] + (int)$array['charcost'] + (int)$array['charcar'] != 40 )
		{ $array->add_error($field, 'notequal_50'); }
	}

	/**
	* Callback di validazione per verificare se il nome � già stato preso
	*/

	public function _nameisunique(Validation $array, $field)
	{
		$char = ORM::factory('character')
		->where( array( 'name' => ucwords($array['charname'] . ' ' . $array['charsurname'])))->find();

		if ( $char->loaded )
			$array->add_error($field, 'username_exists');
	}


	/**
	* Callback per controllo caratteri extra europei
	*/

	public function _checknameformat(Validation $array, $field)
	{
		if (! preg_match( "/^[ \x{00C0}-\x{01FF}a-zA-Z'\-]+$/u", $array['charname'] ) )

		$array->add_error($field, 'wrongformat');
	}

	public function _checksurnameformat(Validation $array, $field)
	{
		if (! preg_match( "/^[ \x{00C0}-\x{01FF}a-zA-Z'\-]+$/u", $array['charsurname'] ) )

		$array->add_error($field, 'wrongformat');
	}

	/**
	* Callback per la validazione della form di creazione char
	* Controllo che le stats siano tutte in range 1-15
	*/

	public function _range_stats(Validation $array, $field)
	{
		if (
			( (int)$array['charstr'] <= 15 AND (int)$array['charstr'] >= 1 ) AND
			( (int)$array['charint'] <= 15  AND (int)$array['charint'] >= 1 ) AND
			( (int)$array['charcost'] <=15 AND (int)$array['charcost'] >= 1 ) AND
			( (int)$array['charcar'] <= 15 AND (int)$array['charcar'] >= 1 ) AND
			( (int)$array['chardex'] <= 15 AND (int)$array['chardex'] >= 1 ) )

		{ }
		else
		{ $array->add_error($field, 'notinrange'); }
	}

	/**
	* Mostra i dettagli del personaggio
	* @param none
	* @return none
	*/

	public function details()
	{

		$char = Character_Model::get_info( Session::instance()->get('char_id') );
		$home = ORM::factory('region', $char->region_id );
		$view    = new View ('character/details');
		$sheets  = array('gamelayout' => 'screen', 'character'=>'screen', 'pagination'=>'screen', 'submenu'=>'screen');

		// Se il char ha age > 30 o ha già ridistribuito gli attributi
		// non faccio apparire il link

		if ( $char-> is_newbie($char) == false  or !is_null ( $char -> get_stats( 'attributeredistributed' ) ) )
			$showcalink = false;
		else
			$showcalink = true;

		if ( $char->position_id == 0 )
			$view->current_position = 'regionview.traveling' ;
		else
			$view->current_position = ORM::factory('region', $char->position_id)->name;

		// Carica malattie

		$diseasesarray = array();
		if ( $char -> is_sick() )
		{
			$diseases = $char -> get_diseases();
			foreach ( $diseases as $disease )
			{
				$diseasesarray[] = kohana::lang('character.disease_' . $disease -> param1 );
			}
		}

		$stat = Character_Model::get_stat_d( $char -> id, 'honorpoints');
			if (!$stat -> loaded)
				$view -> honorpoints = 0;
			else
				$view -> honorpoints = $stat -> value;

		$view -> diseaseslist = $diseasesarray;
		$view -> dexinfooriginal = $char -> get_attribute( 'dex', false );
		$view -> dexinfo = $char -> get_attribute( 'dex' );
		$view -> dexmodifiers = $char -> get_attribute_modifier( 'dex' );
		$view -> strinfooriginal = $char -> get_attribute( 'str', false );
		$view -> strinfo = $char -> get_attribute( 'str' );
		$view -> strmodifiers = $char -> get_attribute_modifier( 'str' );
		$view -> intelinfooriginal = $char -> get_attribute( 'intel', false );
		$view -> intelinfo = $char -> get_attribute( 'intel' );
		$view -> intelmodifiers = $char -> get_attribute_modifier( 'intel' );
		$view -> costinfooriginal = $char -> get_attribute( 'cost', false );
		$view -> costinfo = $char -> get_attribute( 'cost' );
		$view -> costmodifiers = $char -> get_attribute_modifier( 'cost' );
		$view -> carinfooriginal = $char -> get_attribute( 'car', false );
		$view -> carinfo = $char -> get_attribute( 'car' );
		$view -> carmodifiers = $char -> get_attribute_modifier( 'car' );

		// malattie

		$view -> diseases = $char -> get_diseases();

		// parentele

		$view -> kinrelations = Character_Relationship_Model::get_kinrelations( $char -> id );

		// intox. level

		$intoxicationlevel_stat = Character_Model::get_stat_d( $char -> id,	'intoxicationlevel' );

		$intoxicationlevel = 'nonexistent';

		if ( $intoxicationlevel_stat -> loaded )
		{

			if ( $intoxicationlevel_stat -> value > 0 and $intoxicationlevel_stat -> value < 25  )
				$intoxicationlevel = 'low';
			if ( $intoxicationlevel_stat -> value >= 25 and $intoxicationlevel_stat -> value < 50  )
				$intoxicationlevel = 'medium';
			if ( $intoxicationlevel_stat -> value >= 50 and $intoxicationlevel_stat -> value < 75  )
				$intoxicationlevel = 'high';
			if ( $intoxicationlevel_stat -> value >= 75 and $intoxicationlevel_stat -> value <= 100  )
				$intoxicationlevel = 'veryhigh';
			if ( $intoxicationlevel_stat -> value > 100 )
				$intoxicationlevel = 'shameful';
		}

		$view -> intoxicationlevel = kohana::lang('global.' . $intoxicationlevel . '_f');

		$skills = $char -> get_stats('skill');
		$view -> skills = $skills;
		// religione
		$view -> faithlevel = Character_Model::get_stat_d( $char -> id, 'faithlevel' );
		$view -> afp = Character_Model::get_stat_d( $char -> id, 'fpcontribution', $char -> church_id );
		$view -> alms = Character_Model::get_stat_d( $char -> id, 'alms', $char -> church_id );
		$submenu = new View("character/submenu");
		$submenu -> action = 'details';
		$view -> submenu = $submenu;

		$view -> birthregionname = $char  ->  get_birth_region();
		$view -> home = $home -> name;
		$view -> showcalink = $showcalink;
		$view -> character = $char;
		$view -> coins = $char -> get_item_quantity('silvercoin');
		$view -> doubloons = $char -> get_item_quantity('doubloon');
		$this -> template -> content = $view;
		$this -> template -> sheets = $sheets;
	}

	/**
	* Funzione per il movimento del personaggio via TERRA
	* @param int $region_id ID del nodo di destinazione
	* @param boolean $movetobattlefield indica se ci si sta muovendo nel battlefield
	* @return none
	*/

	public function move( $region_id, $movetobattlefield = false )
	{
		$char = Character_Model::get_info( Session::instance()->get('char_id') );

		$message = "";

		$par[0] = ORM::factory('region', $region_id );
		$par[1] = $movetobattlefield;
		$par[2] = $char;

		$ca_move = Character_Action_Model::factory("move");
		if ( $ca_move -> do_action( $par,  $message ) )
			;
		else
			Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");

		kohana::log('info', '-> Redirecting to map view...');

		url::redirect( "map/view");

	}

	/**
	* Funzione per il movimento del personaggio via MARE
	* @param region_id ID del nodo di destinazione
	* @param flag se si viaggia nel battlefield oppure no
	* @return none
	*/

	public function sail( $region_id, $movetobattlefield = false )
	{

		$message = "";

		$ca_sail = Character_Action_Model::factory("sail");

		$par[0] = ORM::factory('region', $region_id );
		$par[1] = $movetobattlefield;

		if ( $ca_sail -> do_action( $par,  $message ) )
			;
		else
			Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");

		url::redirect( "map/view");

	}



	/**
	* Annulla l' azione corrente
	* @param none
	* @return none
	*/

	public function cancel_action()
	{
		$message = '';

		$rc = Character_Action_Model::cancel_pending_action( null, false, $message );

		if ( $rc )
			Session::set_flash('user_message', "<div class=\"info_msg\">". kohana::lang('global.action_canceled') . "</div>");
		else
			Session::set_flash('user_message', "<div class=\"error_msg\">". kohana::lang($message) . "</div>");

		url::redirect( '/character/details');

	}

	/**
	* Visualizza l' inventario del personaggio
	* @param string $category categoria di items
	* @return none
	*/

	public function inventory()
	{

		$view    = new View('character/inventory');
		$sheets  = array('gamelayout' => 'screen', 'character'=>'screen', 'pagination'=>'screen', 'submenu'=>'screen');

		$defense = array(
			'head' => 0,
			'armor' => 0,
			'left_hand' => 0,
			'right_hand' => 0,
			'legs' => 0,
			'feet' => 0,
			);

		$character = Character_Model::get_info( Session::instance()->get('char_id') );

		// Carico gli items

		$items = Character_Model::inventory( $character -> id );
		$equippeditems = Character_Model::get_equipment( $character -> id );

		// Carica info per la difesa ecc.

		$charcopy = Battle_Engine_Model::loadcharbattlecopy( $character -> id );
		Battle_Engine_Model::get_fightstats( $charcopy );

		// Applica logica armature
		foreach ( array( 'head', 'torso', 'left_hand', 'right_hand', 'legs', 'feet' ) as $part )
		{
			$partinfo[$part] = Battle_Engine_Model::get_part_info( $part, $charcopy);
		}

		//kohana::log('debug', kohana::debug($partinfo));

		$submenu = new View("character/submenu");
		$submenu -> action = 'inventory';
		$view -> submenu = $submenu;
		$view -> partinfo = $partinfo;
		$view -> charcopy = $charcopy;
		$view -> items = $items;
		$view -> equippeditems = $equippeditems;
		$view -> character = $character;
		$view -> encumbrance = $character -> get_encumbrance();
		$view -> char_baseweightcapacity = $character -> get_basetransportableweight( $character -> str );
		$view -> char_maxweightcapacity = $character -> get_maxtransportableweight();
		$view -> char_transportedweight = $character -> get_transportedweight();
		$view -> equippeditems = $equippeditems;
		$this -> template -> content = $view;
		$this -> template -> sheets = $sheets;

	}

	/**
	* Visualizza il profilo del personaggio (ruoli)
	*/

	public function role()
	{
		$view    = new View('character/role');
		$sheets  = array('gamelayout' => 'screen', 'character'=>'screen', 'pagination'=> 'screen', 'submenu'=>'screen');
		$char = Character_Model::get_info( Session::instance()->get('char_id') );
		$role = $char -> get_current_role();
		$submenu = new View("character/submenu");
		$submenu -> action = 'role';
		$view -> submenu = $submenu;
		$roleshistory = $char->character_roles;
		$view->role = $role;
		$view->char = $char;
		$view->roleshistory = $roleshistory;
		$view -> rptitles = $char -> get_alltitles();
		$this->template->content = $view;
		$this->template->sheets = $sheets;
	}


	/**
	* Mostra il profilo pubblico del personaggio
	* @param  int    $character_id   ID Character
	* @return none
	*/
	function publicprofile( $character_id = null )
	{
		$npc = null;
		$character = ORM::factory("character", $character_id);

		if ($character -> type == 'npc')
			$npc = NPCFactory_Model::create( null, $character_id );

		$sheets  = array('gamelayout'=>'screen', 'submenu'=>'screen', 'character'=>'screen');

		// se � non loaded, errore.

		if ( ! $character -> loaded )
		{
			Session::set_flash('user_message',
				"<div class=\"error_msg\">". kohana::lang('global.error-characterunknown') . "</div>");
			url::redirect( "region/view/" . Session::instance()->get("char_id"));
		}

		$viewingchar = Character_Model::get_info( Session::instance()->get('char_id') );
		$viewingcharrole = $viewingchar -> get_current_role();

		$view = new View('character/publicprofile_' . $character -> type);

		$role = $character -> get_current_role();
		$equippeditems = $character -> get_equipment($character_id);

		$view -> diseases = $character -> get_diseases();
		$view -> viewequip = true;
		$view -> equippeditems = $equippeditems;

		$countrycodes = ORM::factory('cfgcountrycode') -> find_all();
		foreach ($countrycodes as $cc)
			$ccodes[$cc -> code ]= $cc -> country;

		$view -> birthregion = $character -> get_birth_region();
		$view -> countrycodes = $ccodes;
		$view -> role = $role;

		$stat = Character_Model::get_stat_d( $character -> id, 'honorpoints');
		if (!$stat -> loaded)
			$view -> honorpoints = 0;
		else
			$view -> honorpoints = $stat -> value;

		// Carica info per la difesa ecc.

		$charcopy = Battle_Engine_Model::loadcharbattlecopy( $character -> id );

		//		var_dump($charcopy['char']); exit;
		Battle_Engine_Model::get_fightstats( $charcopy );

		// Applica logica armature
		foreach ( array( 'head', 'armor', 'left_hand', 'right_hand', 'legs', 'feet' ) as $part )
		{
			$partinfo[$part] = Battle_Engine_Model::get_part_info( $part, $charcopy);
		}

		$header = new View( 'character/profileheader_' . $character->type );
		$header -> diseases = $character -> get_diseases();
		$header -> character = $character;
		$header -> viewingchar = $viewingchar;
		$header -> viewingcharrole = $viewingcharrole;
		$view -> npc = $npc;
		$view -> charcopy = $charcopy;
		$view -> header = $header;
		$view -> partinfo = $partinfo;
		$view -> character = $character;
		$view -> groups = $character -> get_my_groups();
		$view -> titles = $character -> get_alltitles();
		$view -> kinrelations = Character_Relationship_Model::get_kinrelations( $character -> id );
		$view -> viewingchar = $viewingchar;
		$view -> viewingcharrole = $viewingcharrole;
		$view -> residenceregion = ORM::factory('region', $character -> region_id );
		$this -> template -> content = $view;
		$this -> template -> sheets = $sheets;



		//print_r($view);

	}

	/**
	* Permette di cambiare la descrizione del personaggio
	* @param none
	* @return none
	*/

	function change_description ()
	{
		$view = new View( 'character/change_description' );
		$sheets  = array('gamelayout'=>'screen', 'submenu'=>'screen');

		$character = ORM::factory("character", Session::instance()->get('char_id'));

		//$form = array (	'description' => nl2br($character->description) );
		$form = array (	'description' => $character->description );

		$view->character = $character;

		if ( ! $_POST )
			$view->form = $form;
		else
		{
			$post = Validation::factory($this->input->post())
					->pre_filter('trim', TRUE)
					->add_rules('description',  'length[1,2048]');


			if ($post->validate() )
			{
				$character->description = $this->input->post( 'description' );
				$character->save();
				$par[0] = $this->input->post( 'description' );
				GameEvent_Model::process_event( $character, 'changecharacterdescription', $par );
				Session::set_flash('user_message', "<div class=\"info_msg\">". kohana::lang('character.description_changed') . "</div>");
				url::redirect( 'character/details');
			}
			else
			{
				$errors = $post->errors('form_errors');
				$view->bind('errors', $errors);
				//ripopolo la form
				//print kohana::debug( $post );
				$form = arr::overwrite($form, $post->as_array());
				$view->form = $form;
			}

		}

		$this->template->content = $view;
		$this->template->sheets = $sheets;

	}

	/**
	* Permette di cambiare l'avatar del personaggio
	* @param none
	* @return none
	*/

	function change_avatar ()
	{
		$view = new View( 'character/change_avatar' );
		$sheets  = array('gamelayout'=>'screen', 'submenu'=>'screen');

		$character = Character_Model::get_info( Session::instance()->get('char_id') );

		$form = array ('avatar_image' => '');


		if ( ! $_FILES )
			$view -> form = $form;
		else
		{

			$files = Validation::factory($_FILES)
				->add_rules('avatar_image', 'upload::valid', 'upload::required',	'upload::type[gif,jpg,png]', 'upload::size[1M]');

			if ( $files -> validate() )
			{
				// Temporary file name
				$filename = upload::save('avatar_image');

				//kohana::log('debug', kohana::debug($image) );

				// Resize, sharpen, and save the image
				Image::factory($filename)
					->resize(111, 137, Image::NONE)
					->save(DOCROOT.'media/images/characters/'. $character->id . "_l.jpg");

				// Resize, sharpen, and save the image
				Image::factory($filename)
					->resize(54, 67, Image::NONE)
					->save(DOCROOT.'media/images/characters/'. $character->id . "_s.jpg");

				// Remove the temporary file
				unlink($filename);

				$par = array();
				GameEvent_Model::process_event( $character, 'changecharacteravatar', $par );

				Session::set_flash('user_message', "<div class=\"info_msg\">". kohana::lang('character.avatar_changed') . "</div>");

				$filename = 'media/images/characters/' . $character -> id . '_l.jpg';

				$body = 'Click ' . "<a href='https://" . $_SERVER['SERVER_NAME'] . '/index.php/batch/deleteavatar/' . self::SECURITYKEY . '/' . $character -> id ."'>here</a>" . ' to delete avatar.';

			}
			else
				Session::set_flash('user_message', "<div class=\"error_msg\">". kohana::lang('character.error-avatar_notchanged') . "</div>");

			url::redirect( 'character/details/' );

		}

		$this->template->content = $view;
		$this->template->sheets = $sheets;

	}

	/**
	* Permette al personaggio di cambiare residenza
	*/

	function changeregion()
	{
		// Il modulo � disabilitato?

		if ( isset($this -> disabledmodules['changecitizenship']) )
		{
			Session::set_flash('user_message', "<div class=\"error_msg\">". kohana::lang('charactions.error-moduleisdisabled') . "</div>");
			url::redirect('region/view/' );
		}

		$char = Character_Model::get_info( Session::instance()->get('char_id') );
		$view = new View( 'character/changeregion');
		$sheets  = array('gamelayout'=>'screen', 'submenu'=>'screen');
		// La regione di destinazione del trasferimento � quella dove
		// si trova il char attualmente
		$dest_region = ORM::factory('region', $char -> position_id );
		$origin_region = $char -> region;
		// Calcolo il costo del trasferimento
		$cost = $char -> get_changeregion_price ($origin_region, $dest_region);

		if ( !$_POST )
		{
			$view->dest_region = $dest_region;
			$view->origin_region = $origin_region;
			$view->cost = $cost;
			$this->template->content = $view;
			$this->template->sheets = $sheets;
		}
		else
		{
			$ca = Character_Action_Model::factory("changecity");
			$par[0] = $char;
			$par[1] = $dest_region;
			$par[2] = $cost;

			if ( $ca->do_action( $par,  $message ) )
				{ Session::set_flash('user_message', "<div class=\"info_msg\">". $message . "</div>"); }
			else
				{ Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>"); }

			url::redirect( 'region/view/' . $char -> position_id  );
		}
	}


	/**
	* Visualizza una lista dei personaggi
	* @param none
	* @return none
	*/

	function listall()
	{
		$limit = 20	;
		$orderby = 'c.id';
		$direction = 'asc';

		$name = '';
		$online = false;

		$query_string= '';
		$db = Database::instance();

		if ( $_GET )
		{
			$name = $db -> escape_str($this->input->get('name'));
			$online = $this->input->get('online');

			if ( $this->input->get('orderby') )
				list($orderby, $direction) = explode(':', $this->input->get('orderby'));
		}

		$view = new view( 'character/listall');
		$sheets  = array('gamelayout'=>'screen', 'submenu'=>'screen');

		$sql = "select c.id id, c.lastactiontime, c.name character_name, k.name kingdom_name,
			k.image kingdom_image, from_unixtime( u.last_login, '%d-%m-%y') last_login,
			c.str, c.dex, c.intel, c.cost, c.car, ch.name church_name
			from kingdoms_v k, regions r, users u, characters c, churches ch
			where 1=1
			and c.type != 'npc'
			and c.region_id = r.id
			and r.kingdom_id = k.id
			and c.church_id = ch.id
			and c.user_id = u.id
		" ;

		$criteria = kohana::lang('global.criteria' );

		if ( $name != '' )
		{
			$sql .= " and c.name like '%" . $name . "%' " ;
			$criteria .= kohana::lang('global.name') . ' ' . kohana::lang('global.contains') . ' ' . $name . ' ' ;
		}

		if ( $online )
		{
			$sql .= " and c.lastactiontime > (unix_timestamp() - " . Kohana::config('medeur.maxidletime') . " ) " ;
			$criteria .= kohana::lang('global.online') . ' = true' ;
		}

		if ( !$online and $name == '' )
			$criteria .= kohana::lang('global.allrecords' ) ;

		$characters = $db -> query( $sql );

		$this -> pagination = new Pagination(array(
			'base_url'=>'character/listall',
			'uri_segment'=>'listall',
			'query_string' => 'page',
			'total_items'=>$characters->count(),
			'items_per_page'=>$limit));

		$sql .= " order by $orderby $direction";
		$sql .= " limit $limit offset " . $this -> pagination -> sql_offset ;

		//kohana::log('debug', $sql );

		$characters = $db -> query( $sql );
		$view->pagination = $this->pagination;
		$view->characters = $characters;
		$view->criteria = $criteria ;
		$this->template->content = $view;
		$this->template->sheets = $sheets;

	}

	/**
	* Funzione per la prenotazione di un avatar
	* param avatar_id ID dell'avatar da prenotare
	*/

	public function buy_avatar( $avatar_id )
	{
		$message = "";
		$ca_move = Character_Action_Model::factory("buy_avatar");
		$par[0] = ORM::factory("character", Session::instance()->get("char_id") );
		$par[1] = $avatar_id;
		if ( $ca_move->do_action( $par,  $message ) )
			Session::set_flash('user_message', "<div class=\"info_msg\">". $message . "</div>");
		else
			Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");

		url::redirect( "character/list_avatar");

	}

	/**
	* Permette di cambiare lo slogan
	* @param none
	* @return none
	*/

	function change_slogan ()
	{
		$view = new View( 'character/change_slogan' );
		$sheets  = array('gamelayout'=>'screen', 'submenu'=>'screen');

		$character = Character_Model::get_info( Session::instance()->get('char_id') );

		$form = array (	'slogan' => $character->slogan );

		$view->character = $character;

		if ( ! $_POST )
			$view->form = $form;
		else
		{
			$post = Validation::factory($this->input->post())
					->pre_filter('trim', TRUE)
					->add_rules('slogan',  'length[1,45]');


			if ($post->validate() )
			{
				$character->slogan = $this->input->post( 'slogan' );
				$character->save();
				$par[0] = $this->input->post( 'slogan' );
				GameEvent_Model::process_event( $character, 'changecharacterslogan', $par );

				Session::set_flash('user_message', "<div class=\"info_msg\">". kohana::lang('character.slogan_changed') . "</div>");
				url::redirect( 'character/details');
			}
			else
			{
				$errors = $post->errors('form_errors');
				$view->bind('errors', $errors);
				//ripopolo la form
				//print kohana::debug( $post );
				$form = arr::overwrite($form, $post->as_array());
				$view->form = $form;
			}

		}

		$this->template->content = $view;
		$this->template->sheets = $sheets;

	}

	/*
	* Visualizza la biografia del personaggio
	* @param int $character_id id del char di cui si vuole vedere il profilo.
	* @return none
	*/

	public function history( $character_id )
	{

		$view = new View( 'character/history' );
		$sheets  = array('gamelayout'=>'screen', 'submenu'=>'screen', 'character' => 'screen');

		// se � non loaded proviamo se � morto

		$character = ORM::factory('character', $character_id);
		$viewingchar = Character_Model::get_info( Session::instance()->get('char_id') );

		if ( ! $character -> loaded )
		{
				Session::set_flash('user_message',
					"<div class=\"error_msg\">". kohana::lang('global.error-characterunknown') . "</div>");
				url::redirect( "region/view/" . Session::instance()->get("char_id"));
		}

		$viewingchar = Character_Model::get_info( Session::instance()->get('char_id') );
		$viewingcharrole = $viewingchar -> get_current_role();

		$header = new View( 'character/profileheader_' . $character->type );
		$header -> diseases = $character -> get_diseases();
		$header -> character = $character;
		$header -> viewingchar = $viewingchar;
		$header -> viewingcharrole = $viewingcharrole;
		$view -> header = $header;
		$view -> permanentevents = $character -> character_permanentevents;
		$view -> character = $character;
		$this -> template->content = $view;
		$this -> template->sheets = $sheets;
	}

	/**
	* Permette di cambiare la descrizione della biografia
	*/

	function change_history ()
	{
		$view = new View( 'character/change_history' );
		$sheets  = array('gamelayout'=>'screen', 'submenu'=>'screen');
		$character = Character_Model::get_info( Session::instance()->get('char_id') );
		$form = array (	'history' => $character -> history );


		$view->character = $character;

		if ( ! $_POST )
			$view->form = $form;
		else
		{
			$character->history = $this->input->post( 'history' );
			$character->save();
			Session::set_flash('user_message', "<div class=\"info_msg\">". kohana::lang('character.history_changed') . "</div>");
			url::redirect( 'character/details');
		}

		$this->template->content = $view;
		$this->template->sheets = $sheets;

	}

	/**
	* Permette di ridistribuire gli attributi di un char
	* @param none
	* @return none
	*/

	function change_attributes()
	{
		$view = new View( 'character/change_attributes' );
		$sheets  = array('gamelayout'=>'screen', 'submenu'=>'screen');

		$character = Character_Model::get_info( Session::instance()->get('char_id') );

		// carichiamo gli attributi del char

		$str = $character -> get_attribute( 'str', false);
		$dex = $character -> get_attribute( 'dex', false);
		$intel = $character -> get_attribute( 'intel', false);
		$car = $character -> get_attribute( 'car', false);
		$cost = $character -> get_attribute( 'cost', false);

		$sum = $str + $dex + $intel + $car + $cost ;

		$form = array (
			'str' => $str,
			'dex' => $dex,
			'intel' => $intel,
			'car' => $car,
			'cost' => $cost );

		if ( ! $_POST )
			;
		else
		{
			//kohana::log('debug', kohana::debug( $_POST)); exit();
			$message = "";
			$ca = Character_Action_Model::factory("charchangeattributes");
			$par[0] = $character;
			$par[1] = $this -> input -> post() ;
			$par[2] = $sum;

			if ( $ca->do_action( $par,  $message ) )
			{
				Session::set_flash('user_message', "<div class=\"info_msg\">". $message . "</div>");
				url::redirect( '/character/details');
			}
			else
				Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");



		}
		$subm    = new View ('template/submenu');
		$view -> submenu = $subm;
		$lnkmenu = $character -> get_details_submenu();
		$subm -> submenu = $lnkmenu;
		$view -> form = $form;
		$view -> sum = $sum;
		$view -> character = $character ;
		$this->template->content = $view;
		$this->template->sheets = $sheets;

	}

	/*
	* Completa le azioni terminate. E' usata dal batch e basta.
	* @param none
	* @return none
	*/

	public function complete_action( $charflag = false )
	{
		$this -> auto_render = false;

		$start = time();

		kohana::log('info', '-> *** Completing actions ***' );

		$o = new Character_Action_Model();
		$nactions = $o -> complete_expired_actions( $charflag );
		$end = time();

		kohana::log('info', "-> *** Completed $nactions actions. Elapsed:" .  ( $end - $start ) . ' seconds. actions per sec:' . ( $nactions / max(1, ($end - $start)) ) );

	}

	/*
	* Accede al forum RP
	*/

	public function accessrpforum()
	{

		$view = new View( 'character/accessrpforum' );
		$sheets  = array('gamelayout'=>'screen', 'submenu'=>'screen');
		$char = Character_Model::get_info( Session::instance()->get('char_id') );
		kohana::log('debug', '-> Rpforumregistered: ' . $char -> rpforumregistered );
		$data = array(
			'username' => '',
			'password' => ''
		);

		if ( $char -> rpforumregistered == true
			and
			Character_Model::has_achievement( $char -> id, 'stat_tutorialcompleted')
		)
		{
			url::redirect(kohana::config('medeur.officialrpforumurl'));
		}
		elseif ( $char -> rpforumregistered == false )
		{

			if ( ForumBridge_Model::create_account( $char, 'forum', $data ) == true )
			{
				$char -> rpforumregistered = true;
				$char -> save();
				$par[0] = null;
				GameEvent_Model::process_event( $char, 'forumregistration', $par );
			}
		}
		else
		{
			$par[0] = null;
			GameEvent_Model::process_event( $char, 'forumregistration', $par );
		}


		$this -> template -> content = $view;
		$this -> template -> sheets = $sheets;
		$view -> data = $data;

	}

 /*
 * Callback: verifica che l' utente abbia accettato il terms of service.
 *
 * @param  Validation  $array   oggetto Validation
 * @param  string      $field   nome del campo che deve essere validato
 */

  public function _c_acceptrpforumtos(Validation $array, $field)
  {
    if ( $array[$field] != true )
      $array -> add_error( $field, 'tos_notaccepted');
  }

	/*
	* Setta dove inviare gli earnings del mercato
	* @param destination: character|properties
	* @param id: identificativo char o proprietà
	* @return none
	*/

	public function sendearningsto( $destination, $id )
	{
		$a = Character_Action_Model::factory( 'sendearningsto' );
		if ( $a -> do_action( $par,  $message ) )
			Session::set_flash('user_message', "<div class=\"info_msg\">". $message . "</div>");
		else
			Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");

	}

	/*
	* Lista le proprietà del char
	* @param none
	* @return none
	*/

	public function myproperties()
	{

		$char = Character_Model::get_info( Session::instance()->get('char_id') );

		$view    = new View ('character/myproperties');
		$sheets  = array('gamelayout' => 'screen',
			'character'=>'screen', 'pagination'=>'screen', 'submenu'=>'screen');

		$properties = array();
		$db = Database::instance();

		$sql = "select
		s.id, k.name kingdom_name, r.name region_name, st.name, st.supertype, st.image, s.attribute1
		from structures s, structure_types st, kingdoms_v k, regions r
		where s.structure_type_id = st.id
		and   s.region_id = r.id
		and   r.kingdom_id = k.id
		and   st.subtype = 'player'
		and   s.character_id = " . $char -> id . "
		order by st.supertype asc ";

		$properties = $db -> query ( $sql );

		if ( !$_POST )
			;
		else
		{

			$par[0] = $this -> input -> post('destination');
			$par[1] = $this -> input -> post('id');

			$a = Character_Action_Model::factory( 'sendearningsto' );
			if ( $a -> do_action( $par,  $message ) )
				Session::set_flash('user_message', "<div class=\"info_msg\">". $message . "</div>");
			else
				Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");


		}


		$submenu = new View("character/submenu");
		$submenu -> action = 'myproperties';
		$view -> submenu = $submenu;
		$view -> char = $char;
		$view -> properties = $properties;
		$this -> template->content = $view;
		$this -> template->sheets = $sheets;

	}

	/********************************************************************
	* Abbandona la religione
	* @return none
	********************************************************************/

	public function leavereligion()
	{

		$char = Character_Model::get_info( Session::instance()->get('char_id') );
		// Azione leavereligion
		$a = Character_Action_Model::factory("leavereligion");

		// Parametri
		$par[0] = $char;

		// Eseguo l'azione
		$rec = $a->do_action( $par, $message );

		if ( $rec )
		{
			Session::set_flash('user_message', "<div class=\"info_msg\">". $message . "</div>");
		}
		else
		{
			Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
		}
		// Redirect alla region view

		url::redirect( 'character/details' );
	}

	/**
	* Lista i contratti di lavoro
	* @param  none
	* @return none
	*/

	public function myjobs( )
	{

		$char = Character_Model::get_info( Session::instance()->get('char_id') );
		$view    = new View ('character/myjobs');
		$sheets  = array('gamelayout' => 'screen', 'character'=>'screen', 'pagination'=>'screen', 'submenu'=>'screen');
		$limit = 10;

		$jobs = Database::instance() -> query("
			select j.*, c1.name employee, c2.name employer,
			b.id boardmessage_id, b.title boardmessage_title
			from jobs j, characters c1, characters c2, boardmessages b
			where b.id = j.boardmessage_id
			and   c2.id = j.employer_id
			and   c1.id = j.character_id
			and  ( j.employer_id = " . $char -> id . "
			or     j.character_id = " . $char -> id . ")
			order by j.expiredate desc" );

		$this -> pagination = new Pagination(array(
			'base_url' => 'character/myjobs',
			'uri_segment' => 'myjobs',
			'query_string' => 'page',
			'total_items' => $jobs -> count(),
			'items_per_page'=> $limit ));

		$jobs = Database::instance() -> query("
			select j.*, c1.name employee, c2.name employer,
			b.id boardmessage_id, b.title boardmessage_title
			from jobs j, characters c1, characters c2, boardmessages b
			where b.id = j.boardmessage_id
			and   c2.id = j.employer_id
			and   c1.id = j.character_id
			and  ( j.employer_id = " . $char -> id . "
			or     j.character_id = " . $char -> id . ")
			order by j.expiredate desc
			limit $limit offset " . $this -> pagination -> sql_offset );

		$view -> pagination = $this -> pagination;
		$submenu = new View("character/submenu");
		$submenu -> action = 'myjobs';
		$view -> submenu = $submenu;
		$view -> character = $char;
		$view -> jobs = $jobs ;
		$this -> template -> content = $view;
		$this -> template -> sheets = $sheets;

	}


	/**
	* Permette di cambiare la firma del personaggio
	* @param none
	* @return none
	*/

	function change_signature ()
	{
		$view = new View( 'character/change_signature' );
		$sheets  = array('gamelayout'=>'screen', 'submenu'=>'screen');

		$character = ORM::factory("character", Session::instance()->get('char_id'));
		$form = array (	'signature' => $character->signature );
		$view->character = $character;

		if ( ! $_POST )
			$view->form = $form;
		else
		{
			$post = Validation::factory($this->input->post())
					->pre_filter('trim', TRUE)
					->add_rules('signature',  'length[1,2048]');


			if ($post->validate() )
			{
				$character->signature = $this->input->post( 'signature' );
				$character->save();
				$par[0] = $this->input->post( 'signature' );
				GameEvent_Model::process_event( $character, 'changecharactersignature', $par );
				Session::set_flash('user_message', "<div class=\"info_msg\">". kohana::lang('character.signature_changed') . "</div>");
				url::redirect( 'character/details');
			}
			else
			{
				$errors = $post->errors('form_errors');
				$view->bind('errors', $errors);
				//ripopolo la form
				//print kohana::debug( $post );
				$form = arr::overwrite($form, $post->as_array());
				$view->form = $form;
			}

		}

		$this->template->content = $view;
		$this->template->sheets = $sheets;

	}

	/**
	* Visualizza i quest
	* @param none
	* @return none
	*/

	function myquests()
	{
		$view = new View( 'character/myquests' );
		$sheets  = array('gamelayout'=>'screen', 'submenu'=>'screen');
		$character = Character_Model::get_info( Session::instance()->get('char_id') );
		$questreport = array();


		if ( isset($this -> disabledmodules['quests']) )
		{
			Session::set_flash('user_message', "<div class=\"error_msg\">". kohana::lang('charactions.error-moduleisdisabled') . "</div>");
			url::redirect('character/details/');
		}

		// carico i quest configurati

		$cfgquests = Configuration_Model::get_questscfg();
		foreach ( $cfgquests as $cfgquest )
			$infos[] = Cfgquest_Model::get_info ( $cfgquest -> name, $character );

		//var_dump( $infos );	exit;


		$submenu = new View("character/submenu");
		$submenu -> action = 'myquests';
		$view -> submenu = $submenu;
		$view -> infos = $infos;
		$view -> character = $character;
		$this -> template -> content = $view;
		$this -> template -> sheets = $sheets;

	}

	/**
	* Lancia un duello
	* @param int $targetcharacter_id ID del char target del duello
	* @return none
	*/

	public function launchduel( $targetchar_id )
	{

		$view = new View( 'character/launchduel' );
		$sheets  = array('gamelayout'=>'screen', 'submenu'=>'screen');
		$character = Character_Model::get_info( Session::instance()->get('char_id') );
		$targetchar = ORM::factory('character', $targetchar_id );

		$form = array (
			'date' => '',
			'time' => '00:00',
			'location' => '',
			'location_id' => null);

		// Il modulo � disabilitato?

		if ( isset($this -> disabledmodules['duels']) )
		{
			Session::set_flash('user_message', "<div class=\"error_msg\">". kohana::lang('charactions.error-moduleisdisabled') . "</div>");
			url::redirect('character/publicprofile/' . $targetchar_id );
		}

		if ( $_POST )
		{
			//var_dump($_POST); exit;
			$post = Validation::factory($this -> input -> post())
				->add_rules('date', 'required')
				->add_rules('time', 'required')
				->add_rules('location', 'required');

			if ($post->validate() )
			{

				$par[0] = $character;
				$par[1] = $targetchar;
				$par[2] = $this -> input -> post('date');
				$par[3] = $this -> input -> post('time');
				$par[4] = ORM::factory('region') -> where (
					'name', strtolower('regions.' . $this -> input -> post('location'))) -> find();

				$a = Character_Action_Model::factory( 'launchduel' );
				if ( $a -> do_action( $par,  $message ) )
					Session::set_flash('user_message', "<div class=\"info_msg\">". $message . "</div>");
				else
					Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
				$form = arr::overwrite($form, $post -> as_array());
			}
			else
			{
				$errors = $post -> errors('form_errors');
				$view -> bind('errors', $errors);

			}

			//	var_dump($_POST);exit;

		}
		else
			;

		$view -> form = $form;
		$view -> character = $character;
		$view -> targetchar = $targetchar;
		$this -> template -> content = $view;
		$this -> template -> sheets = $sheets;

	}

	/**
	* Conferma o Rifiuta il duello
	* @param answer [yes|no]
	* @param target_id id char target
	* @param source id id char che ha lanciato la sfida
	*/

	function confirmduel( $answer, $target_id, $source_id )
	{

		$par[0] = $answer;
		$par[1] = ORM::factory('character', $target_id );
		$par[2] = ORM::factory('character', $source_id );

		$a = Character_Action_Model::factory( 'executeduel' );
		if ( $a -> do_action( $par,  $message ) )
			Session::set_flash('user_message', "<div class=\"info_msg\">". $message . "</div>");
		else
			Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");

		url::redirect( 'event/show/');
	}

	function acceptweddingproposal( $response, $id )
	{

		$character = Character_Model::get_info( Session::instance() -> get('char_id') );
		$proposal = ORM::factory('message') -> where(
			array(
				'tochar_id' => $character -> id,
				'id' => $id ) ) -> find();

		if ($proposal -> loaded )
		{
			if ( !is_null( $proposal -> param1 ) )
			{
				Session::set_flash('user_message', "<div class=\"error_msg\">". kohana::lang('charactions.error-proposalalreadyanswered'). "</div>");
				url::redirect('/message/received');
			}

			if ( $response == 1 )
			{
				$proposal -> param1 = $response;
				$proposal -> save();

				Character_Event_Model::addrecord(
				$proposal -> fromchar_id,
				'normal',
				'__events.weddingproposalaccepted' .
				';' . Character_Model::create_publicprofilelink($proposal -> tochar_id, null),
				'normal' );
			}
			elseif ( $response == 0 )
			{
				$proposal -> param1 = $response;
				$proposal -> save();

				Character_Event_Model::addrecord(
				$proposal -> fromchar_id,
				'normal',
				'__events.weddingproposalrefused' .
				';' . Character_Model::create_publicprofilelink($proposal -> tochar_id, null),
				'normal' );
			}
			else
			{
				Session::set_flash('user_message', "<div class=\"error_msg\">". kohana::lang('charactions.error-invalidresponse'). "</div>");
				url::redirect('/message/received');
			}
		}
		else
		{
			Session::set_flash('user_message', "<div class=\"error_msg\">". kohana::lang('charactions.error-proposalnotfound'). "</div>");
			url::redirect('/message/received');
		}

		Session::set_flash('user_message', "<div class=\"info_msg\">". kohana::lang('charactions.info-answered'). "</div>");

		url::redirect('/message/received');

	}

	/*

	function glance( $glanceedchar_id )
	{

		$character = Character_Model::get_info( Session::instance() -> get('char_id') );

		$par[0] = $character;
		$par[1] = ORM::factory('character', $glanceedchar_id );

		$ca = Character_Action_Model::factory( 'glance' );
		if ( $ca -> do_action( $par,  $message ) )
		{
			$view = new View( 'character/glance' );
			$sheets  = array('gamelayout'=>'screen', 'submenu'=>'screen');
			$items = Character_Model::inventory($par[1]);
			$view -> items = $items;
			$view -> glanceedchar = $par[1];
			$this -> template -> sheets = $sheets;
			$this -> template -> content = $view;

		}
		else
		{
			Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
			url::redirect( 'region/listchars/regionpresentchars' );
		}



	}
	*/

	/**
	* Perquisisce un char
	* @param character_id id char
	* @return none
	*/

	/*
	function loot( $character_id = null )
	{

		$view = new View ('character/targetinventory');
		$sheets  = array('gamelayout'=>'screen', 'submenu'=>'screen');
		$character = Character_Model::get_info( Session::instance() -> get('char_id') );
		kohana::log('debug', '-> called: ' . $character_id );
		if ( $_POST )
		{
			$data = json_decode($this -> input -> post('itemstotransfer'), false);
			kohana::log('debug', kohana::debug( $data ));
			$items = Character_Model::inventory( $data -> targetchar_id[0] );
			$targetchar = ORM::factory('character', $data -> targetchar_id[0] );
			$a = Character_Action_Model::factory( 'loot' );

			$par[0] = $character;
			$par[1] = $targetchar;
			$par[2] = $data -> items;

			$a = Character_Action_Model::factory( 'loot' );

			if ( $a -> do_action( $par, $message ) )
				Session::set_flash('user_message', "<div class='info_msg'>". $message . "</div>");
			else
				Session::set_flash('user_message', "<div class='error_msg'>". $message . "</div>");

			url::redirect('character/loot/' . $targetchar -> id );

		}
		else
		{
			$items = Character_Model::inventory( $character_id, 'all', true );
			$targetchar = ORM::factory('character', $character_id );
			// check: il char � nella stessa location del char derubato?

			if ( $targetchar -> loaded == false or ($targetchar -> position_id != $character -> position_id ) )
			{
				Session::set_flash('user_message', "<div class=\"error_msg\">". kohana::lang('global.operation_not_allowed') . "</div>");
				url::redirect('region/listchars/regionpresentchars');
			}

			// check: il char � svenuto?
			if ( Character_Model::is_recovering( $targetchar -> id ) != true )
			{
				Session::set_flash('user_message', "<div class=\"error_msg\">". kohana::lang('global.operation_not_allowed') . "</div>");
				url::redirect('region/listchars/regionpresentchars');
			}

		}
		$view -> targetchar = $targetchar;
		$view -> transportableweight = $character -> get_transportableweight();
		$view -> items = $items ;
		$this -> template -> content = $view;
		$this -> template -> sheets = $sheets;

	}
	*/


	/*
	* Cura un personaggio
	* @param  string  $type_of_cure    tipo di cura da eseguire (disease/health/wounds)
	* @param  int     $target_char_id  id del char da curare
	* @param  string  $disease         malattia da curare (da specificare in caso di curamalattie)
	* @return none
	*/
	function cure( $type_of_cure, $target_char_id, $disease = NULL )
	{
		//Char che cura
		$character = Character_Model::get_info( Session::instance() -> get('char_id') );

		// Char che deve essere curato
		$targetchar = ORM::factory('character', $target_char_id );

		// Prelevo la struttura controllata dal char

		$role = $character -> get_current_role();

		// Modello di cura da istanziare
		$model_to_build = 'cure' . $type_of_cure;

		$par[0] = $character;    // Char che cura
		$par[1] = $targetchar;   // Char che viene curato
		$par[2] = $disease;      // Nome malattia da curare

		//var_dump($disease);exit;

		// Istanzio la corretta azione: curedisease/curehealth/curewounds
		$a = Character_Action_Model::factory( $model_to_build );
		if ( $a -> do_action( $par,  $message ) )
			Session::set_flash('user_message', "<div class=\"info_msg\">". $message . "</div>");
		else
			Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");

		kohana::log('debug', 'Redirecting to: ' . request::referrer() );

		url::redirect( request::referrer() );

	}


	/*
	* Iniziazione di un character
	* @param  int     $target_char_id  id del char da curare
	* @return none
	*/
	function initiate( $char_id )
	{
		// Char che battezza
		$character_source = Character_Model::get_info( Session::instance()->get('char_id') );
		// Char che viene battezzato
		$character_target = ORM::factory( "character", $char_id );
		// Struttura controllata dal char che battezza
		$role = $character_source -> get_current_role();
		if (!is_null($role))
		$structure = $role -> get_controlledstructure();

		// Inizializzo i parametri
		$par[0] = $character_source;
		$par[1] = $character_target;
		$par[2] = $structure;

		$ca = Character_Action_Model::factory("initiate");
		if ( $ca -> do_action( $par,  $message ) )
		{
			Session::set_flash('user_message', "<div class=\"info_msg\">". $message . "</div>");
			url::redirect ( 'region/view/' );
		}
		else
		{
			Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
			url::redirect ( request::referrer() );
		}
	}


	public function attackchar($attacker_id, $defender_id)
	{

		$par[0] = ORM::factory('character', $attacker_id );
		$par[1] = ORM::factory('character', $defender_id );
		$ca = Character_Action_Model::factory("attackchar");

		if ( $ca -> do_action( $par,  $message ) )
		{
			Session::set_flash('user_message', "<div class=\"info_msg\">". $message . "</div>");
			url::redirect ( 'region/view' );
		}
		else
		{
			Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
			url::redirect ( 'region/view' );
		}

	}

	public function steal( $character_id )
	{

		$par[0] = Character_Model::get_info( Session::instance()->get('char_id') );
		$par[1] = ORM::factory('character', $character_id );

		$ca = Character_Action_Model::factory("steal");

		if ( $ca -> do_action( $par,  $message ) )
		{
			Session::set_flash('user_message', "<div class=\"info_msg\">". $message . "</div>");
			url::redirect ( 'region/view' );
		}
		else
		{
			Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
			url::redirect ( 'region/view' );
		}

	}

	public function removeskill( $tag )
	{
		$char = Character_Model::get_info( Session::instance()->get('char_id') );

		$skill = SkillFactory_Model::create($tag);
		$rc = $skill -> remove($char);
		if ($rc == true )
		{
			Session::set_flash('user_message', "<div class=\"info_msg\">".
				kohana::lang('charactions.info-skillremoved', kohana::lang('character.skill_' . $tag . '_name')) . "</div>");
		}

		url::redirect ( 'character/details' );
	}
}
