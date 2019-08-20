<?php defined('SYSPATH') OR die('No direct access allowed.');

class ST_Nativevillage_1_Model extends Structure_Model
{
	public function init()
	{		
		$this -> setCurrentLevel(1);
		$this -> setParenttype('nativevillage');
		$this -> setSupertype('nativevillage');
		$this -> setMaxlevel(1);
		$this -> setIsbuyable(false);
		$this -> setIssellable(false);	
		$this -> setWikilink('En_US_nativevillage');				
	}
	
	// Funzione che costruisce i links comuni relativi alla struttura
	// @output: stringa contenente i links relativi a questa struttura
	
	public function build_common_links( $structure )
	{
		

		if ( $structure -> region -> kingdom -> get_name()  == 'kingdoms.kingdom-independent' )
		{
			
			$links = Kohana::lang('structures_nativevillage.info');		
			$links .= '<br/><br/>';
			$links .= html::anchor( "/nativevillage/attack/" . $structure -> id, Kohana::lang('structures_nativevillage.attack'), array('class' => 'st_common_command')). "<br/>";		
			$links .= html::anchor( "/structure/rest/" . $structure -> id, Kohana::lang('global.rest'), array('class' => 'st_common_command')). "<br/>";		
			
		}
		else
		{
			$info = Kohana::lang('structures_nativevillage.infoconquered');
			$links = $info . '<br/><br/>';			
			
			$links .= html::anchor( "/terrain/buy",  Kohana::lang('structures_actions.terrain_buy'), array('class' => 'st_common_command')). "<br/>";
			$links .= html::anchor( "/house/index", Kohana::lang('structures_actions.house_buy'), array('class' => 'st_common_command')). "<br/>";
			$links .= html::anchor( "/shop/index", Kohana::lang('structures_actions.shop_buy'), array('class' => 'st_common_command')). "<br/>";			
			$links .= html::anchor( "/boardmessage/index", Kohana::lang('boardmessage.announcementboard'), array('class' => 'st_common_command')) ;
		}

		return $links;
	}

	// Funzione che costruisce i links speciali relativi alla struttura
	// @output: stringa contenente i links relativi a questa struttura
	public function build_special_links( $structure )
	{
		// Azioni speciali accessibili solo al char che governa la struttura
		$links = parent::build_special_links( $structure );
			
		return $links;
	}
	
}
