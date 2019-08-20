<?php defined('SYSPATH') OR die('No direct access allowed.');
class My_Cache_Model
{
	/**
	* Aggiunge un oggetto nella cache
	* @param string $tag tag della variabile nella cache
	* @param string $value valore della variabile
	* @param int $duration ore prima che la cache scade
	*/
	
	
	static function set( $tag, $value, $duration = 3600 )
	{
	
		$completetag = Kohana::config( 'medeur.environment' ) . $tag;
		if ( kohana::config( 'medeur.memcache' ) === true )
		{
			//kohana::log('debug', "mycache -> setting $completetag to cache. ");
			Cache::instance() -> set( $completetag, $value, null, $duration ); 
		}
		
		return;
	
	}
	
	
	static function get( $tag )
	{
		
		$completetag = Kohana::config( 'medeur.environment' ) . $tag;
		
			
		if ( kohana::config( 'medeur.memcache' ) === true ) 
		{
			$value = Cache::instance() -> get( $completetag ); 
			//kohana::log('debug', "mycache -> getting [$completetag] from cache. ");
			//kohana::log('debug', "value: " . kohana::debug($value));
		}
		else
			$value = null;
		
		
		return $value;
	
	}
	
	static function delete ( $tag )
	{
		$completetag = Kohana::config( 'medeur.environment' ) . $tag;
		if ( kohana::config( 'medeur.memcache' ) == true )
		{
			//kohana::log('debug', "mycache -> deleting [$completetag] from cache. ");
			Cache::instance() -> delete( $completetag ); 
		}
		
		return;
	
	}
	
	static function delete_all ( )
	{
		
		if ( kohana::config( 'medeur.memcache' ) == true )
			Cache::instance() -> delete_all();
		
		return;
	
	}
	

}
