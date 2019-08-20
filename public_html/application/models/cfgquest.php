<?php defined('SYSPATH') OR die('No direct access allowed.');

class Cfgquest_Model extends ORM
{

/**
* Ritorna i Quest da cui il quest chiamante è dipendente
* @param dependencies, stringa
* @return dependencies, stringa con nomi dei quest
*/

function get_questdependencies( $dependencies )
{
	if ( $dependencies == '' )
		return null;
	$questnames = array();
	$ids = explode( ";", $dependencies );
	$questdependencies = ORM::factory('cfgquest') -> in ( 'id', $ids ) -> find_all();
	if ( count($questdependencies) > 0 )
	{
		foreach ( $questdependencies as $questdependency )
		{
			//var_dump( $questdependency); 
			if ( $questdependency -> loaded )
				$questnames[] = kohana::lang( 'quests.' . $questdependency -> path .'_' . $questdependency -> tag . '_name' );
		}	
		$str = implode( ",", $questnames );
	}
	
	return $str;
}

/**
* trova le info di un quest per un char
* mergia le informazioni della tabella cfgquest con quelle
* della classe
* @param $char oggetto char
* @return array info
*/

function get_info( $questname, $char )
{
	// istanzia la classe corretta
	//var_dump($this -> name ); exit;
	
	$quest = QuestFactory_Model::createQuest( $questname );	
	$info = array();	
	$info['id'] = $quest -> get_id() ;
	$info['author_id'] = $quest -> get_author_id() ;	
	$info['path'] = kohana::lang('quests.' . $quest -> get_path() );
	$info['descriptivename'] = kohana::lang('quests.' . $quest -> get_name() . '_name' );		
	$info['name'] = $quest -> get_name();
	$info['steps'] = $quest -> get_steps( $char );	
	$info['currentstep'] = $quest -> get_currentstep( $char );		
	$info['description'] = $quest -> get_description(); 	
	$info['status'] = $quest -> get_status( $char );	
	$info['rewards'] = $quest -> get_rewards( $char );
	$info['started'] = $quest -> get_startdate( $char );
	$info['finished'] = $quest -> get_enddate( $char );
	
	return $info;
}

}
