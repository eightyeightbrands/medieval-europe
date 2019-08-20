<?php defined('SYSPATH') OR die('No direct access allowed.');

class IT_Scroll_Model extends Item_Model
{
/**
* Crea i contenuti di uno scroll
* invoca la corretta funzione in base al tipo
* di scroll
* @param none
* @return vettore con in contenuti
*/

function expandcontent()
{

	if ( $this -> cfgitem -> tag == 'scroll_propertylicense' )
		return $this -> ec_scroll_propertylicense( $this ); 
	if ( $this -> cfgitem -> tag == 'scroll_conquerirorder' )
		return $this -> ec_scroll_conquerirorder( $this );
	if ( $this -> cfgitem -> tag == 'scroll_generic' )
		return $this -> ec_scroll_generic( $this ); 	
	if ( $this -> cfgitem -> tag == 'scroll_arrestwarrant' )
		return $this -> ec_scroll_arrestwarrant( $this ); 		
}
	
/**
* Crea i contenuti di uno scroll property license
* @param $item
* @return vettore con in contenuti
*/

function ec_scroll_propertylicense( $item )
{
	
	list( $content['shop_id'], $content['contract_date'], $content['shoptype'] ) = explode( ';' , $item -> param1 );
	
	$shop = ORM::factory('structure', $content['shop_id'] ); 
	
	$content['scrolltitle'] = $item -> cfgitem -> name ; 
	$content['contract_id'] = $item -> id; 
	$content['regionname'] = $shop->region->name ;
	$content['kingdomname'] = $shop->region->kingdom -> get_name()  ;
	
	//kohana::log('debug', kohana::debug ( $content )); 
	
	return $content; 
}

/**
* Crea i contenuti di uno scroll generico
* @param $item
* @return vettore con in contenuti
*/

function ec_scroll_generic( $item )
{
	$content['scroll_id'] = $item->id;
	$content['scroll_date'] = $item->createddate;
	$content['scroll_title'] = $item->param1;
	$content['scroll_body'] = Utility_Model::bbcode($item->param3);
	$content['scroll_signature'] = Utility_Model::bbcode($item->param2);
	return $content; 
}

/**
* Crea i contenuti di uno scroll conquista
* @param $item oggetto scroll
* @return vettore con in contenuti
*/

function ec_scroll_conquerirorder( $item )
{
	
	list( $content['king_id'], $content['captain_id'], $content['captain_name'] , $content['region_id'], $content['region_name'], $content['expirydate'] ) = explode( ';' , $item -> param1 );
	
	$king = ORM::factory('character', $content['king_id'] );
	
	$db = Database::instance();
	$sql = 'select name from regions where id = ' . $content['region_id'] ; 
	$res = $db->query( $sql ); 
	$regionname = $res[0] -> name;
	
	$content['scrolltitle'] = $item -> createddate ; 
	$content['scrolltitle'] = $item -> cfgitem -> name ; 
	$content['contract_id'] = $item -> id; 
	
	$content['kingsignature'] = $king -> signature;
	$content['captainname'] = $content['captain_name'];
	$content['regionname'] = $content['region_name'];
	$content['notes'] = $item -> param2;

	
	
	//kohana::log('debug', kohana::debug ( $content )); 
	
	return $content; 
}

/*
 * effettua controlli particolari per l' oggetto
 * che viene comprato
*/

public function buy_do_proprietary_check( $boughtquantity, &$message )
{
	
	$character = Character_Model::get_info( Session::instance()->get('char_id') ); 	
	
	////////////////////////////////////////////////////////////////////
	// controlli per property_license
	///////////////////////////////////////////////////////////////////
	
	if ( $this -> cfgitem -> tag == 'scroll_propertylicense' )
	{
	
		list( $structure_id, $buydate, $structuretype ) = explode( ';', $this -> param1 );
		
		$structure = StructureFactory_Model::create( null, $structure_id ) ; 
		$seller = ORM::factory( 'character', $this -> seller_id ) ; 		
		
		if ( count( $structure -> item ) > 0 ) 
		{
			$message = 'charactions.sellproperty_propertynotempty';
			return false;							
		}
		
		if ( $structure -> get_pending_actions( $seller -> id ) )
		{
			$message = 'charactions.sellproperty_pendingactionexists';
			return false;							
		}
				
		// il compratore possiede già un negozio?
		if ( $structure -> structure_type -> parenttype == 'shop' ) 
		{
			
			$ownedshops = $character -> count_my_structures( 'shop' );
			
			// se ha già un numero di negozi > di quelli permessi errore.
			
			if ($ownedshops > Kohana::config('medeur.maxshops'))
			{
				$message = kohana::lang( 'ca_buystructure.error-shops_maxlimit');
				return false;
			}
			
			// se ha  un numero di negozi = a quelli permessi errore
			// a meno che la chiesa non abbia il bonus ecc.
			
			else if ($ownedshops == Kohana::config('medeur.maxshops'))		
			{
				
				$churchhasoraetlaborabonus = Church_Model::has_dogma_bonus( $character -> church_id, 'craftblessing');
				$charhasfpcontribution = Character_Model::get_achievement( $character -> id, 'stat_fpcontribution');
				
				if (
					$churchhasoraetlaborabonus == false
					or
					( is_null($charhasfpcontribution) or $charhasfpcontribution['stars'] < 3 )
				)        
				{
					$message = kohana::lang( 'ca_buystructure.error-shops_maxlimit');
					return false;
				}
			}
						
		}	
	}		
		
	if ( $this -> cfgitem -> tag == 'scroll_deliverygoodsnote' )
	{
		// chi la ha (il mercante) deve avere tutti gli oggetti
		// compresi nel contratto.
		
		$items = explode ( ';', $this -> param2, 1 ) ;			
		foreach ( $items as $item )
		{
			list ( $cfgitem_id, $quantity ) = explode ('-', $item );
			$cfgitem = ORM::factory('cfgitem', $cfgitem_id ); 
			if ( Character_Model::has_item( $character->id, $cfgitem->tag, $quantity, true) == false )
			{
				$message = 'charactions.market_deliverygoods_sourcehasnotitems';
				return false;
			}
		}
		
	}
	return true; 
}

/**
* Effettua particolari verifiche su di un oggetto se comprato
* @input par vettore di parametri
* par[0] = struttura mercato
* par[1] = character
* par[2] = item
* par[3] = quantità
* @input/output messaggio da stampare
* @return true o false
*/

public function sell_do_proprietary_check( &$message )
{
	return true;
}


/**
* Effettua particolari azioni su di un oggetto se venduto
* @param message in/out messaggio
* @return true o false
*/

public function buy_do_proprietary_action( &$message)
{

	$character = Character_Model::get_info( Session::instance()->get('char_id') ); 
	$seller = ORM::factory('character', $this->seller_id );
	
	if ( $this -> cfgitem -> tag == 'scroll_propertylicense' ) 
	{
			$property = ORM::factory( 'structure', $this -> param1 ) ; 
			if ( $property -> loaded ) 
				$property -> transfer_ownership( $seller, $character ); 
	}

	return true;

}

/**
* Crea i contenuti di uno scroll arrestwarrant
* @param $item
* @return vettore con in contenuti
*/

function ec_scroll_arrestwarrant( $item )
{	
	
	
	list ( $procedure_id, $source_id, $target_id, $sourcename, $targetname, $createdate ) = explode ( ';',  $item -> param1);
	
	$procedure = ORM::factory('character_sentence', $procedure_id );
	$structure = StructureFactory_Model::create( null, $procedure -> structure_id );
	
	$content['location'] = kohana::lang( $structure -> region -> name ); 
	$content['scrolltitle'] = $item -> cfgitem -> name ; 
	$content['document_id'] = $item -> id;
	$content['procedure_id'] = $procedure -> id;

	$content['document_date'] = $createdate;
	
	$content['sourcename'] = $sourcename;
	$content['targetname'] = $targetname;	
	
	$content['text'] = $procedure -> text;
	$content['trialurl'] = html::anchor( $procedure -> trialurl, 'Link', array( 'target' => 'new' ) ); 	
	
	
	//var_dump( $content ); exit; 
	
	return $content; 
}

}
