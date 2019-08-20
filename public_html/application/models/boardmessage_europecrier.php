<?php defined('SYSPATH') OR die('No direct access allowed.');

class Boardmessage_Europecrier_Model extends Boardmessage_Model
{	
	protected $sorting = array( 'starpoints' => 'desc', 'id' => 'asc');
	
	function is_commandallowed( $command )
	{
		$allowed = array( 'get_view', 'get_limit', 'get_form');
	
		if ( in_array( $command, $allowed ) )
			return true;
		else
			return false;
	
	}
	
	function get_view( $type )
	{
		switch ( $type )
		{
			case 'add': return new View ('boardmessages/add'); break;
			case 'index': return new View ('boardmessages/index_europecrier'); break;
			default: die ('invalid view!');
		}				
	}

	/**
	* Ritorna la query corretta per trovare i record
	* @param params vettore di parametri
	* @return stringa SQL
	*/
	
	function get_sql( $params ) 
	{
		$sql = "select b.* from boardmessages b where
		category = 'europecrier' ";
					
		return $sql ;
		
	}
	
	function get_form ()
	{
		die ('Invalid call to get_form!');
	}
		
	
}