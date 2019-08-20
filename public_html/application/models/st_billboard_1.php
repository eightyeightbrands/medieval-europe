<?php defined('SYSPATH') OR die('No direct access allowed.');

class ST_Billboard_1_Model extends Structure_Model
{
	
	public function init()
	{		
		$this -> setCurrentLevel(1);
		$this -> setParenttype('billboard');
		$this -> setSupertype('billboard');
		$this -> setMaxlevel(1);
		$this -> setIsbuyable(false);
		$this -> setIssellable(false);	
		$this -> setStorage(0);		
	}
	
	// Funzione che costruisce i links comuni relativi al pozzo
	// @output: stringa contenente i links
	
	public function build_common_links( $structure, $bonus = false )
	{
		
		$links = parent::build_common_links( $structure );
		$character = Character_Model::get_info( Session::instance()->get('char_id') );				
		if ($structure -> region -> kingdom_id == $character -> region -> kingdom_id )
			$links .= html::anchor( "royalpalace/showwelcomeannouncement", Kohana::lang('character.regentmessage'),
				array('class' => 'st_evidence2')) . "<br/>" ;		
				
		$links .= html::anchor( "character/myquests", Kohana::lang('character.missions'),
			array('class' => 'st_evidence')) . "<br/>" ;		
		
		$links .= html::anchor( "region/kingdomboards/" . $structure -> region -> kingdom_id, Kohana::lang('regionview.list_announcements'),
			array('class' => 'st_common_command')) . "<br/>" ;	
		
		$links .= html::anchor( "boardmessage/index/europecrier", kohana::lang('boardmessage.messagecategoryeuropecrier'),
			array('class' => 'st_common_command')) . "<br/>" ;
			
		$links .= html::anchor( "boardmessage/index/job", kohana::lang('boardmessage.messagecategoryjob'),
			array('class' => 'st_common_command')) . "<br/>" ;
			
		$links .= html::anchor( "boardmessage/index/other", kohana::lang('boardmessage.messagecategoryother'),
			array('class' => 'st_common_command')) . "<br/>" ;	
		
		$links .= html::anchor( "suggestion/index", kohana::lang('boardmessage.messagecategorysuggestion'),
			array('class' => 'st_common_command')) . "<br/>" ;	
				
		
		return $links;
	}
	
	// Funzione che costruisce i links speciali del pozzo
	// @output: stringa contenente i links
	public function build_special_links( $structure )
	{
		$links = "";
		return $links;
	}	

}
