<?php defined('SYSPATH') OR die('No direct access allowed.');

class Religion_1_Controller extends Template_Controller
{
	// Imposto il nome del template da usare
	public $template = 'template/gamelayout';
	private $data = array();
	private $index = array();



	/**
	* Visualizza informazioni sulla religione
	* @param: church_id Id chiesa
	* @return: none
	*/

	function viewinfo( $church_id = null )
	{

		$view = new View ( 'religion_1/viewinfo' );
		$sheets  = array('gamelayout'=>'screen', 'submenu'=>'screen');
		$church = ORM::factory('church', $church_id );
		$output = '';

		if ( $church -> loaded == false )
		{
			Session::set_flash('user_message', "<div class=\"error_msg\">". kohana::lang('religion.nochurchinfo') . "</div>");
			url::redirect('region/info');
		}

		$info = Church_Model::get_info( $church_id );

		// pagina tutti i follower

		$followers = Database::instance()
			-> query("
			SELECT c.id, c.name character_name, k.name kingdom_name
			FROM   characters c, regions r, kingdoms k
			WHERE  c.region_id = r.id
			AND    r.kingdom_id = k.id
			AND    c.church_id = {$church_id}
			ORDER BY character_name asc
			"
		);

		$this -> pagination = new Pagination(array(
			'base_url'=>'religion_1/viewinfo/' . $church_id,
			'uri_segment'=>'viewinfo',
			'style'=> 'extended',
			'query_string' => 'page',
			'total_items' => $followers->count(),
			'items_per_page' => 20));

		//var_dump($this->pagination);exit;

		$sql = "
			SELECT c.id, c.name character_name, k.name kingdom_name
			FROM   characters c, regions r, kingdoms k
			WHERE  c.region_id = r.id
			AND    r.kingdom_id = k.id
			AND    c.church_id = {$church_id}
			ORDER by c.name asc";

		$sql .= " limit 20 offset " . $this -> pagination -> sql_offset ;

		$followers = Database::instance()-> query($sql);

		// Costruisci gerarchia

		$rset = Database::instance() -> query ("
			SELECT s.id, s.character_id, parent_structure_id parent_id, c.name churchname,
			st.type, r.name regionname
			FROM structures s, structure_types st, regions r, churches c
			WHERE s.structure_type_id = st.id
			AND   st.church_id = c.id
			AND   s.region_id = r.id
			AND	st.church_id = " . $church_id
		);

		foreach ( $rset as $row )
		{
			$id = $row -> id;
			$parent_id = ($row -> parent_id === NULL )? "NULL" : $row -> parent_id ;
			$this -> data[$id] = $row;
			$this -> index[$parent_id][] = $id;
		}

		Utility_Model::helper_displaychildnodes(NULL, 0, $this -> index, $this -> data, $output);

		$view -> pagination = $this -> pagination;
		$view -> followers = $followers;
		$view -> info = $info;
		$view -> output = $output ;
		$this -> template->sheets = $sheets;
		$this -> template->content = $view;

	}


	/**
	* Gestisce la gerarchia
	* @param: int $structure_id ID Struttura Che Assegna il Ruolo
	* @return: none
	*/

	function managehierarchy( $structure_id )
	{

		$view = new View ( 'religion_1/manage_hierarchy' );
		$sheets  = array('gamelayout'=>'screen', 'submenu'=>'screen');
		$character = Character_Model::get_info( Session::instance()->get('char_id') );

		if ( !$_POST )
		{

			$structure = StructureFactory_Model::create( null, $structure_id );

			// controllo permessi
			if ( ! $structure -> allowedaccess( $character,
				$structure -> getParentType(), $message,
				'private', 'managehierarchy' ) )
			{
				Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
				url::redirect('region/view/');
			}
		}
		else
		{
			//var_dump( $this -> input -> post() ); exit;
			$structure = StructureFactory_Model::create( null, $this -> input -> post('structure_id') );
			$targetstructure = ORM::factory('structure', $this -> input -> post('targetstructure_id'));

			$par[0] = $character;
			$par[1] = ORM::factory('character') -> where ( 'name' , $this -> input -> post('owner')) -> find();
			$par[2] = $targetstructure -> structure_type -> associated_role_tag;

			if ( $this -> input -> post('revoke') )
			{
				$ca = Character_Action_Model::factory("revokerole");
				$par[3] = $structure;
				$par[4] = null;
			}
			else
			{

				$ca = Character_Action_Model::factory("assignrole");
				$par[3] = $targetstructure -> region;
				$par[4] = $structure;
			}

			if ( $ca -> do_action( $par,  $message ) )
			{
				Session::set_flash('user_message', "<div class=\"info_msg\">". $message . "</div>");
				url::redirect ( 'religion_1/managehierarchy/' . $structure -> id );
			}
			else
			{
				Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
				url::redirect ( 'religion_1/managehierarchy/' . $structure -> id );
			}
		}

		// Carica le sottostrutture. Si pu� dare il controllo solo a
		// strutture completate

		$childstructures = $structure -> get_childstructures();

		$submenu = new View( 'structure/' . $structure -> getSubmenu() );
		$submenu -> id = $structure -> id;
		$submenu -> action = 'managehierarchy';
		$view->submenu = $submenu;

		$view -> childstructures = $childstructures;
		$view -> structure = $structure;
		$this -> template->sheets = $sheets;
		$this -> template->content = $view;
	}


	/**
	* Assegna i titoli e gli incarichi reali ai giocatori
	* @param   int $structure_id id della struttura (caserma)
	* @return none
	*/

	function assign_rolerp( $structure_id )
	{

		$view   = new View ( 'religion_1/assign_rolerp' );
		$sheets = array('gamelayout'=>'screen', 'submenu'=>'screen');
		$character = Character_Model::get_info( Session::instance()->get('char_id') );
		$formroles = array
		(
			'role'        => 'primate',
			'region'      => null,
			'nominated'   => null,
			'place'       => null,
		);

		// Definisco gli incarichi reali
		// assegnabili
		$roles = array
		(
			'primate'          => kohana::lang('global.primate_m'),
			'generalvicar'     => kohana::lang('global.generalvicar_m'),
			'greatinquisitor'  => kohana::lang('global.greatinquisitor_m'),
			'greatalmoner'     => kohana::lang('global.greatalmoner_m'),
			'ambassadorchurch' => kohana::lang('global.ambassador_m'),
			);


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
			var_dump(1); exit;
			$structure = StructureFactory_Model::create( null, $this -> input -> post('structure_id') );

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
				url::redirect('religion_1/assign_rolerp/' . $structure->id);
			}
			else
			{
				Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
				url::redirect ( 'religion_1/assign_rolerp/' . $structure->id );
			}
		}

		$submenu = new View( 'structure/' . $structure -> getSubmenu() );
		$submenu -> id = $structure -> id;
		$submenu -> action = 'assign_rolerp';
		$view->submenu = $submenu;


		$view -> structure = $structure;
		$view -> formroles = $formroles;
		$view -> roles = $roles;
		$this -> template -> content = $view;
		$this -> template -> sheets = $sheets;
	}

	/*
	* Gestisce i dogmi
	* @param:   int $structure_id Id della struttura
	* @return:  none
	*/

	function managedogmas( $structure_id )
	{
		// Carico la vista e i css
		$view = new View ( 'religion_1/managedogmas' );
		$sheets  = array('gamelayout'=>'screen', 'submenu'=>'screen');
		$character = Character_Model::get_info( Session::instance()->get('char_id') );

		if ( !$_POST )
		{
			// Carico la struttura
			$structure = StructureFactory_Model::create( null, $structure_id );

			// Check: permessi struttura
			if ( ! $structure -> allowedaccess( $character, $structure -> getParentType(), $message,
				'private', 'managedogmas' ) )
			{
				Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
				url::redirect('region/view/');
			}

			$church = ORM::factory("church", $structure->structure_type->church_id );
			$dogmas = ORM::factory("cfgdogmabonus") -> get_all_array();

		}
		else
		{

			$structure = StructureFactory_Model::create( null, $structure_id );
			$dogmabonus = ORM::factory("cfgdogmabonus", $this -> input -> post('dogmabonus') );
			$church = ORM::factory("church", $structure->structure_type->church_id );
			//var_dump( $dogmabonus ); exit;

			// Struttura dove viene eseguita l'azione
			$par[0] = $structure;
			// Character che esegue l'azione
			$par[1] = $character;
			// Bonus da aggiungere
			$par[2] = $dogmabonus;
			// FP Costo per il bonus
			$par[3] = $church->get_cost_next_dogma_bonus();
			// Istanzio l'azione del char
			$ca = Character_Action_Model::factory("adddogmabonus");

			if ( $ca -> do_action( $par,  $message ) )
			{
				Session::set_flash('user_message', "<div class=\"info_msg\">". $message . "</div>");
				url::redirect ( 'religion_1/managedogmas/' . $structure -> id );
			}
			else
			{
				Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
				url::redirect ( 'religion_1/managedogmas/' . $structure -> id );
			}
		}

		$submenu = new View( 'structure/' . $structure -> getSubmenu() );
		$submenu -> id = $structure -> id;
		$submenu -> action = 'managedogmas';
		$view->submenu = $submenu;

		$view -> structure = $structure;
		$view -> church = $church;
		$view -> dogmas = $dogmas;
		$this -> template->sheets = $sheets;
		$this -> template->content = $view;
	}

	/*
	 * Report risorse e faith point
	 * @param int $structure_id ID struttura
	 * @return none
	*/

	function resourcereport( $structure_id )
	{

		$view = new View('religion_1/resourcereport' );
		$sheets  = array('gamelayout'=>'screen', 'submenu'=>'screen');
		$structure = StructureFactory_Model::create( null, $structure_id );
		$character = Character_Model::get_info( Session::instance()->get('char_id') );

		// controllo permessi
		if ( ! $structure->allowedaccess( $character, $structure -> getParentType(), $message, 'private', 'resourcereport' ) )
		{
			Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
			url::redirect('region/view/');
		}

		// seleziono tutte le strutture della chiesa

		$churchstructures =
		Database::instance() -> query(
			"
			SELECT s.id, s.character_id owner_id, st.type structure_type, st.name structure_name, r.name region_name
			FROM  structures s, structure_types st, regions r
			WHERE s.structure_type_id = st.id
			AND   s.region_id = r.id
			AND   st.church_id = {$structure-> structure_type -> church_id}"
		);

		$i = 0;

		foreach ($churchstructures as $churchstructure)
		{
			$info[$i]['structure_type'] = $churchstructure -> structure_type;
			$info[$i]['structure_name'] = $churchstructure -> structure_name;
			$info[$i]['region_name'] = $churchstructure -> region_name;
			$info[$i]['owner_id'] = $churchstructure -> owner_id;
			$info[$i]['silvercoins'] = Structure_Model::get_item_quantity_s( $churchstructure -> id, 'silvercoin');
			$stat = Structure_Model::get_stat_d( $churchstructure -> id, 'faithpoints' );
			if (is_null($stat))
				$info[$i]['faithpoints'] = 0;
			else
				$info[$i]['faithpoints'] = $stat -> value;


			$i++;
		}

		asort($info);

		$submenu = new View( 'structure/' . $structure -> getSubmenu() );
		$submenu -> id = $structure -> id;
		$submenu -> action = 'resourcereport';
		$view->submenu = $submenu;


		$view->regions = $structure->region->kingdom->regions;
		$view->structure = $structure;
		$view->info = $info;
		$this->template->content = $view;
		$this->template->sheets = $sheets;

	}

  /**
	* Rimuove un dogma dalla chiesa
	* @param  int    $structure_id   ID della struttura
	* @param  int    $dogma_id       ID del dogma
	* @return none
	*/

public function removedogmabonus( $structure_id, $churchdogma_id )
	{
		//$message = "";
		$char = Character_Model::get_info( Session::instance()->get('char_id') );
		$structure = StructureFactory_Model::create( null, $structure_id );
		$dogma = ORM::factory( 'church_dogmabonus', $churchdogma_id );

		// Check: permessi struttura
		if ( ! $structure -> allowedaccess( $char, $structure -> getParentType(), $message,
			'private', 'removedogma' ) )
		{
			Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
			url::redirect('region/view/');
		}

		// Azione leavereligion
		$a = Character_Action_Model::factory("removedogmabonus");
		// Parametri
		$par[0] = $structure;
		$par[1] = $char;
		$par[2] = $dogma;

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
		url::redirect( 'religion_1/managedogmas/'.$structure->id );
	}
}
