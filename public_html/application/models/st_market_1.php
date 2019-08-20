<?php defined('SYSPATH') OR die('No direct access allowed.');

class ST_Market_1_Model extends Structure_Model
{
	public function init()
	{		
		$this -> setCurrentLevel(1);
		$this -> setMaxlevel(1);
		$this -> setParenttype('market');					
		$this -> setSupertype('market');
		$this -> setIsbuyable(false);
		$this -> setIssellable(false);	
		$this -> setStorage(100000000);		
		$this -> setWikilink('En_US_TheMarket');		
	}
	
	// Funzione che costruisce i links relativi
	// @output: stringa contenente i links relativi a questa struttura

	public function build_common_links( $structure )
	{
		$links = parent::build_common_links( $structure );
		
		
		$links .= html::anchor( "/structure/info/" . $structure -> id, Kohana::lang('structures_actions.global_info'), array('class' => 'st_common_command')) . "<br/>" ;	
		$links .= html::anchor( "/market/buy/" . $structure -> id, Kohana::lang('structures_actions.market_buy'), 
			array('class' => 'st_common_command')). "<br/>"; 
		$links .= html::anchor( '/market/sell/' . $structure -> id, kohana::lang('structures_actions.market_sell'), 
			array('class' => 'st_common_command')). "<br/>"; 
		
		return $links;
	}

	public function build_special_links( $structure )
	{	
		// setta i link comuni a tutte le strutture		
		$links = parent::build_special_links( $structure );
		return $links;
	}

	
	/*
  * ritorna la lista degli oggetti al mercato
	* @param structure struttura del mercato
	* @return array items
	*/
	
	function get_items( $structure_id )
	{
		$db = Database::instance();
		$items = $db -> query ( "
			select i.*, seller.id seller_id, seller.name seller_name, 
			c.name item_name, c.description, c.tag, c.weight, c.category, c.confiscable 
			from items i, cfgitems c, characters seller 
			where i.cfgitem_id = c.id 
			and   i.seller_id = seller.id
			and structure_id = " . $this -> id ." order by c.tag asc" ); 
		return $items; 	
	}
	
	/*
  * ritorna la lista degli oggetti al mercato
	* @param structure struttura del mercato
	* @return array items
	
	
	function get_items( $structure_id )
	{
		$db = Database::instance();
		$res = $db -> query ( "
			select i.*, seller.id seller_id, seller.name seller_name, 
			c.name item_name, c.description, c.tag, c.weight, c.category, c.confiscable 
			from items i, cfgitems c, characters seller 
			where i.cfgitem_id = c.id 
			and   i.seller_id = seller.id
			and structure_id = " . $this -> id ." order by c.tag asc" );
			
		foreach ( $res as $item )		
		{			
			
			$item -> totalweight = $item -> weight * $item -> quantity;
			$item -> actions = Item_Model::get_actions( $item );
			$items['items'][$item->parentcategory][] = $item;	
			$items['items']['all'][] = $item;
			$items['totalitemsweight'] += $item -> totalweight;			
		}
			
		return $items; 	
	}
	*/
}
