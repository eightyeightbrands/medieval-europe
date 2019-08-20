
<?php defined('SYSPATH') OR die('No direct access allowed.');

class ST_House_Model extends Structure_Model
{		

	public function init()
	{	
		$this -> setIsbuyable(true);
		$this -> setParenttype('house');
		$this -> setSupertype('house');
		$this -> setIssellable(true);		
		$this -> setWikilink('Your_House');
	}
		
		
	public function build_common_links( $structure ) 
	{
		$links = parent::build_common_links( $structure );
		
		$links .= html::anchor( "/structure/info/" . $structure -> id, 
			Kohana::lang('structures_actions.global_info'), array('class' => 'st_common_command')) . "<br/>" ;		
		
		
		return $links;
	}
	
	// Funzione che costruisce i links relativi
	// @output: stringa contenente i links relativi a questa struttura

	public function build_special_links( $structure )
	{			
				
		$links = parent::build_special_links( $structure );
		
		$links .= html::anchor( 
			"/structure/rest/" . $structure -> id, Kohana::lang('global.rest'),
				array('title' => Kohana::lang('global.rest'), 'class' => 'st_special_command'))
			. "<br/>"; 		
		$links .= html::anchor( "structure/sell/". $structure -> id, Kohana::lang('global.sell'),		
			array (
			'class' => 'st_special_command',
			'title' => Kohana::lang('global.sell' ) ));	
		return $links;
	}
	
	/**
	* Return price of structure 
	* @param obj $char Character_Model Personaggio
	* @param obj $regio Region_Model Regione dove la struttura è comprata
	* @return int $price Prezzo di acquisto
	*/
	
	public function getPrice( $char, $region )
	{
		$propertypricemodifier = $region -> get_appliable_tax( $region, 'valueaddedtax', $char );				
		return round( $this -> getBaseprice() * ( 100 + $propertypricemodifier ) / 100, 0) ; 	
	}
	
}
