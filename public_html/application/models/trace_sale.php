<?php defined('SYSPATH') OR die('No direct access allowed.');

class Trace_Sale_Model extends ORM
{

	/**
	* Trace sales
	* cfgitem_id id oggetto
	* quantity quantità comprata
	* price prezzo di acquisto
	*/
	
	function add ( $cfgitem_id, $quantity, $price )
	{
		if ( Kohana::config('medeur.tracesales') )
		{
		
			$timestamp = date('Ym');
			$ts = ORM::factory('trace_sale') -> where 
				( array( 
					'cfgitem_id' => $cfgitem_id,
					'timestamp' => $timestamp )) -> find();
			
			if ( !$ts -> loaded )
			{
				$ts -> cfgitem_id = $cfgitem_id;
				$ts -> timestamp = $timestamp;
			}	
			
			$ts -> quantity += $quantity;
			$ts -> totalprice += $price;
			$ts -> save();
		}
		
		return;
	}

}
