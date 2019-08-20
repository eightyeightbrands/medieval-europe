<?php defined('SYSPATH') OR die('No direct access allowed.');

class Trace_Sink_Model extends ORM
{
	/**
	* Tracks coins and doubloons, in and out.
	* @param string $type currency type (doubloons|silvercoins)
	* @param int $id sourceid
	* @param int $amount amount
	* @param string $reason reason
	* @param string $source source (character|structure|script)
	*/
	
	function add ( $type, $character_id, $amount, $reason, $source = 'character' )
	{
		
		if ( Kohana::config('medeur.tracesinksdoubloons') and $type == 'doubloon' and $amount != 0 )
		{
			
			$ts = ORM::factory('trace_sink');
			$ts -> character_id = $character_id;
			$ts -> type = $type;
			$ts -> amount = $amount;
			$ts -> reason = $reason;	
			$ts -> source = $source;
			$ts -> timestamp = date('Y-m-d H:i:s', time());
			$ts -> save();
		}
		
		if ( Kohana::config('medeur.tracesinkssilvercoins') and $type == 'silvercoin' and $amount != 0 )
		{
			$ts = ORM::factory('trace_sink');
			$ts -> character_id = $character_id;
			$ts -> type = $type;
			$ts -> amount = $amount;
			$ts -> reason = $reason;	
			$ts -> source = $source;
			$ts -> timestamp = date('Y-m-d H:i:s', time());
			$ts -> save();
		}
		
		return;
	}

}
