<?php defined('SYSPATH') OR die('No direct access allowed.');

class Stats_Produceditem_Model extends ORM
{

protected $belongs_to = array ( 'cfgitem' );
protected $sorting = array('character_id' => 'asc', 'quantity' => 'desc' );

/**
* traccia cosa è stato inviato con una send
* @param from_id: id char che invia
* @param to_id: id char che riceve
* @param cfgitem_id: id cfg dell' item spedito
* @param quantity: quantità dell' item spedito
* @return none
*/

public function trace( $from_id, $to_id, $cfgitem_id, $quantity )
{

	$this->from_id = $from_id ;
	$this->to_id = $to_id;
	$this->cfgitem_id = $cfgitem_id;
	$this->quantity = $quantity;
	$this->timestamp = time();
	$this->save();
	
}

}
