<?php defined('SYSPATH') OR die('No direct access allowed.');

class Event_Controller extends Template_Controller
{

// Imposto il nome del template da usare
public $template = 'template/gamelayout';

/**
* Visualizza gli eventi del personaggio
* @param none
* @return none
*/

function show()
{
	
	$char = Character_Model::get_info( Session::instance()->get('char_id') );
	
	$view = new View( 'event/show' );
	$sheets  = array('gamelayout' => 'screen', 'character'=>'screen', 'pagination'=>'screen', 'submenu'=>'screen');
	$limit = 10	;
	
	// ogni volta che viene richiamata, cancello gli event piu' vecchi di 6 mesi
	// e setto il timestamp per gli ultimi eventi letti
	
	$db = Database::instance();
	$char -> modify_stat( 
		'lastreadevent', 
		time(), 
		null, 
		null, 
		true );
		
	My_Cache_Model::delete( '-charinfo_' . $char -> id . '_unreadevents' );	
	
	$events = ORM::factory("character_event")->
		where( array( 
			'character_id' => Session::instance()->get("char_id") ) )->find_all();
	
	$this->pagination = new Pagination(array(
		'base_url'=>'event/show',
		'uri_segment'=>'show',
		'style'=>"extended",
		'total_items'=>$events->count(),
		'items_per_page'=>$limit));				
	
	$events = ORM::factory("character_event")->
		where( array( 
			'character_id' => Session::instance()->get("char_id") ) )->find_all($limit, $this->pagination->sql_offset);
	
	$submenu = new View("character/submenu");
	$submenu -> action = 'show';
	$view -> submenu = $submenu;	
	$view->pagination = $this->pagination;
	$view->events = $events;
	$this->template->content = $view;
	$this->template->sheets = $sheets;

}
	
/*
* Funzione che accetta un invito di adesione ad un gruppo
* @param int $group_id ID Gruppo
* @return none
*/

public function accept_invite ($group_id)
{
		$char = Character_Model::get_info( Session::instance()->get('char_id') );
		$group = ORM::factory('group', $group_id);
	
		Group_Model::accept_invite( $char -> id, $group_id );
		
		// Invio la notifica di accettazione al fondatore
		// del gruppo
		
		Character_Event_Model::addrecord( 
				$group->character_id, 
				'normal', 
				'__events.invite_accepted'.
				';'.$char->name.
				';'.$group->name,
				'normal'
				);
				
		Session::set_flash('user_message', "<div class=\"info_msg\">". kohana::lang('events.flash_invite_accepted', $group->name) . "</div>");		
		url::redirect( 'character/details/' );
}

}
