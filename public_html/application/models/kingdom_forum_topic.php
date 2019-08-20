<?php defined('SYSPATH') OR die('No direct access allowed.');

class Kingdom_Forum_Topic_Model extends Topic_Model
{				
	protected $sorting = array('id' => 'desc');
	protected $belongs_to = array ('kingdom_forum_board');
	
	function index( $char, $board_id, $pagination = false, $limit = null, $offset = null )
	{
		if ($pagination == false)
			$res = ORM::factory('kingdom_board')
				-> where ( 'kingdom_forum_board_id', $board_id ) -> find_all();
		else
			$res = ORM::factory('kingdom_board')
				-> where ( 'kingdom_forum_board_id', $board_id ) -> find_all( $limit, $offset);
		return $res;
		
	}
	
	function read( $char, $topic_id, &$topic )
	{
		
		$topic = ORM::factory('kingdom_forum_topic', $topic_id );		
		if ($topic -> loaded == false)
		{
			$message = 'global.operation_not_allowed';
			return false;						
		}
		
		if ( Kingdom_Forum_Topic_Model::haswriterights( $char, $topic -> kingdom_forum_board -> kingdom ) == false )
		{
			$message = 'global.operationnotallowed';
			return false;
		}
				
		return true;
		
	}
	
	
	function add( $char, $data, &$message )
	{
		//var_dump($data);exit;
		
		$currentboard = ORM::factory('kingdom_forum_board', $data['board_id']);
		if ($currentboard -> loaded == false)
		{			
			$message = 'global.operation_not_allowed';
			return false;			
		}
		
		if ( Kingdom_Forum_Topic_Model::haswriterights( $char, $currentboard -> kingdom ) == false )
		{
			$message = 'global.operationnotallowed';
			return false;
		}
		
		$this -> kingdom_forum_board_id = $data['board_id'];
		$this -> title = $data['title'];
		$this -> body = $data['body'];
		$this -> sticky = 'N';
		$this -> created = date("Y-m-d H:i:s");
		$this -> updated = date("Y-m-d H:i:s");
		$this -> author = $char -> id;
		$this -> save();
		
		// warn all citizens
		
		$citizens = Database::instance() -> query(
		"SELECT c.id  
		FROM characters c, regions r, kingdoms_v k
		WHERE c.region_id = r.id 
		AND   r.kingdom_id = k.id 
		AND   k.id = {$currentboard -> kingdom_id}");
		
		foreach ($citizens as $citizen)
		{
			Character_Event_Model::addrecord(
				$citizen -> id,
				'normal',
				'__events.newkingdom_message' . 				
				';' . html::anchor( 'region/kingdomreplies/' . $currentboard -> kingdom_id . '/' . 
					$this -> id, $data['title'] ),
				'evidence'
			);				
		}
				
		return true;
			
	}
	
	function edit($char, $topic, $data, &$message)
	{
		
		if ($topic -> loaded == false)
		{
			$message = 'global.operation_not_allowed';
			return false;						
		}
		
		if ( Kingdom_Forum_Topic_Model::haswriterights( $char, $topic -> kingdom_forum_board -> kingdom ) == false )
		{
			$message = 'global.operationnotallowed';
			return false;
		}
		
		$topic -> title = $data['title'];
		$topic -> body = $data['body'];
		$topic -> updated = date( "Y-m-d H:i:s");
		$topic -> save();
		
		return true;
		
	}
	
	function haswriterights( $char, $kingdom )
	{
		
		$currentking = $kingdom -> get_king();
		if ( $char -> id != $currentking -> id and !Auth::instance()->logged_in('admin') )				
			return false;
		
		return true;
		
	}
	
	function delete( $char, $topic, &$message)
	{
		
		if ($topic -> loaded == false)
		{
			$message = 'global.operation_not_allowed';
			return false;						
		}
		
		if ( Kingdom_Forum_Topic_Model::haswriterights( $char, $topic -> kingdom_forum_board -> kingdom ) == false )
		{
			$message = 'global.operationnotallowed';
			return false;
		}		
		
		$topic -> status = 'deleted';
		$topic -> updated = date("Y-m-d H:i:s");
		$topic -> save();
		
		return true;
		
	}

}
