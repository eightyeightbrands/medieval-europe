<?php defined('SYSPATH') OR die('No direct access allowed.');

class Toplistvote_Model extends ORM
{
	protected $belongs_to = array( 'cfgtoplist' ) ;

	/**
	* Give a reward for the toplist vote.
	* @param string control key
	* @return boolean response
	*/
	
	function reward_char( $key ) 
	{
		kohana::log('info', "-> Rewarding char... key: " . $key );

		$rewardgiven = false; 
		
		// c'è una entry valida con la stessa key?
	
		$r = ORM::factory('toplistvote') -> where( 
			array( 
				'vkey' => $key, 
				'status' => 'sent')) -> find();		
		
		if ( $r -> loaded )
		{
			kohana::log('info', '-> Key found, checking if char has already received a reward...');
			// determiniamo se il char non ha già ricevuto un reward oggi da questa toplist
			
			$db = Database::instance();
			$sql = "select id from toplistvotes
				where character_id = " . $r -> character_id .  "
				and   cfgtoplist_id = ". $r -> cfgtoplist_id . "
				and   status = 'done' 
				and   rewardgiven = true 
				and   from_unixtime( timestamp , '%Y-%m-%d') = curdate() ";
			
			$res = $db -> query ( $sql );
			
			if ( $res -> count() == 0 )
			{
			
				list( $quantity, $itemtag ) = explode( ';', $r -> cfgtoplist -> reward );
				
				$char = ORM::factory('character', $r -> character_id );
				kohana::log( 'info', '-> Rewarding char: ' . $char -> name );
			
				if ( $itemtag == 'coinbread' and $char -> loaded )
				{
				
					kohana::log( 'info', '-> Receiving a coin reward for toplist: ' . $r -> cfgtoplist -> name .
					', char: ' . $char -> name );
					$char -> modify_coins( $quantity, 'toplistvote' );						
					$char -> save();
					$description = 'page.toplist_reward_coinbread';


				}
				elseif ( $itemtag == 'energy' and $char -> loaded )
				{
					kohana::log( 'info', '-> Receiving a energy reward for toplist: ' . $r -> cfgtoplist -> name .
						', char: ' . $char -> name );
					$char -> modify_energy( $quantity, false, 'toplistvote' );
					$char -> save();
					kohana::log('debug', 'Reward vote: adding ' . $quantity . ' energy to char: ' . $r->character_id );
					$description = 'page.toplist_reward_energy';
				}
				
				Character_Event_Model::addrecord( 
					$char -> id,
					'normal',
					'__events.rewardgiven;' . $quantity .
					';__' . $description . 
					';' . $r -> cfgtoplist -> name, 
					null );
				
				
				$r -> rewardgiven = true;					
				$r -> status = 'done';
				
				// Updates votes counter
			
				$stat = Character_Model::get_stat_d($char -> id, 'dailytoplistvotes', $itemtag );
				
				$lastvoteddate = date('Y-m-d', $stat -> stat1);
				$today = date('Y-m-d');

				kohana::log('debug', '-> lastovoteddate: ' .  date('Y-m-d', $stat -> stat1));

				
				if ( $today != $lastvoteddate )				
					$char -> modify_stat( 'dailytoplistvotes', 1, $itemtag, null, true, time() );
				else
					$char -> modify_stat( 'dailytoplistvotes', $stat -> value + 1, $itemtag, null, true, time() );
					
				kohana::log('debug', "-> Reward vote: reward given: $quantity $itemtag to char " . $r->character_id );
				
				$rewardgiven = true;
				
				//echo '<response rewarded="1" message="Reward given." />';

			}
			else
			{
				$r -> status = 'done' ;
				$r -> note = 'Not unique vote, or char has already voted today.';
				$r -> rewardgiven = false;		
				kohana::log('info', "-> Reward vote: reward not given to char " . $r->character_id . " (not unique vote).");
				
				//echo '<response rewarded="0" message="Reward not given (not unique vote)." />';
			}
			
			$r -> save();			
			
		}
		else
		{
			kohana::log('info', "-> No valid entries for key:" . $key );
		}
		
		return $rewardgiven;
	
	}
	
}
