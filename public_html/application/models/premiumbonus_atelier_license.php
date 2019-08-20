<?php defined('SYSPATH') OR die('No direct access allowed.');

class PremiumBonus_Atelier_License_Model extends PremiumBonus_Model
{
	const ATELIERDIRECTORY = 'media/images/wardrobe/atelier/dynamo';
	var $name = '';
	var $info = array();
	var $canbeboughtonce = false;
			
	function __construct()
    {
        $this -> name = 'atelier-license';
	}
	
	function postsaveactions( $char, $cut, $par, &$message )
	{
		
		$filename = 
			self::ATELIERDIRECTORY . '/' . $par[2] . '/' . $par[3] . '/originals/' . strtolower($par[0]). '/' . $par[4];
			
		if ( strpos($par[4], "sets") === FALSE ) 
			$filename .= '.png';		
		else
			$filename .= '.zip';		
		
		kohana::log('debug', "-> Sending $filename to buyer." );
		
		if (strpos( $char -> user -> email, "medieval-europe.eu") !== false )
			$message = 'bonus.atelierlicensecontactsupport';		
		else
		{
			$rc = Utility_Model::mail( 
				$char -> user -> email, 
				"Medieval Europe: delivery Atelier item: " . 
				$par[2] . "-" . $par[3] . "-" . $par[4],
				"Dear customer, you will find attached at this email the item you bought. 
				For any problem regarding this delivery please contact support at https://support.medieval-europe.eu.
				
				<br/>Thank you again for your support.",
				$filename );
			
			if( $rc === false ) {
			   $message = kohana::lang('ca_buyatelieritem.error-deliverynotok');
			   kohana::log('error', '-> Error while sending item to customer email. Filename was: ' 
				. $filename );
			   return false;
			}
		}
		
		parent::postsaveactions($char, $cut, $par, $message);
		
		return true;
	
	}
	
}
