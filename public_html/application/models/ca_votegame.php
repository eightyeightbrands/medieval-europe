<?php defined('SYSPATH') OR die('No direct access allowed.');
class CA_Votegame_Model extends Character_Action_Model
{
	protected $immediate_action = true;
	protected $enabledifrestrained = true;

	/**
	*	Effettua tutti i controlli relativi alla wear, sia quelli condivisi
	* con tutte le action che quelli peculiari della wear
	* @param par array di parametri (par[0] memorizza l'id dell'oggetto che indosso)
	* @param message messaggio di ritorno
	* @return TRUE = azione disponibile, FALSE = azione non disponibile
	*          $messages contiene gli errori in caso di FALSE
	* par[0]: oggetto char 
	* par[1]: oggetto id toplist
	* par[2]: ip address
	* par[3]: numero voti odierni
	*/
	
	protected function check( $par, &$message )
	{ 
		return true; 		
	}

	
	// nessun controllo particolare
	protected function append_action( $par, &$message )
	{}

	public function complete_action( $data)
	{}
	
	public function execute_action ( $par, &$message ) 
	{	
	
		$rndkey = substr( md5 ( time() . rand(1, 10000000) ), 1, 25);
		$toplist = ORM::factory('cfgtoplist', $par[1] );
			
		$v = new Toplistvote_Model();
		$v -> cfgtoplist_id = $toplist -> id;
		$v -> character_id = $par[0] -> id;
		$v -> vkey = $rndkey;
		$v -> status ='sent';
		$v -> ip = $par[2];		
		$v -> urlcalled = str_replace( 'KEY', $rndkey, $toplist -> url );
		
		$v -> timestamp = time();
		$v -> save();	
	
	
		/* 
		* se la toplist non ha reward system, dai il reward subito
		*/
		
		kohana::log('info', "-> Toplist {$toplist -> name} has reward system: " . $toplist -> hasrewardsystem );

		if ( $toplist -> hasrewardsystem == 0  )
		{
			kohana::log('info', '-> Toplist has not reward system, giving reward directly.');
			$t = new Toplistvote_Model;
			$t -> reward_char( $rndkey ); 
		}
		// partial commit;
		Database::instance() -> query("commit");
		
		url::redirect( $v -> urlcalled ); 
		
		return true;
	}
	
}
