<?php defined('SYSPATH') OR die('No direct access allowed.');

class Structure_Event_Model extends ORM
{
  protected $sorting = array('id' => 'desc');

	/**
	* Funzione che aggiunge un annuncio o un evento alla struttura
	* @param structure_id id struttura	
	* @param text Testo dell' annuncio
	* class classe CSS dell' annuncio. Per ora esiste solo evidence
	*/
	
	public function add( $structure_id, $text, $eventclass = null )
	{
		$this->id=null;
		$this->structure_id = $structure_id;
		$this->type = 'normal';
		$this->description = $text;
		$this->timestamp = time();		
		$this->eventclass = $eventclass;
		$this->save();		
		
	}
	
	/**
	* Funzione che aggiunge un annuncio o un evento alla struttura
	* @param structure_id id struttura	
	* @param text Testo dell' annuncio
	* class classe CSS dell' annuncio. Per ora esiste solo evidence
	*/
	
	public function newadd( $structure_id, $text, $eventclass = null )
	{
		$a = new Structure_Event_Model();	
		$a -> id = null;
		$a -> structure_id = $structure_id;
		$a -> type = 'normal';
		$a -> description = $text;
		$a -> timestamp = time();		
		$a -> eventclass = $eventclass;
		$a -> save();		
		
	}

}
