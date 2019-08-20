<?php defined('SYSPATH') OR die('No direct access allowed.');

class ST_Breeding_Model extends Structure_Model
{
	
	public function init()
	{		
		$this -> setCurrentLevel(1);
		$this -> setParenttype('breeding');
		$this -> setMaxlevel(1);
		$this -> setIsbuyable(false);
		$this -> setIssellable(false);						
		$this -> setBaseprice(100);
		$this -> setWikilink('Farms');
		$this->setStorage(600000);

	}

	
	public function build_common_links( $structure, $bonus ) {
	
		$links = parent::build_common_links( $structure );
		
		$links .= html::anchor( "/structure/info/" . $structure -> id, Kohana::lang('structures_actions.global_info'), array('class' => 'st_common_command')) . "<br/>";
		
		return $links;
	}
	
	// Funzione che costruisce i links relativi
	// @output: stringa contenente i links relativi a questa struttura

	public function build_special_links( $structure, $bonus )
	{	
		$links = parent::build_special_links( $structure );
		
		$links .= html::anchor(
			'/breeding/feed/' . $structure->id, kohana::lang('structures.breeding_feed'),
				array( 'class' => 'st_special_command'));
		$links .= "<br/>";
		$links .= html::anchor(
			'/breeding/gather/' . $structure->id, 
				kohana::lang('structures.breeding_gather'),
				array( 'class' => 'st_special_command'));
		$links .= "<br/>";		
		$links .= html::anchor(
			'/breeding/butcher/' . $structure->id, kohana::lang('structures.breeding_butcher'),
				array( 'class' => 'st_special_command'));
				
		return $links;
		
	}

	// Funzione che costruisce i links relativi
	// @output: stringa contenente i links relativi a questa struttura

	public function info_breeding( $structure )
	{
		
		// Recupero la struttura
		$breeding = $structure; 
		// Costruisco le info
		$info = kohana::lang('structures.breeding_numanimals') . ': ' . $breeding->attribute1 . '<br/>';
		
		kohana::log('debug', 'alive animals: ' . $breeding->attribute1 );
		kohana::log('debug', 'feeding status: ' . $breeding->attribute2	 );
				
		$info .= kohana::lang('structures.breeding_statusanimals') . ': ';
		
		if ($breeding->attribute2 > 80 AND $breeding->attribute2 <= 100)			
			$info .= kohana::lang('global.excellent');
		elseif ($breeding->attribute2 > 60 AND $breeding->attribute2 <= 80)
			$info .= kohana::lang('global.good');			
		elseif ($breeding->attribute2 > 40 AND $breeding->attribute2 <= 60)
			$info .= kohana::lang('global.notgood');
		elseif ($breeding->attribute2 > 20 AND $breeding->attribute2 <= 40)
			$info .= kohana::lang('global.poor');			
		else if ($breeding->attribute2 <= 20)
			$info .= kohana::lang('global.verybad');
		else
			;
		$info .= '<br/>';
		$info .= kohana::lang('structures.breeding_days', 20 - $breeding->attribute5) . '<br/>';
	
		
		$info .= kohana::lang('structures.breeding_permissiontogather') . ': ';
		if ($breeding -> attribute3 and $breeding->attribute1 > 0 ) 
		{
			$info .= kohana::lang('global.yes')  . "<br/>";
		
		} else {
			$info .= kohana::lang('global.no') . "<br/>";
		}

		$info .= kohana::lang('structures.breeding_permissiontobutcher') . ': ';
		
		if ( $breeding -> attribute4 == 1 and $breeding->attribute1 > 0 )
		{
			$info .= kohana::lang('global.yes')  . "<br/>";		
		} 
		else 
		{
			$info .= kohana::lang('global.no') . "<br/>";
		}

		return $info;
	}
	
}
