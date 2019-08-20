<?php defined('SYSPATH') OR die('No direct access allowed.');

class ST_Buildingsite_1_Model extends Structure_Model
{
	
	public function init()
	{
		$this -> setCurrentLevel(1);		
		$this -> setParenttype('buildingsite');
		$this -> setSupertype('buildingsite');
		$this -> setMaxlevel(1);
		$this -> setIsbuyable(false);
		$this -> setIssellable(false);	
		$this -> setStorage(100000000);
		$this -> setWikilink('En_US_BuildingSite');
		
	}
	
	// Funzione che costruisce i links relativi
	// @output: stringa contenente i links relativi a questa struttura

	public function build_common_links( $structure, $bonus )
	{
		
		$kp = ORM::factory('kingdomproject') -> where ( 'structure_id', $structure->id ) -> find(); 				
		// controlla se i materiali sono stati inseriti e aggiorna lo stato di conseguenza.
		$kp -> is_buildable();
		$info = $kp -> get_info();
		
		
		$links = '';		
		$links .= Kohana::lang('structures_buildingsite.infoaction',	kohana::lang( $info['builtstructure'] -> name ) ) . "<br/><br/>";
		$links .= Kohana::lang('global.status') . ': ' . kohana::lang('structures.prj_status_' . $info['project'] -> status	 ) . '<br/>';				
		$links .= Kohana::lang('global.progress') . ': ' . $info['workedhours_percentage'] . '%<br/>'; 

		if ( in_array( $info['project'] -> cfgkingdomproject -> tag, 
			array( 'religion_2', 'religion_3', 'religion_4' ) ) )	
			$links .= kohana::lang('global.condition') . ': ' . "<span class='value'>"	. $structure -> state . '%</span><br><br>';
		
		$links .= html::anchor( "/structure/donate/" . $structure -> id, Kohana::lang('structures_actions.global_deposit'), array('class' => 'st_common_command')) . "<br/>" ;
		$links .= html::anchor( "/structure/info/" . $structure -> id, Kohana::lang('structures_actions.global_info'), array('class' => 'st_common_command')) . "<br/>" ;
		if ( $info['project'] -> status == 'building' )
			$links .= html::anchor( "/buildingsite/build/" . $structure -> id, Kohana::lang('global.build'), array('class' => 'st_common_command')) . "<br/>" ;
		
		// aggiungi link danneggia e ripara solo se il building site
		// è per una struttura religiosa
		
		if ( in_array( $info['project'] -> cfgkingdomproject -> tag, 
			array( 'religion_2', 'religion_3', 'religion_4' ) ) )
		{
			$links .= html::anchor( "/structure/damage/" . $structure -> id, Kohana::lang('ca_damage.damage'), array('class' => 'st_common_command')) ;
			
			if ( $bonus !== false )
			{
				$links .= ' - '.html::anchor( "/structure/damage/".$structure->id."/2", 'x2',
				array('title' => Kohana::lang('ca_damage.damage').' (x2)', 'class' => 'st_common_command',
						'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')' ));
				
				$links .= ' - '.html::anchor( "/structure/damage/".$structure->id."/3", 'x3',
				array('title' => Kohana::lang('ca_damage.damage').' (x3)', 'class' => 'st_common_command',
						'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')' ));
			}
			
			if ( $structure -> state < 100 )
			{
				$links .= "<br/>";
				
				$links .= html::anchor( "/structure/repair/" . $structure -> id, Kohana::lang('ca_repair.repair'), array('class' => 'st_common_command')) . '<br>';
				
				if ( $bonus !== false )
				{
						$links .= ' - '.html::anchor( "/structure/repair/".$structure->id."/2", 'x2',
						array('title' => Kohana::lang('ca_repair.repair').' (x2)', 'class' => 'st_common_command',
							'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')' ));
						
						$links .= ' - '.html::anchor( "/structure/repair/".$structure->id."/3", 'x3',
						array('title' => Kohana::lang('ca_repair.repair').' (x3)', 'class' => 'st_common_command',
							'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')' ));
				}
			}
		
		}
		
		$links .= "<br/>";

		return $links;
	}

	
}
