<?php defined('SYSPATH') OR die('No direct access allowed.');

class ST_Tavern_1_Model extends Structure_Model
{
	public function init()
	{				
		$this -> setIsbuyable(false);
		$this -> setIssellable(false);
		$this -> setParenttype('tavern');
		$this -> setSupertype('tavern');
		$this -> setCurrentLevel(1);
		$this -> setMaxlevel(1);
		$this -> setWikilink('En_US_TheTavern');
	}

	const TAVERNRESTBASICPRICE = 0.28;

	// Funzione che costruisce i links relativi
	// @output: stringa contenente i links relativi a questa struttura
	
	public function build_common_links( $structure )
	{
				
		$links = parent::build_common_links( $structure );
		
		$links .= html::anchor( "/structure/info/" . $structure -> id, Kohana::lang('structures_actions.global_info'), 	array('class' => 'st_common_command')) . "<br/>" ;		
		$links .= html::anchor( "/tavern/game_dice/" . $structure -> id , Kohana::lang('structures_tavern.dice_play'), 	array('title' => Kohana::lang('structures_tavern.dice_play'), 'class' => 'st_common_command')) ."<br/>";
		$links .= html::anchor( "/tavern/rest/". $structure -> id, Kohana::lang('global.rest'), 
			array(	'title' => Kohana::lang('global.rest'), 'class' => 'st_common_command')) ."<br/>";
		
		return $links;
	}

	public function build_special_links( $structure )
	{
	
		// setta i link comuni a tutte le strutture
		
		$links = parent::build_special_links( $structure);
		return $links;
	}
	
	/*
	* stabilisce il prezzo per riposare in taverna
	* @param $character ogg char
	* @param $structure ogg structure
	* @return $data
	*/
	
	function get_price( $character, $structure )
	{
		$data = array( 'baseprice' => 0, 'price' => 0 );
		
		if ( $character -> get_age() < 90 )
		{
			$data['baseprice'] = 0;
			$data['price'] = 0;
		}
		else
		{
			$data['baseprice'] = self::TAVERNRESTBASICPRICE;
			$data['price']= round( self::TAVERNRESTBASICPRICE * 
			(100 + $structure -> region -> 
				get_appliable_tax( $structure -> region, 'valueaddedtax', $character ))/100, 2);			
		}
		
		return $data;
	}
	
}
