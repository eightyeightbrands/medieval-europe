<?php defined('SYSPATH') OR die('No direct access allowed.');

class Job_Model extends ORM
{

	protected $belongs_to = array('character', 'boardmessage');
	
	/**
	* Cancella un job
	* @param  $char oggetto char
	* @param  $job  oggetto job
	* @param  $message messaggio d'errore
	* @return none
	*/
	
	function delete( $char, $job, &$message )
	{
		
		if ( !$char -> loaded or !$job -> loaded or $job -> status != 'active' )
		{	$message = kohana::lang('global.operation_not_allowed'); return false; }
	
		// chi cancella può essere solo l' employer
		
		if ( $char -> id != $job -> employer_id	 )
		{	$message = kohana::lang('global.operation_not_allowed'); return false; }
	
		// toglie l' eventuale grant sulla struttura		
		$employer = ORM::factory( 'character', $job -> employer_id );		
		$employee = ORM::factory( 'character', $job -> character_id );		
		$structure = StructureFactory_Model::create( null, $job -> structure_id );		
		
		if ( $structure -> loaded )
			Structure_Grant_Model::revoke( $structure, $job -> character, $job, 'worker'  ); 

		// eventi
		
		Character_Event_Model::addrecord( 			
			$employer -> id,			
			'normal',
			'__events.jobcanceled' . ';' . 
			$char -> name . ';' .
			$job -> id,
			'normal');
		
		Character_Event_Model::addrecord( 			
			$employee -> id, 			
			'normal',
			'__events.jobcanceled' . ';' . 
			$char -> name . ';' .
			$job -> id,
			'normal');				
		
		// setta il contratto come cancellato
		$job -> status = 'canceled' ;
		$job -> save();
				
		$message = kohana::lang( 'jobs.delete-ok');
		
		return true;
	
	}
	
	/**
	* Chiude un job
	* @param  $char oggetto char
	* @param  $job  oggetto job
	* @param  $message messaggio d'errore
	* @return none
	*/
	
	function close( $char, $job, &$message )
	{
		
		if ( !$char -> loaded or !$job -> loaded or !in_array( $job -> status, array( 'active', 'canceled' ) ) )
		{	$message = kohana::lang('global.operation_not_allowed'); return false; }
	
		// chi cancella può essere solo l' employer
		if ( $char -> id != $job -> employer_id )
		{	$message = kohana::lang('global.operation_not_allowed'); return false; }
	
		// toglie l' eventuale grant sulla struttura		
		
		$employer = ORM::factory( 'character', $job -> employer_id );		
		$employee = ORM::factory( 'character', $job -> character_id );				
		$structure = StructureFactory_Model::create( null, $job -> structure_id );		
		
		if ( $structure -> loaded )
			Structure_Grant_Model::revoke( $structure, $job -> character, $job, 'worker'  ); 
		
		// eventi
		
		Character_Event_Model::addrecord( 			
			$employer -> id, 
			'normal',
		'__events.jobclosed' . ';' . 
			$char -> name . ';' .
			$job -> id,
			'normal');
		
		Character_Event_Model::addrecord( 			
			$employee -> id, 
			'normal',
		'__events.jobclosed' . ';' . 
			$char -> name . ';' .
			$job -> id,
			'normal');				
		
		// setta il contratto come chiuso
		$job -> status = 'closed' ;
		$job -> save();
				
		$message = kohana::lang( 'jobs.close-ok');
		
		return true;
	
	}

	/**
	* Chiude un job
	* @param  $char oggetto char
	* @param  $job  oggetto job
	* @param  $message messaggio d'errore
	* @return none
	*/
	
	function republish( $char, $job, &$message )
	{
		
		if ( !$char -> loaded or !$job -> loaded or !in_array( $job -> status, array( 'canceled' ) ) )
		{	$message = kohana::lang('global.operation_not_allowed'); return false; }
	
		// chi ripubblica può essere solo l' employer
		
		if ( $char -> id != $job -> employer_id )
		{	$message = kohana::lang('global.operation_not_allowed'); return false; }
	
		// si può ripubblicare un annuncio solo se non è scaduto
		
		if ( $job -> expiredate < time() )
		{	$message = kohana::lang('jobs.error-jobisexpired'); return false; }
		
		// cambia lo stato all' annuncio		
		$job -> boardmessage -> status = 'published';
		$job -> boardmessage -> save();
		
		// setta il contratto come chiuso
		$job -> status = 'closed' ;
		$job -> save();
		
		$message = kohana::lang( 'jobs.republished-ok');
		
		return true;
	
	}
	
	/**
	* Accepts a job.
	* @param  obj char employee
	* @param  obj char employer
	* @param  obj announce
	* @param  obj structure where the job will be done
	* @param  string error message
	* @return none
	*/
	
	function accept( $employee, $employer, $announce, $structure, &$message )
	{
		
		// controllo dati
		
		if ( 
			!$employee -> loaded or 
			!$employer -> loaded or 			
			!$announce -> loaded or
			$announce -> category != 'job' or 
			$announce -> status != 'published' )			
		{	$message = kohana::lang('global.operation_not_allowed'); return false; }
		
		if ( !is_null( $structure ) )
			if ( !$structure -> loaded )
				{	$message = kohana::lang('global.operation_not_allowed'); return false; }
				
		// chi accetta può essere solo l' employee
		
		if ( $employee -> id == $announce -> character_id )
		{	$message = kohana::lang('global.operation_not_allowed'); return false; }
		
		if ( !is_null( $structure ) )
		{
			// c'è già un contratto attivo per lo stesso char sulla struttura?
			
			$jobs = ORM::factory('job') -> where( 
				array( 
					'structure_id' => $structure -> id,
					'status' => 'active',
					'expiredate >' => time(),
					'character_id' => $employee -> id,
			)) -> find();
			
			if ( $jobs -> loaded )		
			{$message = kohana::lang('jobs.error-jobonstructurealreadyactive', $jobs -> id ); return false;}

			// c'è già un contratto attivo sulla struttura e nello shop non c'è lo sturdyworkbench?
			
			$activejobsonstructure = ORM::factory('job') -> where( 
				array( 
					'structure_id' => $structure -> id,
					'status' => 'active',
					'expiredate >' => time(),			
			)) -> count_all();
			
			if ( $structure -> structure_type -> parenttype == 'shop' ) 
			{
			
				if ( $activejobsonstructure > 0 and $structure -> contains_item( 'sturdyworkbench', 1 ) == false )
				{$message = kohana::lang('jobs.error-maxemployeecapacityreached' ); return false;}
										
				// ci sono già attivi 5 contratti sulla struttura e nello shop non c'è lo sturdyworkbench?
				
				if ( $activejobsonstructure > 4 and $structure -> contains_item( 'sturdyworkbench', 1 ) == true )
				{$message = kohana::lang('jobs.error-maxemployeecapacityreached' ); return false;}
			}
			else
				if ( $activejobsonstructure > 0 )
				{$message = kohana::lang('jobs.error-maxemployeecapacityreached' ); return false;}
			
		}
								
		// metti il jobpost in stato accepted
		
		$announce -> status = 'active' ;
		$announce -> save();
		
		$job = new Job_Model();
		$job -> character_id = $employee -> id;
		$job -> employer_id = $employer -> id;		
		$job -> status = 'active';
		$job -> boardmessage_id = $announce -> id;
		$job -> wage = $announce -> spare2;
		$job -> hourlywage = $announce -> spare4;
		$job -> expiredate = time() + $announce -> spare1 * ( 24 * 3600 );
		$job -> structure_id = (is_null( $structure) ) ? null : $structure -> id;		
		$job -> save();
		
		// dà una grant sulla struttura
		if ( !is_null( $structure ) )
			Structure_Grant_Model::add( 
				$structure, 
				$employee, 
				$job, 
				'worker', 
				time() + $announce -> spare1 * ( 24 * 3600 ) ); 
		
		// evento a chi assume
		
		Character_Event_Model::addrecord( 			
			$employer -> id, 
			'normal',
			'__events.jobacceptedemployer' . ';' . 
			$employee -> name . ';' . 
			$job -> id,
			'normal');
		
		// evento a chi è assunto
		
		Character_Event_Model::addrecord( 			
			$employee -> id, 
			'normal',
		'__events.jobacceptedemployee' . ';' . 			
			$job -> id . ';' . 
			$employer -> name, 
			'normal');	
		
		$message = kohana::lang( 'jobs.accept-ok');
		
		return true;
	
	}
	
	
	/**
	* Paga oraria
	* @param structure oggetto struttura dove il lavoro è fatto
	* @param employee oggetto char che esegue il lavoro
	* @return none
	*/
	
	public function givehourlywage( $structure, $employee, $workedhours )
	{
		
		if ( Structure_Grant_Model::get_chargrant( $structure, $employee, 'worker' ) == true )
		{
				
			// find the contract 
				
			$contract = ORM::factory( 'job' )
				-> where ( 
					array( 
						'character_id' => $employee -> id,
						'structure_id' => $structure -> id,
						'status' => 'active',
						'expiredate >' => time() ) ) -> find();
			
				
			// get wage tied to contract
				
			$wage = $contract -> hourlywage * $workedhours ;
				
			// check: la struttura ha i soldi sufficienti?
				
			$silvercoins = $structure -> get_item_quantity( 'silvercoin' );
			$coppercoins = $structure -> get_item_quantity( 'coppercoin' );
				
			$totalcoppercoins = $silvercoins * 100 + $coppercoins ;
			$wageincoppercoins = $wage * 100;
			kohana::log('debug', '
			Structure: ' . $structure -> id . ' total coppercoins: ' . $totalcoppercoins . ', wageincopper: ' . 					$wageincoppercoins ); 
				
			if ( $totalcoppercoins < $wageincoppercoins )
			{
				
				Structure_Event_Model::newadd( 
					$structure -> id, 
					'__events.wagefundsmissing' . ';' .
					$employee -> name );				
				
				Character_Event_Model::addrecord( 
					$employee -> id, 
					'normal', 
					'__events.nowagefunds' . 
					';' . $wage .
					';__' . $structure -> structure_type -> name .
					';__' . $structure -> region -> name .
					';' . $structure -> character -> name,
					'normal' );										
				
			}
			else
			{
				$structure ->  modify_coins( - $wage,  'wage', false);
				$employee  ->  modify_coins( $wage, 'wage' );
				
				Structure_Event_Model::newadd( 
					$structure -> id, 
					'__events.wagegiven' . ';' .
					$wage . ';' . 
					$employee -> name );				
				
				Character_Event_Model::addrecord( 
					$employee -> id, 
					'normal', 
					'__events.hourlywagegiven' . 
					';' . $wage .
					';__' . $structure -> structure_type -> name .
					';__' . $structure -> region -> name . 
					';' . $structure -> character -> name, 
					'normal' );										
				
				
			}
		}
	}
}
