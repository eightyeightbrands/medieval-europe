<?php defined('SYSPATH') OR die('No direct access allowed.');
class CA_AttackIR_Model extends Character_Action_Model
{
	
	protected $immediate_action = false;
	protected $cancel_action = false;
	protected $basetime  = 1;         // 1 ora
	protected $attribute = 'none';       // nessun attributo
	protected $premium   = 'none';       // nessun pacchetto premium
	
	protected $appliedbonuses = array ( 'none' ); // bonuses da applicare
	
	// Effettua tutti i controlli relativi alla wear, sia quelli condivisi
	// con tutte le action che quelli peculiari della wear
	// @input: array di parametri (par[0] memorizza l'id dell'oggetto che indosso)
	// @output: TRUE = azione disponibile, FALSE = azione non disponibile
	//          $messages contiene gli errori in caso di FALSE	
	// par[0]: oggetto gruppo
	// par[1]: oggetto regione che si attacca 
	
	protected function check( $par, &$message )
	{ 						
			
		if ( $par[1] -> loaded == false )
		{ $message = kohana::lang('global.error-regionunknown'); return FALSE; }			
	
		// controllo che la regione non sia stata conquistata
		// dai nativi meno di 15 giorni fa
		
		if ( !is_null($par[1] -> canbeconquered ) and $par[1] -> canbeconquered > time() ) 		
		{ $message = kohana::lang('ca_attackir.error-lastconqueredbynativescooldown'); return FALSE; }			
		
		if ( ! $par[0] -> loaded )
		{ $message = kohana::lang('ca_attackir.mustchooseagroup'); return FALSE; }
		
		// controllo che sia un capitano della guardia
		$role = $par[0] -> character -> get_current_role();
		if ( is_null( $role ) or $role -> tag != 'sheriff' )
		{ $message = kohana::lang('ca_attackir.error-charisnotasheriff'); return FALSE; }
		
		// controllo che il char abbia l' ordine di conquistare
		// la regione in cui è ora.
		
		if ( Battle_Conquer_IR_Model::iscaptainallowedtoattack( $par[0] ) == false )
		{ $message = kohana::lang('ca_attackir.error-noattackorderavailable'); return FALSE; }		
		
		
				
		// non ci devono essere altre battaglie incorso		
		$battle = ORM::factory('battle' ) -> 
			where ( array ( 
				'type' => 'conquer_ir',
				'status' => 'running',
				'dest_region_id' => $par[1] -> id ) ) -> find();		
				
		if ( $battle -> loaded )
		{ $message = kohana::lang('ca_attackir.battlealreadyrunning'); return FALSE; }		
		
		return true;
	}
	
	// nessun controllo particolare
	
	protected function append_action( $par, &$message )
	{	
		
		$bm = new Battle_Model();
		$bm -> source_character_id = $par[0] -> character -> id; 
		
		// trova la capitale del regno del capitano della guardia
		$capital = Kingdom_Model::get_capitalregion( $par[0] -> character -> region -> kingdom_id );
		
		$bm -> source_region_id = $capital -> id;
		$bm -> dest_region_id = $par[1] -> id; 
		$bm -> type = 'conquer_ir';
		$bm -> status = 'running';						
		$bm -> timestamp = time();
		$bm -> save();

		$wdr = new Battle_Report_Model();
		$wdr -> battle_id = $bm -> id;
		$wdr -> save();
		
		$this -> character_id = $par[0] -> character -> id;
		$this -> starttime = time();			
		$this -> status = "running";	
		$this -> endtime = $this -> starttime + $this -> get_action_time( $par[0] -> character );
		$this -> param1 = $par[0] -> id;		
		$this -> param2 = $par[1] -> id;		
		$this -> param3 = $bm -> id;		
		$this -> save();	
		
		$message = kohana::lang('ca_attackir.attack-ok',
			$par[1] -> name); 
		
		return true;
	
	}

	public function complete_action( $data )
	{ 
		
		$character = ORM::factory( 'character', $data -> character_id );		
		$par[0] = ORM::factory( 'group', $data -> param1 );
		$par[1] = ORM::factory( 'region', $data -> param2 );
		$par[2] = ORM::factory( 'battle', $data -> param3 );
		$battletype = Battle_TypeFactory_Model::create( $par[2] -> type );				
		$battletype -> run( $par, $report );
		
		return;
		
	}
	
	public function cancel_action() { return true ; }
	
	public function execute_action() {}
	
	public function get_action_message( $type = 'long') 
	{
		$pending_action = $this -> get_pending_action();
		$message = "";				
		$region = ORM::factory('region', $pending_action -> param2 );
		
		
		if ( $pending_action -> loaded )
		{
			if ( $type == 'long' )					
			$message = '__regionview.attackir_longmessage;__' . $region -> name;
			else
			$message = '__regionview.attackir_shortmessage';
		}
				
		return $message;
		
	}

}
