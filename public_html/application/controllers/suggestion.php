<?php defined('SYSPATH') OR die('No direct access allowed.');

class Suggestion_Controller extends Template_Controller
{
	
	public $template = 'template/gamelayout';		
	
	function index ($status = 'new')
	{

		$view = new View ( 'suggestion/index' ); 
		$suggestioncommands = new view('template/suggestioncommands');
		$submenu = new View ('template/submenu_boardmessage');
		$submenu2 = new view('template/submenu_suggestions');
		
		$sheets  = array('gamelayout'=>'screen', 'submenu'=>'screen');		
		$char = Character_Model::get_info( Session::instance()->get('char_id') ); 
		
		$suggestions = ORM::factory('suggestion') 
			-> in( 'status', array( $status ) )
			-> find_all();
		
		$this -> pagination = new Pagination(array(
			'base_url' => 'suggestion/index/'.$status,
			'uri_segment' => 'index',
			'query_string' => 'page',
			'total_items' => $suggestions -> count(),
			'items_per_page'=> 20 ));
		
		$suggestions = ORM::factory('suggestion') 
			-> in( 'status', array( $status ) )
			-> orderby( 'baesianrating', 'DESC') 
			-> find_all();
		
				
		$view -> pagination = $this -> pagination;		
		$suggestioncommands -> char = $char;
		
		$submenu -> category = 'suggestion';
		$view -> category = 'suggestion';
		$view -> submenu = $submenu;			
		
		$view -> submenu2 = $submenu2;
		$view -> suggestioncommands = $suggestioncommands;
		$view -> suggestions = $suggestions;
		$this -> template -> sheets = $sheets;
		$this -> template -> content = $view; 
			
	}
	
	function view($id)	
	{
		
		$view = new View ( 'suggestion/view' ); 
		$suggestioncommands = new view('template/suggestioncommands');
		$sheets  = array('gamelayout'=>'screen', 'submenu'=>'screen');	
		$suggestion = ORM::factory('suggestion', $id );
		$char = Character_Model::get_info( Session::instance()->get('char_id') ); 
		
		if ( !$suggestion -> loaded )
		{
			Session::set_flash('user_message', "<div class=\"error_msg\">" . 
				kohana::lang('suggestions.suggestionnotfound') . "</div>" );
			url::redirect(request::referrer());			
		}
		
		$stat = Character_Model::get_stat_d( $char -> id, 'votedsuggestion', $id );
		
		if ($stat -> loaded)
		{
			$view -> alreadyvoted = true;
			$view -> charrating = $stat -> stat1;
		}
		else
		{
			$view -> alreadyvoted = false;
		}
			
		$view -> suggestion = $suggestion;
		$suggestioncommands -> suggestion = $suggestion;
		$suggestioncommands -> char = $char;
		$view -> suggestioncommands = $suggestioncommands;
		$this -> template -> sheets = $sheets;
		$this -> template -> content = $view; 
	
	}
		
	function add()
	{
		$view = new View ( 'suggestion/add' ); 
		$sheets  = array('gamelayout'=>'screen', 'submenu'=>'screen');	
		$char = Character_Model::get_info( Session::instance()->get('char_id') ); 
		$message = '';
		
		$form = array(
				'title' => '',
				'body' => '',
				'discussionurl' => '',
				'detailsurl' => '',
				'quote' => 0,
			);
		
		if (!$_POST)
		{
			;			
		}
		else
		{
			
			$post = Validation::factory($this -> input -> post())
				-> add_rules('title','required', 'length[3,50]')
				-> add_rules('body', 'required', 'length[20,4096]')
				-> add_rules('discussionurl', 'required');	
				
			if ( $post -> validate() )
			{
				
				$rc = Suggestion_Model::add( $char, $post, $message);
				
				if ($rc == true )
				{
					Session::set_flash('user_message', "<div class=\"info_msg\">" . 
					$message . "</div>" );
					url::redirect('suggestion/index/new');
				}
				else
				{
					Session::set_flash('user_message', "<div class=\"error_msg\">" . 
					$message .  "</div>" );
					url::redirect('suggestion/index/new');					
				}
				
			}
			else
			{      
				$errors = $post -> errors('form_errors');						
				$view -> errors = $errors;			
				$form = arr::overwrite( $form, $post -> as_array());	
			}
			
			
		}
		
		$view -> form = $form;				
		$view -> char = $char;
		$this -> template -> sheets = $sheets;
		$this -> template -> content = $view; 
	}
		
	/*
	* Vota una suggestion
	*/
	
	function vote( $id, $rating )
	{
		
		$message = '';
		$view = new View ( 'suggestion/index' ); 
		$sheets  = array('gamelayout'=>'screen', 'submenu'=>'screen');				
		$char = Character_Model::get_info( Session::instance()->get('char_id') ); 
		
		$rc = Suggestion_Model::vote( 
			$char, 
			$id, 
			$rating,
			$message
		);
		
		if ( $rc == false )
		{
			Session::set_flash('user_message', "<div class=\"error_msg\">" . $message . "</div>" );
			url::redirect('suggestion/index/new');
		}	
		else
		{
			Session::set_flash('user_message', "<div class=\"info_msg\">" . $message . "</div>" );
			url::redirect('suggestion/index/new');
		}	
		
	}
	
	/* 
	* modifica suggerimento
	*/
	
	function edit( $id )
	{
		
		$message = '';		
		$char = Character_Model::get_info( Session::instance()->get('char_id') ); 
		$view = new View ( 'suggestion/edit' ); 
		$sheets  = array('gamelayout'=>'screen', 'submenu'=>'screen');				
		
		$form = array(
				'title' => '',
				'body' => '',
				'discussionurl' => '',
				'detailsurl' => '',
				'quote' => 0
			);
		
		if ( !$_POST )
		{
			
			$suggestion = ORM::factory('suggestion', $id );
						
			if ( 
				(					
					$suggestion -> character_id != $char -> id 
					or
					$suggestion -> created > (time() - 24*3600)				
					or
					$suggestion -> status != 'new' 
				)
				and
		  	    !Auth::instance() -> logged_in('admin')
			)
			{
				Session::set_flash('user_message', "<div class=\"error_msg\">" . 
					kohana::lang('suggestions.cannoteditsuggestion') . "</div>" );
				url::redirect(request::referrer());			
			}		
			
			$form = array(
				'title' => $suggestion -> title,
				'body' => $suggestion -> body,
				'discussionurl' => $suggestion -> discussionurl,
				'detailsurl' => $suggestion -> detailsurl,
				'quote' => $suggestion -> quote
			);
		
		}
		else
		{
			$post = Validation::factory($this -> input -> post())
				-> add_rules('title','required', 'length[5,50]')
				-> add_rules('body', 'required', 'length[20,4096]');				
			$suggestion = ORM::factory('suggestion', $this -> input -> post('id') );

			if ( $post -> validate() )
			{	
				$rc = Suggestion_Model::edit( $char, $suggestion, $post, $message );
				
				if ($rc == true )
				{
					Session::set_flash('user_message', "<div class=\"info_msg\">" . 
					$message . "</div>" );
					url::redirect('suggestion/index/new');
				}
				else
				{
					Session::set_flash('user_message', "<div class=\"error_msg\">" . 
					$message .  "</div>" );
					url::redirect('suggestion/index/new');				
				}			
			}
			else
			{      
				$errors = $post -> errors('form_errors');						
				$view -> errors = $errors;			
				$form = arr::overwrite( $form, $post -> as_array());	
			}	
			
		}
		
		$view -> suggestion = $suggestion;
		$view -> form = $form;				
		$this -> template -> sheets = $sheets;
		$this -> template -> content = $view; 
	
	}
	
	function sponsor( $id, $doubloons = 10 )
	{
		$message = '';
		$char = Character_Model::get_info( Session::instance()->get('char_id') ); 
		
		$rc = Suggestion_Model::sponsor( $char, $id, $doubloons, $message );

		if ( $rc )
		{				
			Session::set_flash('user_message', "<div class=\"info_msg\">" . $message . "</div>" );			
			url::redirect( request::referrer() );

		}
		else
		{			
			Session::set_flash('user_message', "<div class=\"error_msg\">" . $message . "</div>" );
			url::redirect( request::referrer() );
		} 
		
	}
	
	public function sponsorlist( $id )
	{
		
		$suggestion = ORM::factory('suggestion', $id );
		$char = Character_Model::get_info( Session::instance()->get('char_id') );		
		$view = new View ( 'suggestion/sponsorlist' ); 		
		$sheets  = array('gamelayout'=>'screen', 'submenu'=>'screen');		
		
		if ( ! $suggestion -> loaded )
		{
			Session::set_flash('user_message', "<div class=\"error_msg\">" . 
				kohana::lang('suggestions.error-suggestionnotfound') . "</div>" );
			url::redirect( 'suggestion/view/'.$id);
		}
		
		
		$sql = "select c.name, cs.value from character_stats cs, characters c
			where param1 = " . $id . "
			and   cs.character_id = c.id 
			and   cs.name = 'suggestionsponsorship' 
			order by value desc";
		$sponsorlist = Database::instance() -> query ( $sql ) ;
		
		$view -> suggestion = $suggestion;
		$view -> sponsorlist = $sponsorlist;
		$this -> template -> sheets = $sheets;
		$this -> template -> content = $view; 
	}
	
	public function remove( $id )
	{
		
		$view = new View ( 'suggestion/remove' ); 		
		$sheets  = array('gamelayout'=>'screen', 'submenu'=>'screen');						
		$message = '';
		$char = Character_Model::get_info( Session::instance()->get('char_id') ); 
		
		$form = array(
			'reason' => '',				
		);
						
		if (!$_POST)
		{
			
			$suggestion = ORM::factory('suggestion', $id );	
			
			if ( !$suggestion -> loaded )
			{
				Session::set_flash('user_message', "<div class=\"error_msg\">" . 
					kohana::lang('suggestions.suggestionnotfound') . "</div>" );
				url::redirect(request::referrer());			
			}
						
		}
		else
		{
			
			$suggestion = ORM::factory('suggestion', $this -> input -> post('id') );	
			
			
			$post = Validation::factory($this -> input -> post())
				-> add_rules('reason','required');				
			
			if ( $post -> validate() )
			{
				$rc = Suggestion_Model::remove( $char, $this -> input -> post('id'), $this -> input -> post('reason'), $message );

				if ( $rc )
				{				
					Session::set_flash('user_message', "<div class=\"info_msg\">" . $message . "</div>" );			
					url::redirect( 'suggestion/index' );

				}
				else
				{			
					Session::set_flash('user_message', "<div class=\"error_msg\">" . $message . "</div>" );
					url::redirect( 'suggestion/index' );
				}
			}
			else
			{
				$errors = $post -> errors('form_errors');						
				$view -> errors = $errors;			
				$form = arr::overwrite( $form, $post -> as_array());				
			}
		}
		
		$view -> form = $form;
		$view -> suggestion = $suggestion;
		$this -> template -> sheets = $sheets;
		$this -> template -> content = $view; 
		
	}
}
