<?php defined('SYSPATH') OR die('No direct access allowed.');


class Boardmessage_Job_Model extends Boardmessage_Model
{
	protected $table_name = 'boardmessages';
	protected $belongs_to = array( 'character', 'kingdom' );
	
	/**
	* Factory, torna la classe corretta.
	* @param category
	* @return instanza della classe corretta.
	*/
	
	function is_commandallowed( $command )
	{
		$allowed = array( 'get_view', 'get_limit', 'get_form', 'add', 'get_sql', 'edit', 
		'give_globalvisibility', 'bump_up', 'delete_message', 'report', 'view' );
	
		if ( in_array( $command, $allowed ) )
			return true;
		else
			return false;
	
	}
	
	/**
	* Ritorna la view corretta	
	* @param type tipo di view ('add', 'index' ... )
	* @return oggetto View
	*/
	
	function get_view( $type )
	{
		switch ( $type )
		{
			case 'add': return new View ('boardmessages/add_job'); break;
			case 'edit': return new View ('boardmessages/edit_job'); break;
			case 'index': return new View ('boardmessages/index_job'); break;
			case 'report': return new View ('boardmessages/report'); break;
			case 'view': return new View ('boardmessages/view_job'); break;
			default: die ('invalid view!');
		}
				
	}
	

	/**
	* Ritorna la form corretta
	* @param nessuno
	* @return form corretta
	*/
	
	function get_form( $type )
	{
	
		switch ( $type )
		{
			case 'add':
			case 'edit':
				return array
				(
					'id' => null,
					'category' => 'job',					
					'message' => '',
					'created' => null,
					'validity' => 0,
					'spare1' => null, 
					'spare2' => null, 					
					'spare3' => null,
					'spare4' => null,
					'title' => '',
					'validity' => '2', 
				); break;
			case 'report': 
				return array( 'id' => null, 'reason' => '' ); break;
			default: return null;
		}
		
	}
	
	
	function systemadd( $character_id, $eventtype, $text, $eventclass )
	{
		$m = new Boardmessage_Model();
		$m -> category = $eventtype;
		$m -> character_id = $character_id;
		$m -> kingdom_id = null;
		$m -> title = null;
		$m -> message = $text;
		$m -> validity = 180;
		$m -> status = 'published';
		$m -> visibility = 'global';
		$m -> messageclass = $eventclass;
		$m -> created = time();
		$m -> save();	
	
	}
	
	function get_limit( )
	{
		return 10;
	}
	
	/*
	* Aggiunge post alla boardmessage (metodo usato dal sistema)
	*/
	
	function add( $params, &$message )
	{
		
		//var_dump( $params[0] ); exit; 
		// validate post
		
		if ( strlen($params[0]['title']) < 3 or strlen( $params[0]['title'] ) > 90 )
		{
			$message = kohana::lang ( 'boardmessage.error-title' );
			return false;
		}
		
		if ( strlen($params[0]['message']) <= 0 )
		{
			$message = kohana::lang ( 'boardmessage.error-message' );
			return false;
		}
		
		if ( $params[0]['spare1'] <= 0 or $params[0]['spare1'] > 30 )
		{	
			$message = kohana::lang ( 'boardmessage.error-workduration' );
			return false;
		}
				
		if ( $params[0]['validity'] < 0 or $params[0]['validity'] > 30)
		{	
			$message = kohana::lang ( 'boardmessage.error-validity-1' );
			return false;
		}
		
		
		if ( Character_Model::has_item( $params[1] -> id, 'paper_piece', 1 ) == false )
		{
			$message = kohana::lang('charactions.paperpieceneeded');
			return false;
		}
		
		
			$cost = 0.5 *  $params[0]['validity'];		
			
			if ( $params[1] -> check_money( $cost ) == false )
			{
				$message = kohana::lang('charactions.global_notenoughmoney');
				return false;
			}
			
		

		// se la struttura è popolata, controlliamo che sia di livello >=2
		// e che l' hourly wage sia > 0
		
		if ( $params[0]['spare3'] != 0 )
		{
			
			$structure = StructureFactory_Model::create( null, $params[0]['spare3'] );

			
			if ( ( in_array( $structure -> structure_type, array( 'shop', 'terrain' ) ) and  $structure -> structure_type -> level < 2 )
						and $structure -> structure_type != 'breeding' )
			{
				$message = kohana::lang('boardmessage.error-structurenotlinkable');
				return false;
			}
			
			if ( $params[0]['spare4'] < 0 )
			{
				$message = kohana::lang ( 'boardmessage.error-hourlywage' );
				return false;
			}

		}
		
		// add the message		
		
		$this -> category = $params[0]['category'];
		$this -> character_id = $params[1] -> id;
		$this -> kingdom_id = $params[2] -> kingdom -> id;
		$this -> title = $params[0]['title'];
		$this -> message = $params[0]['message'];
		$this -> validity = $params[0]['validity'];
		$this -> spare1 = $params[0]['spare1'];
		$this -> spare3 = $params[0]['spare3'];
		$this -> spare4 = $params[0]['spare4'];
		$this -> spare5 = 0;
		$this -> spare6 = 0;		
		$this -> created = time();		
		$this -> save();	
		
		My_Cache_Model::set(  '-global-boardmessagelastpost', time() );
		
		// aggiungi un evento
		
		Character_Event_Model::addrecord( 1, 'announcement', 
			'__events.jobposted' .
			';__boardmessage.messagecategory' . $params[0]['category'].
			';' .   $params[0]['title'] .
			';__' . $params[2] -> name . 
			';__' . $params[2] -> kingdom -> get_name(),
			'system' );
		
		
		// rimuovi il pezzo di carta
		
		$item = Item_Model::factory( null, 'paper_piece' );
		$item -> removeitem ( 'character', $params[1] -> id , 1 );
		
		
			
			//togli i soldi
			$params[1] -> modify_coins( - $cost, 'boardvisibility' );
			$params[1] -> save();
		
		
		$message = kohana::lang('boardmessage.add-ok');
		return true;
				
	}
	
	/*
	* Modifica messaggio
	* @params vettore con parametri
	*    params[0] = contenuto della form
	*    params[1] = char che posta il messaggio
	*    params[2] = regione (corrente posizione del personaggio)
	*    params[3] = oggetto messaggio
	*    params[4] = oggetto auth	
	* @params message messaggio di esito
	* @return none
	*/
	
	function edit( $params, &$message )
	{
		
		
		if ( $params[3] -> character_id != $params[1] -> id and ! $params[4] -> logged_in('admin') and !$params[4] -> logged_in('staff') 		
		)
		{
			$message = kohana::lang('global.operation_not_allowed');
			return false;
		}
		
		if ( $params[3] -> character_id != $params[1] -> id and $params[3] -> created < time() - ( 24 * 3600 ) and 		
				! $params[4] -> logged_in('admin') and !$params[4] -> logged_in('staff') 
		)
		{
			$message = kohana::lang('boardmessage.error-edittimeexpired');
			return false;
		}		
		
		if ( strlen($params[0]['title']) < 3 or strlen( $params[0]['title'] ) > 90 )
		{
			$message = kohana::lang ( 'boardmessage.error-title' );
			return false;
		}
		
		if ( $params[0]['spare1'] <= 0 or $params[0]['spare1'] > 30 )
		{	
			$message = kohana::lang ( 'boardmessage.error-workduration' );
			return false;
		}
		
		if ( strlen($params[0]['message']) <= 0 )
		{
			$message = kohana::lang ( 'boardmessage.error-message' );
			return false;
		}		
		
		// se la struttura è popolata, controlliamo che sia di livello >=2
		// e che l' hourly wage sia > 0
		
		if ( $params[0]['spare3'] != 0 )
		{
			
			$structure = StructureFactory_Model::create( null, $params[0]['spare3'] );			
			
			if ( ( in_array( $structure -> structure_type, array( 'shop', 'terrain' ) ) and  $structure -> structure_type -> level < 2 )
						and $structure -> structure_type != 'breeding' )
			{
				$message = kohana::lang('boardmessage.error-structurenotlinkable');
				return false;
			}
			
			if ( $params[0]['spare4'] < 0 )
			{
				$message = kohana::lang ( 'boardmessage.error-hourlywage' );
				return false;
			}

		}
		
		
		// save the message
				
		$params[3] -> title = $params[0]['title'];
		$params[3] -> message = $params[0]['message'];				
		$params[3] -> spare1 = $params[0]['spare1'];				
		$params[3] -> spare3 = $params[0]['spare3'];
		$params[3] -> spare4 = $params[0]['spare4'];
		$params[3] -> save();				
		
		$message = kohana::lang('boardmessage.edit-ok');
		return true;
				
	}
		
	function give_globalvisibility( $params, &$message )
	{
					
		
		if ( $params[2] -> kingdom -> id != $params[0] -> kingdom -> id )
		{
			$message = kohana::lang('boardmessage.givevisibility-error-locationiswrong');
			return false;
		}

		if ( $params[1] -> get_item_quantity( 'doubloon' )  < 2 )
		{
			$message = kohana::lang('bonus.error-notenoughdoubloons');
			return false;
		}
			
		
		// already visible.		
		if ( $params[2] -> visibility == 'global' )
		{
			$message = kohana::lang('boardmessage.bonus-boardmessagegivevisibility-error-messageisalreadyglobal');
			return false;			
		}
		
		// toglie dobloni
		$params[1] -> modify_doubloons ( -2, 'boardvisibility' );
		$params[1] -> save();	
				
		$params[2] -> visibility = 'global';
		$params[2] -> save();
		$message = kohana::lang('boardmessage.givevisibility-ok');
		return true;
		
	}
	
	function bump_up($params, &$message)
	{	
		
		if ( $params[1] -> get_item_quantity( 'doubloon' )  < 1 )
		{
			$message = kohana::lang('bonus.error-notenoughdoubloons');
			return false;
		}
		
		// toglie dobloni
		$params[1] -> modify_doubloons ( -1, 'boardbump' );
		$params[1] -> save();
				
		$params[2] -> starpoints ++;
		$params[2] -> save();
		
		$message = kohana::lang('boardmessage.bumpup-ok');		
		return true;
	}
	
	
	function delete_message( $params, &$message )
	{	
		
		if ( 		
		! $params[3] -> logged_in('admin') and !$params[3] -> logged_in('staff') 
		and
		$params[2] -> character -> id != $params[1] -> id )
		{
			$message = 	kohana::lang('global.operation_not_allowed') ;
			return false;		
		}		
		
		$params[2] -> delete();		
		$message = 	kohana::lang('boardmessage.delete-ok');
		
		return true;
	
	}
	
	function report ( $params, &$message )
	{
	
		if ( strlen($params[2]['reason']) == 0 )
		{
			$message = kohana::lang('boardmessage.error-reason');
			return false;
		}
		
		$subject = 'Messaggio: ' . $params[3] -> id . ' segnalato!';
		$body = 'Il messaggio è stato segnalato da : ' . $params[1] -> name . ', causale: ' . $params[2]['reason'];
		Utility_Model::alertstaff( $subject, $body );

		$message = kohana::lang('boardmessage.reported-ok');
		return true;

	}
	
	/**
	* Incremente il numero di viste di un messaggio
	* @param params vettore di parametri
	* @param message messggio di ritorno	
	* @return none
	*/
	
	function view( $params, &$message )
	{
		if ( 
		! $params[3] -> logged_in('admin') and !$params[3] -> logged_in('staff') and 
		$params[2] -> kingdom -> id != $params[0] -> kingdom -> id and 
		$params[2] -> visibility != 'global' 
		)
		{
			$message = kohana::lang('boardmessage.givevisibility-error-locationiswrong');
			return false;		
		}
		
		$params[2] -> readtimes++;
		$params[2] -> save();
		
		return true;		
		
	}
	
	/**
	* Torna i messaggi non letti di una specifica board
	* @param character oggetto char	
	* @category categoria della board
	* @return numero di messaggi non letti
	
	function countboardnewmessages($character, $category)
	{
		$db = Database::instance();
		$stats = $character -> get_stats( 'boardlastread', $category );
		$lastread = 0;
		if ( is_null ( $stats  ) )
			$lastread = 0;
		else
			$lastread = $stats[0] -> value;			
			
		$sql = "select b.id from boardmessages b
			where (b. created + b.validity * 24 * 3600 ) > unix_timestamp() 
			and   b.category = '$category' 
			and 	b.status = 'published' 
			and		(b.kingdom_id = " . $character -> region -> kingdom -> id . " or b.visibility = 'global') 
			and   ( b.created > " . $lastread . " or b.updated > " . $lastread . " ) "; 
		
		//kohana::log('debug', $sql ); 
		
		$res = $db -> query ( $sql );		
		
		
		return count($res);
		
	}	
	*/
}
