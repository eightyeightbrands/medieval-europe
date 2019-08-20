<?php defined('SYSPATH') OR die('No direct access allowed.');

class Suggestion_Model extends ORM
{
	
	protected $belongs_to = array('character');
	const MAXOPENSUGGESTIONS = 3;
	
	
	/**
	* Vota un suggerimento
	* @param obj $char Character_Model
	* @param int $id ID Suggerimento
	* @param float $rating Voto
	* @param str $message esito ritorno
	* @return bool
	*/
	
	public function vote( $char, $id, $rating, &$message )
	{
		
		// verifica che char non è newbie
		
		if ($char -> is_newbie($char))
		{			
			$message = kohana::lang('charactions.error-tooyoung');
			return false;		
		}
		
		// verifica che non abbia già votato
		
		$stat = $char -> get_stats( 'votedsuggestion', $id );
		if (! is_null( $stat ) )
		{
			$message = kohana::lang('boardmessage.error-suggestionalreadyvoted');
			return false;		
		}
		
		// verifica che suggestion esista
		
		$suggestion = ORM::factory('suggestion', $id );
		if ( $suggestion -> loaded == false )
		{
			$message = kohana::lang( 'suggestions.error-suggestionnotfound');
			return false;
		}
		// verifica che voto è tra 1 e 5 
		
		if ( $rating < 0 or $rating > 5 )
		{		
			$message = kohana::lang( 'suggestions.error-ratingmustbefrom1to5');
			return false;
		}
		
		$suggestion -> totalrating += $rating;
		$suggestion -> votes ++;
		$suggestion -> averagerating = $suggestion -> totalrating / $suggestion -> votes;
		
		// Calcolo Baesian Rating
		
		$suggestionwithvotes = ORM::factory('suggestion') 
			-> where(
				array(
					'status !=' => 'deleted',
					'votes >' => 0 ))
			-> find_all();
			
		$totnumvotes = 0;
		$totrating = 0;
		$count = 0;
		
		foreach ($suggestionwithvotes as $suggestionwithvote)
		{
			$totnumvotes += $suggestionwithvote -> votes;
			$totrating += $suggestionwithvote -> averagerating;
			$count++;
		}
			
		$avgnumvotes = $totnumvotes / max(1,$count);
		$avgrating = $totrating / max(1,$count);
		
		$baesianrating = (
			( $avgnumvotes * $avgrating ) + ( $suggestion -> votes * $suggestion -> averagerating )
		) 
		/
		($avgnumvotes + $suggestion -> votes);
		
		$suggestion -> baesianrating = $baesianrating;
		
		$suggestion -> save();
		
		// save stat
		
		Character_Model::modify_stat_d( 
			$char -> id,
			'votedsuggestion', 
			0,
			$id,
			null,
			true,
			$rating			
		);

		$message = kohana::lang('suggestions.info-suggestionvoted') ;		

		return true;
		
	}
	
	/**
	* Aggiunge un suggerimento
	* @param obj $char Character_Model
	* @param array $post $_POST
	* @param str $message esito ritorno
	* @return bool
	*/
	
	public function add( $char, $post, &$message)
	{
		
		// personaggio deve avere almeno 90 giorni di gioco

		if ( $char -> get_age() < 90 )
		{
			$message = kohana::lang( 'charactions.error-tooyoung');
			return false;		
		}
		
		
		$suggestionsxchar = ORM::factory('suggestion') 
			-> where( array('character_id' => $char -> id) )
			-> notin( 'status', array( 'deleted', 'funded', 'completed' ))
			-> count_all();
					
		if (
			$suggestionsxchar >= self::MAXOPENSUGGESTIONS 
		    and	
			(!Auth::instance() -> logged_in('admin') and !Auth::instance()->logged_in('staff'))			
			)
		{
			$message = kohana::lang( 'suggestions.error-toomanysuggestionopened', self::MAXOPENSUGGESTIONS);
			return false;		
		}
			
		$suggestion = ORM::factory('suggestion');
		$suggestion -> character_id = $char -> id;
		$suggestion -> title = $post['title'];
		$suggestion -> body = $post['body'];
		$suggestion -> discussionurl = $post['discussionurl'];
		$suggestion -> created = time();
		$suggestion -> save();
		
		Character_Event_Model::addrecord( 
			1, 
			'announcement', 
			'__events.suggestionposted' .				
			';' .  html::anchor('suggestion/view/'. $suggestion->id, $suggestion -> title ) .
			';' .   $char -> name,
			'system' ); 		
		
		$message = kohana::lang('suggestions.info-suggestionadded');
		
		return true;
	}
	
	/**
	* Aggiunge un suggerimento
	* @param obj $char Character_Model
	* @param obj $suggestion Suggestion_Model
	* @param array $post $_POST
	* @param str $message esito ritorno
	* @return bool
	*/
	
	public function edit( $char, $suggestion, $post, &$message)
	{
		
		// suggestion deve esistere
		
		if ( $suggestion -> loaded == false )
		{
			$message = kohana::lang( 'suggestions.error-suggestionnotfound');
			return false;		
		}
		
		
		$suggestion -> title = $post['title'];
		$suggestion -> body = $post['body'];
		$suggestion -> detailsurl = $post['detailsurl'];
		$suggestion -> discussionurl = $post['discussionurl'];
		
		if ($suggestion -> status == 'new' 
			and 
			$post['quote'] > 0 
			and
			Auth::instance() -> logged_in('admin')
		)
		{
			$suggestion -> quote = $post['quote'];
			$suggestion -> status = 'fundable';
		}
		
		$suggestion -> save();
		
		
		if ($suggestion -> status == 'fundable')
			Character_Event_Model::addrecord( 
				1, 
				'announcement', 
				'__events.suggestionfundable' .				
				';' .  html::anchor('suggestion/view/'. $suggestion->id, $suggestion -> title ),				
				'system' ); 
		else
			Character_Event_Model::addrecord( 
				1, 
				'announcement', 
				'__events.suggestionedited' .				
				';' .  html::anchor('suggestion/view/'. $suggestion->id, $suggestion -> title ) .
				';' .   $char -> name,
				'system' ); 
		
		$message = kohana::lang('suggestions.info-suggestionedited');
		
		return true;
		
	}
	
	/**
	* Sponsorizza un suggerimento
	* @param obj $char Character_Model
	* @param int $id ID Suggerimento
	* @param int $doubloons Dobloni
	* @param str $message esito ritorno
	* @return bool
	*/
	
	public function sponsor( $char, $id, $doubloons, &$message)
	{
		
		$suggestion = ORM::factory('suggestion', $id);
		
		if ( $char -> get_item_quantity( 'doubloon' )  < $doubloons )
		{
			$message = kohana::lang('bonus.error-notenoughdoubloons');
			return false;
		}
		
		if ( $suggestion -> status != 'fundable' )
		{
			$message = kohana::lang('suggestions.error-suggestionisnotfundable');
			return false;
		}
		
		if ( !in_array( $doubloons, array( 50, 500 ) ) )
		{
			$message = kohana::lang('suggestions.error-sponsorshipvaluenotallowed');
			return false;
		}		
		
		$suggestion -> sponsoredamount += $doubloons;		
		if ( $suggestion -> sponsoredamount >= $suggestion -> quote )
		{
			$suggestion -> status = 'funded' ;
			Character_Event_Model::addrecord( 1, 'announcement', 
			'__events.suggestionfullysponsorised' .							
			';' . $suggestion -> title,			
			'system' ); 
		}
		
		$suggestion -> save();
		
		$char -> modify_doubloons ( - $doubloons, 'suggestionsponsorship' );		
		$char -> save();	
		
		Character_Event_Model::addrecord( 1, 'announcement', 
			'__events.suggestionsponsorised' .							
			';' . $char -> name . 
			';' . $suggestion -> title .			
			';' . $doubloons . 
			';' . $suggestion -> sponsoredamount .'/'. $suggestion -> quote,
			'system' ); 
		
		Character_Model::modify_stat_d(
			$char -> id,
			'suggestionsponsorship', 
			$doubloons,
			$suggestion -> id,
			null,
			false
			);
		
		$message = kohana::lang('suggestions.info-suggestionsponsorised',$doubloons);
		return true;
		
		
	}
	
	/**
	* Cancella un suggerimento
	* @param obj $char Character_Model
	* @param int $id ID Suggerimento
	* @param str $reason Causale
	* @param str $message esito ritorno
	* @return bool
	*/
	
	public function remove( $char, $id, $reason, &$message )
	{
		$suggestion = ORM::factory('suggestion', $id);
				
		if ( $suggestion -> loaded == false )
		{
			$message = kohana::lang( 'suggestions.error-suggestionnotfound');
			return false;
		}
		
		if ( 
				(
					$suggestion -> character_id != $char -> id 
					or
					$suggestion -> status != 'new' 
				)
				and
		  	    !Auth::instance() -> logged_in('admin')
			)
			{
				
				$message = kohana::lang('suggestions.cannotdeletesuggestion');
				return false;
			}
		
		Character_Event_Model::addrecord( 
			1, 
			'announcement', 
			'__events.suggestiondeleted' .				
			';' . $suggestion -> title .
			';' . $char -> name .
			';' . $reason,
			'system' ); 
		
		$suggestion -> status = 'deleted';
		$suggestion -> reason = $reason;
		$suggestion -> save();
		
		
		$message = kohana::lang( 'suggestions.info-suggestiondeleted');
		
		return true;	
	}	
}
