<?php defined('SYSPATH') OR die('No direct access allowed.');

class Wardrobe_Approvalrequest_Model extends ORM
{

protected $belongs_to = array( 'character' );
	
/**
* Aggiunge un record
* @param char oggetto char
* @param message errore messaggio
* @return false
*/

function add( $char, &$message )
{
	$c = ORM::factory('wardrobe_approvalrequest') -> where (
	array( 
		'character_id' => $char -> id,
		'status' => 'new' )) -> count_all();

	if ( $c > 0 )
	{
		$message = 'wardrobe.error-unprocessedrequestexists';
		return false;
	}
	
	$o = new Wardrobe_Approvalrequest_Model();
	$o -> character_id = $char -> id;
	$o -> created = time();
	$o -> save();
	
	Utility_Model::mail("donutlord@protonmail.com", 'New Custom approval Request', 'Attenzione: nuova richiesta di approvazione vestiti custom ricevuta!', ".");
	$message = 'wardrobe.approvalrequestsent';
	
	return true;
}

}
