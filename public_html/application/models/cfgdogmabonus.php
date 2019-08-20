<?php defined('SYSPATH') OR die('No direct access allowed.');

class Cfgdogmabonus_Model extends ORM
{
	
	protected $has_many = array( 'church_dogmabonuses' );
	
	/*
	*  Restituisce tutti i dogmi disponibili
	*  @param   none
	*  @return  $dogmas   array contenente i dogmi
	*/
	function get_all_array()	
	{
		// Carico tutti i dogmi ordinati per
		// Dogma (crescente) e livello (Decrescente)
		$db = Database::instance();
		
		$dogmas = $db -> query
		( 
			"select
				*
			from
				cfgdogmabonuses
			order by
				dogma ASC,
				level DESC"
		);
		
		// Costruisco l'array ottimizzato per
		// la select della form
		$dogma_array = array();
		
		foreach ($dogmas->as_array() as $dogma)
		{
			$dogma_array[$dogma->id] = Kohana::lang('religion.dogmabonus_' . $dogma -> bonus);
		}
		
		return $dogma_array;
	}
}	