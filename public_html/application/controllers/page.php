<?php defined('SYSPATH') OR die('No direct access allowed.');

class Page_Controller extends Template_Controller
{
	// Imposto il nome del template da usare
	
	public $template = 'template/gamelayout';	
	
	public function getpayoutstats()
	{
		
		$view = new View ('page/minedoubloons');
		$sheets  = array('gamelayout' => 'screen',  'character' => 'screen', 'submenu'=>'screen');		

		if ( request::is_ajax() )
		{
			$char = Character_Model::get_info( Session::instance()->get('char_id') ); 

			$this -> auto_render = false;
			kohana::log('debug', '--- refreshing stats ---');
			
			$query = http_build_query([
			 'secret' => 'nJ50mTiETusrG38BgKjmQoiZgKogAFAO',
			]);
			
			$url = "https://api.coin-hive.com/stats/payout/?" . $query;
			kohana::log('debug', "-> URL: {$url}");
			
			$ch = curl_init( $url );
			curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			$payoutstats=json_decode(curl_exec($ch));
			
			$query = http_build_query([
			 'secret' => 'nJ50mTiETusrG38BgKjmQoiZgKogAFAO',
			 'name' => $char -> user -> username
			]);
			
			$url = "https://api.coin-hive.com/user/balance/?" . $query;
			kohana::log('debug', "-> URL: {$url}");
			
			$ch = curl_init( $url );
			curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			$userbalance=json_decode(curl_exec($ch));
			
						
			kohana::log('debug', kohana::debug($userbalance));
			
			$output = array(
				'minedmonero' => $payoutstats -> payoutPer1MHashes * $userbalance -> balance / 1000000,
				'usd' => $payoutstats -> payoutPer1MHashes * $userbalance -> balance /1000000 * $payoutstats -> xmrToUsd
			);
			
			kohana::log('debug', kohana::debug($userbalance));

			curl_close($ch);
			echo json_encode($output);
			
		}
	}
	
	public function minedoubloons()
	{
		$char = Character_Model::get_info( Session::instance()->get('char_id') ); 

		$view = new view('page/minedoubloons');
		$sheets  = array(
			'gamelayout' => 'screen', 
			'character' => 'screen', 'pagination'=>'screen');
		
		
		
		$view -> user = $char -> user -> username;
		
		$this -> template -> sheets = $sheets;
		$this -> template -> content = $view;		
	}
	
	public function fullchatscreen()
	{
		$view = new view ('page/fullchatscreen');
		$title = 'Medieval Europe Chat';
		$char = Character_Model::get_info( Session::instance()->get('char_id') ); 
		$sheets  = array(
			'gamelayout' => 'screen', 
			'character' => 'screen', 'pagination'=>'screen');
			
		$view -> nick = str_replace("'", "", str_replace(" ", "_", $char -> name));
		$this -> template -> title = $title;
		$this -> template -> sheets = $sheets;
		$this -> template -> content = $view;
		
	}
	
	/** 
	* Funzione per visualizzare una pagina generica
	* che non ha bisogno di pre-elaborazioni
	* @param: nome della pagina da visualizzare
	* @return: none
	*/
	
	public function display( $page )
	{
		
		kohana::log('debug', "-> Displaying page: {$page}");
		
		try
		{
		
			$view = new view ('page/' . $page );
			$title = 'Medieval Europe, a Free Online Role Playing Game  - ' . ucfirst($page);
			
			
			// pagine che devono usare il template home
			
			if ( in_array( $page, array( 
				'terms-of-use', 
				'game-rules', 
				'privacy-and-cookies',
				'userregistered',				
				) ) )
			{
				$this -> template = new View('template/homepage');
				$sheets = array('home' => 'screen');
			}
			
			// pagine di errore
			
			elseif ( in_array( $page, 
				array ( 
					'custom_404',)))
			{
				$this -> template = new View('template/blank');
				$sheets = array('home' => 'screen');
			}
			elseif ( in_array( $page, 
				array ( 				
					'notauthorizedpage',
					'unsubscribe-ok',
					'unsubscribe-nok',)
				))
			{
				$this -> template = new View('template/blank');
				$sheets = array('home' => 'screen');
			}
			
			// pagine che usano il template del gioco
			
			else
			{
				$this -> template = new View('template/gamelayout');
				$sheets  = array(
					'gamelayout' => 'screen', 
					'submenu' => 'screen',
					'character' => 'screen',
					);
			}
							
			$this -> template -> title = $title;
			$this -> template -> sheets = $sheets;
			$this -> template -> content = $view;	
		
		} catch (Kohana_Exception $e)
		{
			kohana::log('error', "-> page {$page} does not have a view.");
			url::redirect('page/display/custom_404');
			exit;
		}
		
	}
	
	// Homepage
	
	public function index()
	{
		
		// GOOGLE SSO		
		$google = new Google_Bridge_Model();
		
		// FACEBOOK SSO
		$fb = new Facebook_Bridge_Model();

		$ipaddress = $this -> input -> ip_address();
		
		// if language cookie does not exists, set language based on IP geolocation
		
		$val = cookie::get('lang', 'cookiemissing');
		if ($val == 'cookiemissing')
		{
			
			kohana::log('info', '-> Visitor has no language cookie, installing it.');
			User_Model::setcorrect_language( $this -> input -> ip_address() );
		}
		
		$title = 'Medieval Europe, a Free Online Role Playing Game';		
		
		if ( Auth::instance() -> logged_in() )
		{
			if (Auth::instance() -> logged_in('affiliate'))
				url::redirect('/affiliate/dashboard');
			else
				url::redirect('/region/view');
		}
		else
		{
			
			$this -> template = new View('template/homepage');
			$sheets = array('home' => 'screen');
			$view = new view('page/home');			
			
			if ( $this -> input -> get('referreruser') )
				$referreruser = $this -> input -> get('referreruser');
			else
				$referreruser = null;						
			
			if ( $this -> input -> get('request_ids') )
				$request_ids = $this -> input -> get('request_ids');
			else
				$request_ids = null;	
						
			// Scrivi cookie per ricordare il referral.
			
			$parameters = "referreruser={$referreruser}&request_ids={$request_ids}";
			kohana::log('info', "-> Setting cookie value to:{$parameters}");
			$cookie_params = array  ( 
						'name'   => 'referraldata',
						'expire' => 3600,
						'value'  => $parameters,						
						'path'   => '/' );					
			cookie::set($cookie_params);	
							
							
							
			// match captcha
			$data = Utility_Model::get_mathcaptcha();
			Session::instance() -> set ('captchadata', $data);
			
			$form = array( 
				'username' => '', 
				'password' => '', 
				'email' => '',
				'referreruser' => $referreruser,				
			);			
			
			kohana::log('info', '-> Showing home page...');
			
			$view -> facebook_login_url = $fb -> get_login_url();
			$view -> google_login_url = $google -> get_google_login_url();
			$view -> form = $form;						
			$this -> template -> title = $title;
			$this -> template -> sheets = $sheets;
			$this -> template -> content = $view;						
			
		}	
	}
	
	public function retire()
	{
		$char = Character_Model::get_info( Session::instance()->get('char_id') ); 
		if ( $char -> is_meditating($char -> id) )
			$retireaction = Character_Action_Model::get_pending_action( $char -> id );
		else
			url::redirect('boardmessage/index/europecrier');
		$view = new view('page/retire');
		$sheets  = array(
			'gamelayout' => 'screen', 
			'character' => 'screen', 'pagination'=>'screen');
		
		$view -> retireaction = $retireaction;
		$this -> template->sheets = $sheets;
		$this -> template->content = $view;
		
	}
	
	// Jail Page
	
	public function jail()
	{
	
		$view = new view ('page/jail');
		$sheets  = array('gamelayout'=>'screen', 'submenu'=>'screen');
		$char = Character_Model::get_info( Session::instance()->get('char_id') ); 	
			
		$region = ORM::factory("region", $char -> position_id ); 		
		$db = Database::instance();		
				
		if (Character_Model::is_imprisoned($char->id)	== false )
		{
			url::redirect('region/view');
			return;
		}
		
		$sentence = ORM::factory("character_sentence") -> where ( 
			array ( 
				'character_id' => Session::instance()->get('char_id'),
				'status' => 'executing') ) -> find();		
		
		if ($sentence -> loaded == false )
		{
			url::redirect('region/view');
			return;
		}
		
		$structure = StructureFactory_Model::create( null, $sentence -> prison_id  );
		$role = $structure -> character->get_current_role();
		$view->structure = $structure;
		$view->sentence = $sentence;
		$view->role= $role;
		$view->region = $region;
		$this->template->content = $view;
		$this->template->sheets = $sheets;		
	}
	
	/**
	* Visualizza il report di battaglia
	* @param battle_id id battaglia
	* @param round_id id round
	*/
	
	public function battlereport( $battle_id = null, $round_id = 1 )
	{
		$view = new view ('page/battlereport');
		$sheets  = array('gamelayout'=>'screen', 'submenu'=>'screen', 'battlereport' => 'screen');
		
		$sql = "
		select 
			report1,
			report2,
			report3,
			report4,
			report5
		from battle_reports
		where battle_id = ?		
		";
		
		$rset = Database::instance() -> query ($sql, $battle_id);
		
		if ( count( $rset ) > 0 )
		{
			foreach ( $rset as $row )
			{			
				$view -> battlereport1 = $row -> report1;
				$view -> battlereport2 = $row -> report2;
				$view -> battlereport3 = $row -> report3;
				$view -> battlereport4 = $row -> report4;
				$view -> battlereport5 = $row -> report5;
			}
		}
		else
		{
			Session::set_flash('user_message', "<div class=\"error_msg\">" . 
				kohana::lang('battle.error-battlereportnotfound', $battle_id) . "</div>");
			url::redirect('boardmessage/index/europecrier');
			
		}	
				
		$this -> template -> sheets = $sheets;	
		$this -> template -> content = $view;	
	}
		
	/**
	* Visualizza le statistiche delle regioni
	*/
	
	public function kingdomstats( ) 
	{
		
		$view = new View ('page/kingdomstats');
		$sheets  = array('gamelayout' => 'screen',  'character' => 'screen', 'submenu'=>'screen');		
		
		if ( request::is_ajax() )
		{
			$this -> auto_render = false;
						
			// Carico i dati delle statistiche			
			// kohana::log('debug', kohana::debug( $this -> input -> post() ) );
			
				$sql = "select * from stats_historical 	
					where year( from_unixtime(period) ) > year(curdate()) - 3
					and kingdom_id = " . $this -> input -> post('id') ;
				
			$data = Database::instance() -> query( $sql ) -> as_array();
			
			$a['data']= $data;			
		
			echo json_encode( $a );
		}
		else
		{
		
			$kingdoms = Database::instance()->query("select id, name from kingdoms_v
			where name != 'kingdoms.kingdom-independent'") -> as_array();
			
			
			foreach ( $kingdoms as $key => $value )			
				$k[ $value -> id ] = kohana::lang( $value -> name ); 
			$view -> kingdoms = $k;			
			$this -> template -> sheets = $sheets; 
			$this -> template -> content = $view; 
			
		}
		
		
	}
	
	/**
	* Visualizza ranking
	* @param type tipo ranking char|kingdom|region
	* @param category categoria del ranking
	* @mode all|top25
	* @centeronplayer indica se si posiziona la pagina sulla posizione del player.
	*/
	
	public function rankings( 
		$type ='char', 
		$category = 'richestchars', 
		$mode = 'all', 
		$centeronplayer = false )
	{
		
		$sheets  = array('gamelayout'=>'screen', 'submenu'=>'screen');		
		$paginationlimit = 25;
		$character = Character_Model::get_info( Session::instance()->get('char_id') ); 
		$db = Database::instance();
		$linkedtitle = null;
		
		// category
		
		$view = new View( 'page/rankings_category');
		
		// limit
		
		if ( $mode == 'top25' )
		{
			$limit = 25;
			$modelabel = 'Top 25';
		}
		else
		{
			$limit = 10000;
			$modelabel = 'Overall';
		}		
		
		$rankings = $db -> query ( "select * from stats_globals 
			where type = '" . $category . "' and 
			date(from_unixtime(extractiontime)) = date_sub( current_date, interval 0 day) order by position asc limit $limit" ); 
		
		$position = null;
			
		if ( $type == 'char' )
		{	
			
			foreach ($rankings as $r)
			{
				if ( $r -> stats_id == $character -> id )
					{$position = $r -> position ; break;}
						
			}

			
			if ( $centeronplayer == 1 )
			{
				$page = intval ( $position / $paginationlimit ) + 1; 
				kohana::log('debug', 'Redirecting...' ); 

				url::redirect ( 'page/rankings/' . $type .'/' . $category . '/' . $mode . '/false/' . '?page=' . $page ); 
				return;
			}
		}
		
		$this->pagination = new Pagination(array(
			'base_url'=>'page/rankings/' . $type .'/' . $category . '/' . $mode,
			'uri_segment'=>'rankings/',			
			'query_string'=>'page',
			'total_items'=> count($rankings),
			'items_per_page'=>$paginationlimit));						
				
		
		$rankings = $db -> query ( "select * from stats_globals 
			where type = '" . $category . "' and 
			date(from_unixtime(extractiontime))  = date_sub( current_date, interval 0 day) order by position asc limit $paginationlimit offset " . $this->pagination->sql_offset ); 
				
		$rankinglist = array();	
		$entity = null;
		
		$i = 0;
		
		foreach ( $rankings as $r )
		{			
			
			$rankinglist['extractiontime'] = $r -> extractiontime;
			$entity = $r -> entity;
			
			if ( in_array( $r->type, array( 'raiderskingdoms', 'raidedkingdoms') ) )
			{
				if ( $r->value > 0 )
					$rankinglist[$r->type][$i]['stat'] = $r;
			}
			else
			{
				
				$rankinglist[$r->type][$i]['stat'] = $r;
				
				if ( $type == 'char' )
				{					
					
					$linkedtitles = Database::instance() -> query(
					"select * 
						from  character_titles 
						where character_id = " . $r -> stats_id . "
							and   name = 'stat_$category'
							and   current = 'Y' 
							") -> as_array();
					//var_dump($linkedtitles); 
					if ( count($linkedtitles) == 1 )
						$rankinglist[$r->type][$i]['title'] = kohana::lang('titles.' .
							$linkedtitles[0] -> name . '_' . $linkedtitles[0] -> stars);
					else
						$rankinglist[$r->type][$i]['title'] = '';
				}
				
			}
			$i++;
		}
		
		//exit;
		
		$view->linkedtitle = $linkedtitle;
		$view->type=$type;
		$view->pagination = $this->pagination;
		$view->limit = $limit;
		$view->modelabel = $modelabel;
		$view->mode = $mode;
		$view->entity = $entity;
		$view->category = $category ;
		$view->rankings = $rankinglist ;
		$this->template->content = $view;
		$this->template->sheets = $sheets;
		
	}
	
	/**
	* Visualizza info server dal file medeur.php
	*/
	
	
	public function serverinfo ( ) 
	{
		
		$view = new View ('page/serverinfo');
		$sheets  = array(
			'homepage' => 'screen', 
			'character' => 'screen', 'pagination'=>'screen');
		
		$this -> template = new View('template/homepage');		
		$this -> template -> sheets = $sheets;		
		$this -> template -> content = $view;
	
	}
	
	public function readnews( $newsid )
	{		
		$view = new view( 'page/readnews');
		$this -> template = new View('template/blank');
		$sheets  = array('bootstrap_me' => 'screen');
		
		$message = ORM::factory('admin_message', $newsid);
		
		if ( !$message -> loaded )
		{
			echo "No such new exists";	
			die();
		}		
		$this -> template -> sheets = $sheets;		
		$view -> message = $message;
		$this -> template -> content = $view;
	}
	
}
