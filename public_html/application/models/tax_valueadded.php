<?php defined('SYSPATH') OR die('No direct access allowed.');

class Tax_ValueAdded_Model
{

	/*
	* Applica la tassa
	* @param par vettore di parametri
	*                par[0] = struttura dove si è acquistato il bene o servizio
	*                par[1] = char che acquista il bene o servizio
	*                par[2] = prezzo totale
	*                par[3] = tipo di vendita, (good, service, license, structure)
	*				 par[4] = oggetto item venduto o null
	*                par[5] = causale 
	*                par[6] = prezzo base del bene
	* @param info informazioni di ritorno
	* @return netto
	*/
	
	public function apply ( $par ) 
	{
		
		/////////////////////////////////////////////////////////////////
		// se si sta vendendo un item, si controlla il flag taxable dato
		// che per alcuni tipi di item non si applica la tassa.				
		///////////////////////////////////////////////////////
		
		//var_dump( $par[0] ); exit;
		
		$vat = Region_Model::get_appliable_tax( 
			$par[0] -> region, 'valueaddedtax', $par[1] );
		
		if ( $par[3] == 'good' )
			if ( $par[4] -> cfgitem -> taxable == false or
				($par[4] -> cfgitem -> tag == 'doubloon' and $par[4] -> price <= 2))			$vat = 0;
				
		// troviamo il palazzo reale e il castello		
		//$structureinstance = ORM::factory('structure', $par[0] -> id );		
		
		$castle = $par[0] -> region -> get_controllingcastle();		
		$royalpalace = $par[0] -> region -> get_controllingroyalpalace();		
		$distributiontax = Kingdom_Model::get_tax( 'distributiontax', $par[0] -> region -> kingdom_id );
				
		//var_dump( $distributiontax ); exit; 		
		kohana::log('debug', '----------  TAXES ---------------');
		kohana::log('debug', '-> Original Price of good: ' . $par[6] );				
		kohana::log('debug', '-> Price at which the good was sold (including tax): ' . $par[2] );		
		kohana::log('debug', '-> Kingdom distribution value for royal palace: ' . $distributiontax -> citizen . '%');		
		kohana::log('debug', '-> Current Goods and Service tax is: ' . $vat . '%');
		
		$total_taxcoins = $par[2] - $par[6];
		$total_net = $par[2] - $total_taxcoins ;
	
		//var_dump( 'Tax: ' . $total_taxcoins . ' Net: ' . $total_net ); exit; 
		
		kohana::log('debug', 'Total tax is : ' . $total_taxcoins );
		kohana::log('debug', 'Total net is : ' . $total_net );
	
		// computiamo l' importo per il castello,il palazzo reale ed il venditore
	
		$castle_coins = round( $total_taxcoins * ( 100 - $distributiontax -> citizen ) / 100, 2 );
		//$royalpalace_coins = round( $total_taxcoins * ( $distributiontax -> citizen ) / 100, 2 );
		$royalpalace_coins = $total_taxcoins - $castle_coins;
		//var_dump( 'Castle coins: ' . $castle_coins . ' RP Coins: ' . $royalpalace_coins ); exit; 
				
		kohana::log('debug', 'Tax coins that will go to royalpalace: ' . $royalpalace_coins );
		kohana::log('debug', 'Tax coins that will go to castle: ' . $castle_coins );				
		kohana::log('debug', 'Net: ' . $total_net );				
		
		// invio soldi		
		
		if ( $royalpalace_coins > 0 )
		{
			$royalpalace -> modify_coins( $royalpalace_coins, $par[5], false );	
			$text = '__events.valueaddedtaxincome;' . $royalpalace_coins .  ';'  . $par[1] -> name . ';' . 
			'__taxes.income_' . $par[5] . ';' . 
			'__' . $par[0] -> region -> name ;
			Structure_Event_Model::newadd( $royalpalace -> id, $text );
		}
		
		if ( $castle_coins > 0 )
		{
			$castle -> modify_coins( $castle_coins, $par[5], false );
			$text = '__events.valueaddedtaxincome;' . $castle_coins .  ';'  . $par[1]->name  . ';' . 
			'__taxes.income_' . $par[5] . ';' . 
			'__' . $par[0] -> region -> name ;
			Structure_Event_Model::newadd( $castle -> id, $text );
		}
		
		return $total_net;
		
	}
	
	
}
