<?php defined('SYSPATH') OR die('No direct access allowed.');

class Toplist_Controller extends Template_Controller
{
	
	public $template = 'template/gamelayout';
	
	/**
	* Vota il gioco su una toplist
	* @param type tipo del premio
	* @param toplistid id toplist
	* @return none
	*/
	
	function vote( $type, $toplist_id = null )
	{
		
		$view = new View ( '/toplist/sitelist' ); 
		$sheets  = array('gamelayout'=>'screen', 'submenu'=>'screen');
		$db = Database::instance();
		$char = Character_Model::get_info( Session::instance()->get('char_id') );		
		$vt = array();	
		$playercanvote = true;
		kohana::log('info', '-> char: ' . $char -> name . ' is voting for toplist: ' . $toplist_id );
		
		// se la toplist non esiste o è inactive, errore
		
		if ( !is_null( $toplist_id ) )
		{
			$_toplist = ORM::factory('cfgtoplist', $toplist_id ); 
			if ( $_toplist -> loaded == false or $_toplist -> status != 'active' )
			{
				Session::set_flash('user_message', "<div class=\"error_msg\">". kohana::lang( 'global.operation_not_allowed') . "</div>");
				url::redirect( '/region/view' );	
			}
		}
		
		////////////////////////////////////////////////////////////////////
		// Il type bread e riservato solo ai newbie
		////////////////////////////////////////////////////////////////////
		
		if ( $type == 'coinbread' and $char -> is_newbie($char)==false  )
		{
			Session::set_flash('user_message', "<div class=\"error_msg\">". kohana::lang( 'page.error-votedreservedfornewbies') . "</div>");
			url::redirect( '/region/view' );		
		}
		
		////////////////////////////////////////////////////////////////////
		// se il giocatore ha già effettuato 3 voti per tipo, stop al voto
		////////////////////////////////////////////////////////////////////
		
		$stat = Character_Model::get_stat_d($char -> id, 'dailytoplistvotes', $type );
		if ( $stat -> loaded )
		{
			$lastvoteddate = date('Y-m-d', $stat -> stat1);
			$today = date('Y-m-d');
			if ( $today == $lastvoteddate and $stat -> value >= 3 )
			{			
				Session::set_flash('user_message', "<div class=\"error_msg\">". kohana::lang( 'page.error-votedthreetimes') . "</div>");
				url::redirect( '/region/view' );			
			}
		}
		
		// Se la toplist_id è nulla, visualizzo le toplist
		
		if ( is_null( $toplist_id ) )		
		{
			
			// trova le toplist dove il char non ha ancora votato.
			
			if ( true )
			{
				
				// troviamo tutte le toplist per il premio selezionato
				
				$toplists = $db -> query ( "
				select * from cfgtoplists c 
				where c.reward like ?
				and   c.status = 'active' 
				and   c.showtoplist = true", '%' . $type ); 
				
				$i = 0 ;
				foreach ( $toplists as $toplist )
				{
				
					
					// contiamo i voti della toplist x questo mese
					
					$res = $db -> query ( "
						select count(id) votes 
						from toplistvotes 
						where status = 'done' 
						and cfgtoplist_id = " . 
						$toplist -> id .  " and year(from_unixtime( timestamp )) = year(curdate()) " .
						" and month(from_unixtime( timestamp )) = month(curdate() ) " ) -> as_array();				
									
					// La inseriamo nella lista solo se il target non � raggiunto
					
					if ( $res[0] -> votes <= $toplist -> target ) 
					{
					
						$lastvote = $db -> query ( "
						select max(timestamp) lastvote
						from toplistvotes 
						where status = 'done' 
						and cfgtoplist_id = {$toplist -> id}
						and character_id = {$char->id}");
					
						$vt[$i]['id'] = $toplist -> id ; 
						$vt[$i]['name'] = $toplist -> name ; 
						$vt[$i]['url'] = $toplist -> url; 
						$vt[$i]['lastvote'] = Utility_Model::format_datetime($lastvote[0] -> lastvote);
						$vt[$i]['lastvoteunixtime'] = $lastvote[0] -> lastvote;
						$vt[$i]['nextvoteunixtime'] = $lastvote[0] -> lastvote + (24*3600);
						$vt[$i]['countdown'] = Utility_Model::countdown($vt[$i]['nextvoteunixtime']);
						list ( $quantity, $rewardtype) = explode( ";",  $toplist -> reward );
						$vt[$i]['reward'] = $quantity . " " . $rewardtype; 
						
						$res = $db -> query ( "
							select count(id) votes 
							from toplistvotes 
							where status = 'done' 
							and cfgtoplist_id = " . 
							$toplist -> id .  " and year(from_unixtime( timestamp )) = year(curdate()) " .
							" and month(from_unixtime( timestamp )) = month(curdate() ) " ) -> as_array();
						
						$vt[$i]['votes'] = $res[0] -> votes;
						$vt[$i]['target'] = $toplist -> target;					
						$i++;
					
					}
				
				}
			}
			
		}
		else
		{
			
			kohana::log('info', '-> char: ' . $char -> name . ' is voting for toplist: ' . $toplist_id . ' - redirecting to toplist...' );
			$par[0] = $char;
			$par[1] = $toplist_id;
			$par[2] = $this -> input -> ip_address();
			$par[3] = null;
			$ca = Character_Action_Model::factory("votegame");		
			
			if ( $ca -> do_action( $par,  $message ) )
				;		
			else	
			{ 
				Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>"); 
			}			
			
			url::redirect(request::referrer());
		}
				
		$view -> playercanvote = $playercanvote;
		$view -> type = $type; 
		$view -> toplists = $vt; 
		$this -> template -> sheets = $sheets;
		$this -> template -> content = $view; 		

	}	
	
	
	/**
	* Process a reward callback from arenatop100.
	* param none
	* return none
	*/
	
	function obs_reward_arenatop100( )
	{
		kohana::log('info', '-> Received a reward from top100arena');
		kohana::log('info', kohana::debug($this -> input -> post()));
		
		$whitelist = array('dksajdasjdwuudsak'); 

		$userId = isset($_POST['userid']) ? $_POST['userid'] : null; 
		$userIp = isset($_POST['userip']) ? $_POST['userip'] : null; 
		$valid = isset($_POST['voted']) ? intval($_POST['voted']) : 0; 
		$at_refc = isset($_POST['at_refc']) ? $_POST['at_refc'] : null; 

		kohana::log('info', '-> userId' . $userId );
		kohana::log('info', '-> userip' . $userIp );
		kohana::log('info', '-> valid' . $valid );
		kohana::log('info', '-> at_refc' . $at_refc );
		
		
		$result = false; 
		
		if (!empty($userId) && !empty($at_refc)) 
		{ 

		  if (in_array($at_refc, $whitelist)) 
		  { 
			$result = true; 
			$t = new Toplistvote_Model;
			$rewardgiven = $t -> reward_char( $userId ); 
		  } 
		} 

		if ($result) { 
			  echo 'OK'; 
		} 

	}
	
	/**
	* Process a reward callback, generic procedure for all toplists.
	* param none
	* return none
	*/
	
	function reward()
	{
		
		$rewardgiven = false;
		$quantity = 0;
		$itemtag = '';
	
		// se il voto non è immediato ci aspettiamo
		// un callback dalla toplist
	
		$key = $this -> input -> get('key');

		kohana::log('info', "-> Topsite: receiving a reward callback --- ");
		kohana::log('info', "-> Topsite: key: $key");
		
		// apex web gaming sends i...
		if ( $key == '' ) 
			$key = $this -> input -> post('i');
		
		// topwebgames.com, top100arena.com
		if ( $key == '' )
			$key = $this -> input -> post('uid');						

		if ( $key == '' )
			$key = $this -> input -> get('cid');						
 
		if ( $key == '' )
			$key = $this -> input -> get('userid');						
		
		if ( $key == '' )
			$key = $this -> input -> post('userid');		
		
		if ( $key == '' )
		{
			kohana::log('error', '-> Toplist callback: no parameter received, exiting.');
			exit();
		}
		
	
		$t = new Toplistvote_Model;
		$rewardgiven = $t -> reward_char( $key ); 
						
		if ( $rewardgiven )
			echo '<response rewarded="1" message="Reward given." />';			
		else
			echo '<response rewarded="0" message="Reward not given (key not matched or already voted)." />';					
				
		exit(); 
		
	}

	
}
