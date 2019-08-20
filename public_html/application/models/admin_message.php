<?php defined('SYSPATH') OR die('No direct access allowed.');

class Admin_Message_Model extends ORM
{
  protected $sorting = array('id' => 'desc');

	/** 
	* Carica l' ultimo messaggio dell' amministrazione
	*/
	
	function get_last_message()
	{
		$cachetag =  '-global_adminmessage' ;

		kohana::log('debug', "-> Getting $cachetag from CACHE..." ); 				
		
		$message = My_Cache_Model::get( $cachetag );		
				
		if ( is_null( $message ) )
		{
			kohana::log('debug', "-> Getting $cachetag from DB..." ); 
			$message = ORM::factory('admin_message') -> limit ( 1 ) -> find() -> as_array();
			My_Cache_Model::set( $cachetag, $message ); 
		}
		
		return $message;
	}
}
