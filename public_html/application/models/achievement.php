<?php defined('SYSPATH') OR die('No direct access allowed.');

class Achievement_Model
{

	/**
	* Compute if the character reaches an achievement.
	*
	* @param string $name achievement name
	* @param string $value stat value (on which the achievement reach is evaluated)
	* @param int $character_id character_id
	* @param string $param1 spare parameter
	* @return  none
	*
	*/	

	static function compute_achievement( $name, $value, $character_id, $param1 = null )
	{
		
		$character = ORM::factory( 'character', $character_id );
		$cfgachievements = Configuration_Model::getcfg_achievements();
		$position = 1;
		
		kohana::log('info', '-> Computing title for stats: [' . $name . '], char: [' . $character_id . '] value: [' . $value . '], param1: [' . $param1 . ']' );		
		
		$stars = 0;
			
		// Arresti
		
		if ( $name == 'stat_arrests' )
		{
			$position = 3;
			if ( $value >= 1 )	$stars = 1;
			if ( $value >= 5 )	$stars = 2;
			if ( $value >= 15 )	$stars = 3;
			if ( $value >= 40 )	$stars = 4;
			if ( $value >= 120 ) $stars = 5;		
		
		}
		
		// Dobloni spesi
		
		if ( $name == 'stat_boughtdoubloons' )
		{
			$position = 1;
			if ( $value >= 900 )	$stars = 1;
			if ( $value >= 10000 )	$stars = 2;
			if ( $value >= 40000 )	$stars = 3;
			if ( $value >= 100000 )	$stars = 4;
			if ( $value >= 400000 ) $stars = 5;		
		
		}
		
		// Duelli
		
		if ( $name == 'stat_bestduelist' )
		{
			$position = 1;
			if ( $value == 100) $stars = 6;
			if ( $value >= 50 and $value < 100) $stars = 5;
			if ( $value >= 25 and $value < 50) $stars = 4;
			if ( $value >= 10 and $value < 25) $stars = 3;
			if ( $value >= 5 and $value < 10) $stars = 2;
			if ( $value >= 1 and $value < 5) $stars = 1;
		}

		// massimizzazione attributi
		
		if ( in_array( $name,
			array( 'stat_intel', 'stat_str', 'stat_dex', 'stat_cost', 'stat_car' ) ) )
		{
			$position = 2;
			$stars = 5;
		}
		

		// Fighting stats
		
		if ( $name == 'stat_fightstats' )
		{
			$perc = round($value/max(1,$param1)*100,0);
			kohana::log('info', "-> Perc: {$perc}");
			$position = 1;
			if ( $value >= 10 and $perc >= 80 )	$stars = 1;
			if ( $value >= 75 and $perc >= 75 )	$stars = 2;
			if ( $value >= 150 and $perc >= 70 )	$stars = 3;
			if ( $value >= 500 and $perc >= 65 )	$stars = 4;
			if ( $value >= 1000 and $perc >= 60 ) $stars = 5;
		
		}
		
		// Faith point accumulati (per religione)
		
		if ( $name == 'stat_battlechampion' )
		{			
				$position = 1;
				if ( $value >= 5 )	$stars = 1;
				if ( $value >= 15 )	$stars = 2;
				if ( $value >= 50 ) $stars = 3;
				if ( $value >= 100 ) $stars = 4;
				if ( $value >= 250 ) $stars = 5;		
		}
		
		// Faith point accumulati (per religione)
		
		if ( $name == 'stat_fpcontribution' )
		{			
				$position = 1;
				if ( $value >= 500 )	$stars = 1;
				if ( $value >= 1500 )	$stars = 2;
				if ( $value >= 5000 )	$stars = 3;
				if ( $value >= 15000 )	$stars = 4;
				if ( $value >= 30000 ) $stars = 5;		
		}
		
		// Honor points
		
		if ( $name == 'stat_honorpoints' )
		{			
				$position = 1;
				
				if ( $value >= 15 )	$stars = 1;
				if ( $value >= 30 )	$stars = 2;
				if ( $value >= 60 )	$stars = 3;
				if ( $value >= 120 ) $stars = 4;
				if ( $value >= 240 ) $stars = 5;		
				
				if ( $value <= -15 ) $stars = -1;
				if ( $value <= -30 ) $stars = -2;
				if ( $value <= -60 ) $stars = -3;
				if ( $value <= -120 ) $stars = -4;
				if ( $value <= -240 ) $stars = -5;
		}
		
		// Game Age
		
		if ( $name == 'stat_gameage' )
		{			
				$position = 1;
				
				if ( $value > 90 )	$stars = 1;
				if ( $value > 365 )	$stars = 2;
				if ( $value > 730 )	$stars = 3;
				if ( $value > 1095 ) $stars = 4;
				if ( $value > 1825 ) $stars = 5;						
		}

		// Game Age
		
		if ( $name == 'stat_watchedvideo' )
		{			
				$position = 1;
				
				if ( $value > 250 )	$stars = 1;
				if ( $value > 500 )	$stars = 2;
				if ( $value > 1000 ) $stars = 3;
				if ( $value > 2000 ) $stars = 4;
				if ( $value > 5000 ) $stars = 5;						
		}
		
		kohana::log('info', "-> Title {$name}, Stars: {$stars}");
		
		if ($stars > 0 )
			Achievement_Model::add( $character, $name, $stars, $position);
		
	}
	
	/**
	* Add an achievement to a character
	*
	* @param obj $character Character
	* @param string $name achievement name	
	* @param int $stars Stars or Levels
	* @param int $position sort order (left to right)
	* @return  none
	*
	*/	
	
	static function add( $character, $name, $stars, $position )
	{
		
		$cfgachievements = Configuration_Model::getcfg_achievements();
		
		kohana::log('info', "-> Adding title {$name}, Stars: {$stars} for Char: {$character->name}...");
		kohana::log('info', '-> Checking for entry with name: ' .  $name  );
		
		$title = null;
			
		// if the title exists, reset this title to current and all the other to NON Current.
		
		$title = ORM::factory('character_title') -> 
			where( array( 
				'character_id' => $character -> id,
				'name' => $name,
				'stars' => $stars )) -> find();
		
		if ( $title -> loaded )
		{				
	
			kohana::log('info', '-> title: ' . $name . ' stars: ' . $stars . ' FOUND for charid: ' . $character -> id . ', doing nothing. ' );
			
			Database::instance() -> query( "update character_titles set current = 'N' 
			where character_id = {$character -> id}
			and   name = '{$name}'");
			
			Database::instance() -> query( "update character_titles set current = 'Y' 
			where character_id = {$character -> id}
			and   name = '{$name}'
			and   stars = {$stars}");				
			
		}
		else
		{
			
			kohana::log('info', '-> title: ' . $name . ' stars: ' . $stars . ' NOT FOUND for charid: ' . $character -> id . ' adding... ' );
		
			Database::instance() -> query( "update character_titles set current = 'N' 
			where character_id = {$character -> id}
			and   name = '{$name}'");
		
			$character_title = new Character_Title_Model();
			$character_title -> character_id = $character -> id ;
			$character_title -> cfgachievement_id = $cfgachievements[$name][$stars] -> id;
			$character_title -> name = $name;
			$character_title -> stars = $stars;
			$character_title -> position = $position;
			$character_title -> timestamp = date("Y-m-d H:i:s");
			$character_title -> current = 'Y';
			$character_title -> save();
						
			// give points, only if not already done.
			
			$pointsalreadyrewarded = Character_Model::get_stat_d(
				$character -> id,
				'pointrewarded',
				$name,
				$stars);
				
			//var_dump($cfgachievements[$name][$stars]);exit;
			if ( ! $pointsalreadyrewarded -> loaded or $pointsalreadyrewarded -> value == 0)
			{
				
				Character_Model::modify_stat_d( 
					$character -> id, 
					'pointrewarded',
					1,
					$name,
					$stars,
					true );
				
				// event
			
				Character_Event_Model::addrecord( 
					$character -> id, 
					'normal',  
					'__events.gottitle' .
					';__titles.' . $name . '_' . $stars,
					'evidence'
				);
				
				// Recompute score
				kohana::log('info', '-> Recomputing score...' );
				Achievement_Model::computescore( $character -> id );		
				
			}
			else
				Character_Event_Model::addrecord( 
					$character -> id, 
					'normal',  
					'__events.gottitleandpoints' .
					';__titles.' . $name . '_' . $stars . 
					';' . $cfgachievements[$name][$stars] -> score,
					'evidence'
				);				
		
		}		
		
		My_Cache_Model::delete('-charactertitles');	
		My_Cache_Model::delete('-achievement_' . $character -> id . '_' . $name); 	
	
	}
	
	/**
	* Delete an achievement
	*
	* @param int $character_id Id of character that loses the achievement
	* @param string $tag Name (tag of the achievement)
	* @param string $mode (all|single) Remove ALL or a single achievement 
	* @return none
	*/	
	
	public function remove( $character_id, $tag, $mode = 'single' )
	{		
			
		$cfgachievements = Configuration_Model::getcfg_achievements();
		
		// find current achievement of char
		
		$currentachievement = Character_Model::get_achievement( $character_id, $tag );			
		
		if (!is_null($currentachievement ))
		{
			$cfgachievementstodelete = array( $cfgachievements[$currentachievement['name']][$currentachievement['stars']]);		
			foreach ($cfgachievementstodelete as $cfgachievementtodelete)
			{
				
				Kohana::log('info',"-> Removing achievement: {$cfgachievementtodelete -> tag}, level: {$cfgachievementtodelete -> level} for char: {$character_id}.");
			
				// Remove achievement from player
				
				$sql = 
				"DELETE FROM character_titles
				WHERE  character_id = {$character_id}
				AND	cfgachievement_id = {$cfgachievementtodelete -> id}";
				
				Database::instance() -> query ($sql);			
			
				if ($mode != 'all')
				{
					// Set as current achievement the previous level
				
					if (isset($cfgachievements[$tag][$currentachievement['stars']-1]))
					{
						$previouscfgachievement = $cfgachievements[$tag][$currentachievement['stars']-1];
					
						$sql = 
							"UPDATE character_titles 
							SET current = 'Y' 
							WHERE character_id = {$character_id}
							AND	cfgachievement_id = {$previouscfgachievement -> id}";
					
						Database::instance() -> query ($sql);
					}
				}
		
				// recompute score
				
				Achievement_Model::computescore( $character_id );
			
				// update stat
				
				Character_Model::modify_stat_d(
					$character_id,
					'pointrewarded',
					0,
					$cfgachievementtodelete -> tag,
					$cfgachievementtodelete -> level,
					true);				
			
			
				// event
				
				Character_Event_Model::addrecord( 
					$character_id,
					'normal', 
					'__events.lostachievement' . 
					';__titles.' . $cfgachievementtodelete -> tag .'_' . $cfgachievementtodelete -> level,				
					'evidence' );
			}
		}
	}
	
	/**
	* Recompute the game score for a char
	*
	* @param int $character_id Id of character that loses the achievement
	* @return none
	*/	
	
	public function computescore( $character_id )
	{
		
		$totalscore = 0;
		
		$sql = "
			select c.name, c.position, ca.score, c.character_id, c.id, ca.id cfgachievement_id 
			from character_titles c, cfgachievements ca, characters ch
			where c.cfgachievement_id = ca.id 
			and   ch.id = c.character_id 
			and   ch.id = {$character_id}";
		
		$achievements = Database::instance() -> query( $sql );
		
		foreach ($achievements as $achievement)
		{			
				kohana::log('debug', "-> Giving points: {$achievement -> score} to char: {$character_id} for achievement: {$achievement -> name}");
				$totalscore += $achievement -> score;
		}
		
		$character = ORM::factory('character', $character_id );
		$character -> score = 0;
		$character -> modify_score ( $totalscore );
		$character -> save();
		
	}	
		
}
