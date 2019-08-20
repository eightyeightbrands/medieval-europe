<?php defined('SYSPATH') OR die('No direct access allowed.');

class CA_Declarewaraction_Model extends Character_Action_Model
{

	protected $immediate_action = true;
	protected $warcost = 0;

	// Effettua tutti i controlli relativi all' azione, sia quelli condivisi
	// con tutte le action che quelli peculiari
	// @input: array di parametri
	// par[0]: oggetto char
	// par[1]: oggetto regione che sferra l'attacco
	// par[2]: tipo attacco
	// par[3]: oggetto regione che riceve l'attacco
	// par[4]: eventuale candidato alla reggenza
	// par[5]: maxattackers
	// par[6]: parameter 1
	// @output: TRUE = azione disponibile, FALSE = azione non disponibile
	//          $message contiene il messaggio di ritorno

	protected function check( $par, &$message )
	{


		// Cooldown su click

		$lastdeclarationsubmit =
		Character_Model::get_stat_d(
			$par[0] -> id,
			'lastdeclarationwarsubmit',
			null,
			null
		);

		if (
			$lastdeclarationsubmit -> loaded !== false
			and
			$lastdeclarationsubmit -> stat1 > time() )
			{
				// sets the cooldown
				$nexttime = mt_rand(60,120);
				Character_Model::modify_stat_d(
					$par[0] -> id,
					'lastdeclarationwarsubmit',
					0,
					null,
					null,
					true,
					time() + $nexttime
				);

				$message = kohana::lang( 'ca_declarewaraction.error-lastsubmitcooldown'); return false;
			}

		// set the cooldown
		$nexttime = mt_rand(60,120);
		Character_Model::modify_stat_d(
				$par[0] -> id,
				'lastdeclarationwarsubmit',
				0,
				null,
				null,
				true,
				time() + $nexttime
		);

		if ( ! parent::check( $par, $message ) )
		{ return false; }

		////////////////////////////////
		// check dati
		////////////////////////////////

		if ( !$par[3] -> loaded )
		{ $message = kohana::lang( 'global.error-regionunknown'); return false;}

		// controllo se la regione � disabled

		if ( $par[3] -> status == 'disabled' )
		{ $message = kohana::lang( 'global.operation_not_allowed'); return false;}

		// Il regno che lancia l' attacco � in guerra?
		$attackingkingdomrunningwars = Kingdom_Model::get_kingdomwars( $par[1] -> kingdom_id, 'running');

		if (count($attackingkingdomrunningwars) == 0 )
		{ $message = kohana::lang( 'ca_declarewaraction.error-attackingkingdomisnotinwar'); return false;}

		// Il regno che � attaccato � in guerra?
		$defendingkingdomrunningwars = Kingdom_Model::get_kingdomwars( $par[3] -> kingdom_id, 'running');
		if (count($defendingkingdomrunningwars) == 0 )
		{ $message = kohana::lang( 'ca_declarewaraction.error-defendingkingdomisnotinwar'); return false;}

		// I due regni, sono nella medesima guerra?

		if ($attackingkingdomrunningwars[0]['war'] -> id  != $defendingkingdomrunningwars[0]['war'] -> id)
		{ $message = kohana::lang( 'ca_declarewaraction.error-defendingkingdomisnotinsamewar'); return false;}

		// Devono essere passati almeno 2 giorni dall' ultima dichiarazione di guerra
		if ( (time() - $attackingkingdomrunningwars[0]['war']->start) < ( kohana::config('medeur.war_cooldownbeforeattacks') * 24 * 3600 ) )
		{ $message = kohana::lang( 'ca_declarewaraction.error-youcannotattackyet'); return false;}

		// La relazione diplomatica con il regno in cui si attacca � ostile?

		$diplomacyrelation = Diplomacy_Relation_Model::get_diplomacy_relation( $par[1] -> kingdom_id, $par[3] -> kingdom_id);
		if ($diplomacyrelation['type'] != 'hostile')
		{ $message = kohana::lang( 'ca_declarewaraction.error-diplomacyrelationisnothostile'); return false;}

		// Conquista capitale: controllo se il regno ha almeno
		// 3 regioni

		if ( $par[2] == 'conquer_r' and $par[3] -> capital and count( $par[3] -> kingdom -> regions ) > 3 )
		{$message = kohana::lang('ca_declarewaraction.mustreduceownedregions');return false;}

		////////////////////////////////
		// Controllo candidato reggenza
		// se si attacca una capitale.
		////////////////////////////////

		if ( $par[2] == 'conquer_r' and $par[3] -> capital and Character_Role_Model::check_eligibility( $par[4], 'king', null, $message ) == false  )
		{ $message = kohana::lang('ca_declarewaraction.kingcandidatenoneligible') . ':' . $message; return false;}

		if ( $par[2] == 'conquer_r' and $par[3] -> capital and $par[4] -> id == $par[0] -> id )
		{ $message = kohana::lang('ca_declarewaraction.error-youcannotbetheregentcandidate') . ':' . $message; return false;}

		// Il regno che dichiara l'attacco non pu� essere sotto attacco.
		$data = null;
		$iskingdomfighting = Kingdom_Model::is_fighting( $par[1] -> kingdom_id, $data ) ;
		if ( $iskingdomfighting == true )
		{ $message = kohana::lang( 'ca_declarewaraction.attacker_isfighting', kohana::lang($par[1]->name) ) ; return false; }

		// Se il regno da attaccare � gi� sotto attacco, non � possi
		// bile dichiarare azioni ostili, se invece sta attaccando, ok.

		$iskingdomfighting = Kingdom_Model::is_fighting( $par[3] -> kingdom_id, $data );
		if ( $iskingdomfighting == true and $data['defending'] == true )
		{	$message = kohana::lang( 'ca_declarewaraction.defender_isfighting',
			kohana::lang( $par[3] -> kingdom -> name) ) ; return false;	}

		// Nella regione c'� un battlefield? Se s� non si pu� attaccare
		// (non si pu� attaccare la stessa regione contemporaneamente

		$battlefield = $par[3] -> get_structure('battlefield');
		if ( !is_null( $battlefield ) )
		{ $message = kohana::lang( 'ca_declarewaraction.error-battlefieldpresent' ) ; return false; }

		// costo: se chi lancia l' attacco � chi ha lanciato la guerra o un suo alleato
		// il costo � 0

		$diplomacyrelationwithkingdomthatdeclaredwar = Diplomacy_Relation_Model::get_diplomacy_relation(
			$par[1] -> kingdom_id , $attackingkingdomrunningwars[0]['war'] -> source_kingdom_id
		);

		if (
			$attackingkingdomrunningwars[0]['war'] -> source_kingdom_id == $par[1] -> kingdom_id
			or
			$diplomacyrelationwithkingdomthatdeclaredwar['type'] == 'allied'
		)
			$this -> warcost = 0;
		elseif ( $par[2] == 'raid' or $par[2] == 'conquer_r' )
			$this -> warcost = Battle_Type_Model::compute_costs();

		if ($this -> warcost > 0 )
		{
			$royalpalace = $par[1] -> kingdom -> get_structure('royalpalace');

			if ( $royalpalace -> get_item_quantity( 'silvercoin' ) < $this -> warcost )
			{
				$message = kohana::lang( 'ca_declarewaraction.error-notenoughfunds', $this -> warcost );
				return false;
			}
		}

		// Non � possibile raidare la reliquia della propria religione.
		/*
		if ( $par[2] == 'raid' and $par[6] == 'relic_' . $par[0] -> church -> name)
		{
			$message = kohana::lang( 'ca_declarewaraction.error-cantraidownrelic');
			return false;
		}
		*/
				
		return true;

	}

	protected function append_action( $par, &$message ) {}

	function complete_action( $data )
	{

		// invio evento al Re del regno attaccato

	}

	public function execute_action ( $par, &$message)
	{

		$defendingregion = ORM::factory('region', $par[3] -> id );
		$role_def = $defendingregion -> get_roledetails( 'king' );

		if (is_null($role_def))
			$king_def = null;
		else
			$king_def = ORM::factory('character', $role_def -> character_id );

		$king_att = ORM::factory('character', $par[0] -> id );
		$role_att = $king_att -> get_current_role();

		//////////////////////////////////////
		// Aggiungo un record in battle
		//////////////////////////////////////

		$attackingkingdomrunningwars = Kingdom_Model::get_kingdomwars( $par[1] -> kingdom_id, 'running');

		$wd = new Battle_Model();
		$wd -> source_character_id = $par[0] -> id;
		$wd -> kingdomwar_id = $attackingkingdomrunningwars[0]['war'] -> id;
		if ( is_null($king_def) )
			$wd -> dest_character_id = null;
		else
			$wd -> dest_character_id = $king_def -> id;
		$wd -> dest_region_id = $par[3] -> id;
		$wd -> source_region_id = $par[1] -> id;
		$wd -> type = $par[2];
		$wd -> maxattackers = $par[5];
		$wd -> param1 = $par[6];

		if ( !is_null( $par[4] ) )
			$wd -> kingcandidate = $par[4] -> id;
		else
			$wd -> kingcandidate = null;

		$wd -> status = 'running';
		$wd -> timestamp = time();
		$wd -> save ();

		$wdr = new Battle_Report_Model();
		$wdr -> battle_id = $wd -> id;
		$wdr -> save();

		//////////////////////////////////////
		// Tolgo i soldi
		//////////////////////////////////////

		$royalpalace = $par[1] -> kingdom -> get_structure('royalpalace');
		$royalpalace -> modify_coins( - $this -> warcost, 'declarewaraction' );

		//////////////////////////////////////
		// Informa il Re difensore
		// in caso di Raid, il Re � informato
		// solo quando il battleraid � pronto
		//////////////////////////////////////

		if ( $par[2] != 'raid' )
		{
			if (!is_null($king_def))
				Character_Event_Model::addrecord(
					$king_def->id,
					'normal',
					'__events.wardeclaration_event2' .
					';__'. $king_att -> region -> kingdom -> get_name()  .
					';__battle.' . $par[2] .
					';__' . $par[3] -> name,
					'evidence'
					);
		}

		//////////////////////////////////////
		// Informa il Re attaccante
		//////////////////////////////////////
		if (!is_null( $king_def ))
			Character_Event_Model::addrecord(
				$king_att->id,
				'normal',
				'__events.wardeclaration_event3' .
				';__battle.' . $par[2] .
				';__'. $par[3] -> name .
				';__'. $king_def -> region -> kingdom -> get_name() ,
				'evidence'
				);

		//////////////////////////////////////
		// Aggiunge evento a banditore
		// eccetto raid
		//////////////////////////////////////

		if ( $par[2] != 'raid' )
		{
			Character_Event_Model::addrecord(
				null,
				'announcement',
				'__events.wardeclaration_announcement2' .
				';__' . $king_att -> region -> kingdom -> get_article() .
				';__' . $king_att -> region -> kingdom -> get_name()  .
				';__' . $par[3] -> kingdom -> get_article3() .
				';__' . $par[3] -> kingdom -> get_name()  .
				';__battle.' . $par[2] .
				';__' . $par[3] -> name,
				'evidence'
			);
		}

		// Schedula una azione per costruire il campo di battaglia

		$a = new Character_Action_Model();
		$a -> character_id = $king_att -> id;
		$a -> action = 'createcdb';
		$a -> blocking_flag = false;
		$a -> cycle_flag = false;
		$a -> status = 'running';

		if ( $par[2] == 'raid' )
			$a -> starttime = time() + 16 * 3600;
		else
			$a -> starttime = time() + 48 * 3600;

		$a -> endtime = $a -> starttime;
		$a -> param1 = $wd -> id;
		$a -> save ();

		$message = sprintf( kohana::lang( 'ca_declarewaraction.wardeclaration_ok'),  kohana::lang($par[3]->name) );

		return true;

	}
}
