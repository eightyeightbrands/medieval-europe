<?php defined('SYSPATH') OR die('No direct access allowed.');

class Gameevent_Controller extends Template_Controller
{
	// Imposto il nome del template da usare	
	
	function index(  )
	{				
		
		$view = new View ('gameevent/index');				
		$gameevents = ORM::factory("cfggameevent") -> find_all();
		$view -> gameevents = $gameevents;
		$this -> template -> content = $view;	
	}
	
	function view( $gameeventid )
	{
		
		$view = new View ('gameevent/view');				
		$gameevent = ORM::factory("cfggameevent", $gameeventid);
		
		$totalsubscriptions = 0;
		$doubloonsjackpot = 0;
		$silvercoinsjackpot = 0;
		
		foreach ( $gameevent -> gameevent_subscription as $subscription )
		{
			$totalsubscriptions ++;
			$doubloonsjackpot += $subscription -> doubloons;
			$silvercoinsjackpot += $subscription -> silvercoins;			
		}
		
		$view -> gameevent = $gameevent;
		$view -> totalsubscriptions = $totalsubscriptions;
		$view -> doubloonsjackpot = round($doubloonsjackpot *80/100);
		$view -> silvercoinsjackpot = round($silvercoinsjackpot*80/100);
		$this -> template -> content = $view;	
		
	}
	
	function subscribe()
	{
		$character = Character_Model::get_info( Session::instance() -> get('char_id') );

		$par[0] = $character;
		$par[1] = $this -> input -> post('cfggameeventid');

		if (null !== $this -> input -> post('subscribedoubloons'))
			$par[2] = 'doubloons';
		else
			$par[2] = 'silvercoins';
		
		$ca = Character_Action_Model::factory("gameeventsubscribe");		
		
		if ( $ca -> do_action( $par,  $message ) )
		{ 				
			Session::set_flash('user_message', "<div class=\"info_msg\">". $message . "</div>");
			url::redirect ( 'gameevent/view/' .  $this -> input -> post('cfggameeventid'));
		}	
		else	
		{ 
			Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>"); 
			url::redirect ( 'gameevent/view/' . $this -> input -> post('cfggameeventid'));
		}	
	}
		
	
	
}
