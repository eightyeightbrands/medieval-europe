<?php defined('SYSPATH') OR die('No direct access allowed.');

class WatchTower_Controller extends Template_Controller
{
	
	public $template = 'template/gamelayout';		
	
	public function watch( $structure_id = null )
	{
	
		$view = new view('watchtower/watch');
		$sheets  = array('gamelayout'=>'screen', 'submenu'=>'screen');		
		$character = Character_Model::get_info( Session::instance()->get('char_id') );
		$presentchars = array();
		$adjacentregions = array();
		
		if (!$_POST)
		{
			$structure = StructureFactory_Model::create( null, $structure_id);
			if ( ! $structure -> allowedaccess( $character, $structure -> getParentType(), $message,
				'private', 'watch') )
			{
				Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
				url::redirect('region/view/');
			}	
			
			
			$currentwatchedregion_id = 0;
			$adjacentregions[$structure -> region_id ] = kohana::lang($structure -> region -> name );		
			$_adjacentregions = Region_Model::find_adjacentregions( $structure -> region );
			$adjacentregions = $adjacentregions + $_adjacentregions ;			
			
		}
		else
		{
			
			$structure = StructureFactory_Model::create( null, $this -> input -> post('structure_id') );
			
			if ( ! $structure -> allowedaccess( $character, $structure -> getParentType(), $message, 'private', 'watch') )
			{
				Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
				url::redirect('region/view/');
			}			
			
			$adjacentregions[$structure -> region_id ] = kohana::lang($structure -> region -> name );		
			$_adjacentregions = Region_Model::find_adjacentregions( $structure -> region );
			$adjacentregions = $adjacentregions + $_adjacentregions ;
			
			if ( $this -> input -> post('startwatch') ) 
			{							
				$currentwatchedregion_id = $structure -> region_id;
				
				$ca = Character_Action_Model::factory("watcharea");				
				$par[0] = $character;
				$par[1] = ORM::factory('region', $currentwatchedregion_id ); 
			
				if ( $ca -> do_action( $par,  $message ) )
				{
					$presentchars = Region_Model::get_characteractivity( $currentwatchedregion_id  );			
					Session::set_flash('user_message', "<div class=\"info_msg\">". $message . "</div>"); 					
				}	
				else	
				{ 
					Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>"); 					
				}
				
			}
			else
			{
				
				$currentwatchedregion_id = $this -> input -> post('region_id');				
				if ( !array_key_exists( $currentwatchedregion_id, $adjacentregions ) )
				{
					Session::set_flash('user_message', "<div class=\"error_msg\">". kohana::lang('global.operation_not_allowed') . "</div>");
					url::redirect('watchtower/watch/');
				}
			
				$presentchars = Region_Model::get_characteractivity( $this -> input -> post('region_id')  );			
				
			}
		
		}
		$submenu = new View( 'structure/' . $structure -> getSubmenu() );
		$submenu -> id = $structure -> id;
		$submenu -> action = 'watch';
		$view -> submenu = $submenu;
		$view -> currentwatchedregion = ORM::factory('region', $currentwatchedregion_id);		
		$view -> adjacentregions = $adjacentregions;		
		$view -> presentchars = $presentchars;
		$view -> currentregion_id = $structure -> region_id;	
		$view -> iswatchingarea = Character_Model::is_watchingarea( $character -> id );		
		$view -> structure = $structure;				
		$view -> structure_id = $structure_id ;
		$this -> template -> content = $view ;
		$this -> template -> sheets = $sheets; 
	
	}
	
}
