<?php defined('SYSPATH') OR die('No direct access allowed.');
class Map_Controller extends Template_Controller
{

	public $template = 'template/gamelayout';
	
	/*
	* Visualizza la mappa
	* @param none
	* @return none
	*/
	
	public function view( )
	{	
				
		$char = Character_Model::get_info( Session::instance()->get('char_id') );						
		$sheets  = array('gamelayout' => 'screen', 'map' => 'screen');
		$view = new view('map/view');
		$bonuses = Character_Model::get_premiumbonuses( $char -> id ); ;
		
		//////////////////////////////////////////////////////////////////////////////////
		// Controllo il nodo corrente del char. Se la posizione è 0, 
		// significa che il pg sta viaggiando
		//////////////////////////////////////////////////////////////////////////////////
		
		if ( Character_Model::is_traveling( $char -> id ) )			
		{
			$current_action = Character_Model::get_currentpendingaction( $char -> id );
			$current_position = ORM::factory('region', $current_action['param2'] );		
			$prev_position = ORM::factory('region', $current_action['param1'] );		
			kohana::log('debug', "Char is traveling, setting current position to: {$current_position -> name}");
			$travelingtext = kohana::lang('charactions.travelmessage', 
					kohana::lang( $prev_position -> name ), kohana::lang( $current_position -> name ) );
		}
		else
		{
			$travelingtext = '';
			$current_position = ORM::factory('region', $char -> position_id );		
			kohana::log('debug', "Char is NOT traveling, setting current position to: {$current_position -> name}");
		}
		
		///////////////////////////////////////////////////
		// tiro su tutte le informazioni delle regioni 
		//////////////////////////////////////////////////
		Database::instance() -> query("select '--regions_byid--'");
		$regions = Configuration_Model::get_cfg_regions_byid();		
		Database::instance() -> query("select '--regions_withstructures--'");
		$regions_with_structures = Configuration_model::get_regions_structures();
		Database::instance() -> query("select '--regions_resources--'");
		$resources = Configuration_Model::get_resources_all_regions();
		Database::instance() -> query("select '--diplomaticrelations--'");
		$diplomacy = Configuration_Model::get_cfg_diplomacyrelations();		
		Database::instance() -> query("select '--kingdoms--'");
		$kingdoms = Configuration_Model::getcfg_kingdoms();
		Database::instance() -> query("select '--regionpaths--'");
		$region_paths = Configuration_Model::get_cfg_regions_paths2();		
				
		// La chiesa del char ha il dogma per vedere le risorse?
		
		$hasdogma_resourceextractionblessing = 
			Church_Model::has_dogma_bonus( $char -> church_id, 'resourceextractionblessing');
		
		$afpachievement = 
			Character_Model::get_achievement( $char -> id, 'stat_fpcontribution' );
			
		foreach ($regions as $region_id => &$data )
		{
			
			// translate terms			
			
			$data['name'] = kohana::lang($data['name']);
			$data['kingdom_name'] = kohana::lang($data['kingdom_name']);
			$data['geographytext'] = kohana::lang('global.type') . ": <span class='valuelight'>" . kohana::lang( 'regioninfo.' . $data['geography'] ) . "</span>";
			$data['climatext'] = kohana::lang('global.climate') . ": <span class='valuelight'>" . kohana::lang( 
			'regioninfo.climate_' . $data['clima'] ) . "</span>";
			$data['infolink'] = html::anchor( 'region/info/' .$data['id'], 
						kohana::lang('global.info'),
						array(							
							'target' => 'new' 
						));
			$data['lawslink'] = html::anchor( 
					'region/info_laws/'.$data['id'],
						kohana::lang('regionview.submenu_laws'),
						array(
							'target' => 'new' 
						));
			
			// Carico informazioni su risorse (se la regione le ha)
			
			if (isset( $resources[$region_id] ) )
			{
				
				// Visibilità risorse
			
				if (
					$hasdogma_resourceextractionblessing 
					and 
					!is_null($afpachievement) 
					and 
					in_array($afpachievement['stars'], array(3,4,5)) 
				)
				{		
					$data['canseeavailability'] = true;
				}
				else
					$data['canseeavailability'] = false;

				$_info=array();
				
				foreach ($resources[$region_id]['resources'] as $resourcename => $info )
				{
					$_info[$resourcename]['name'] = kohana::lang('items.' . $resourcename . '_name');
					$_info[$resourcename]['availability'] = '(' . round($info['current']/$info['max'],2)*100 . '%)';
				}
							
				$data['resources']['info'] = $_info;
			}
		}
		
		// Carico Lista Regni
		
		$kingdomlist['']=kohana::lang('map.findakingdom');
		$kingdomlist['all']=kohana::lang('map.allkingdoms');
		foreach($kingdoms as $kingdomname => $kingdomdata)		
			$kingdomlist[$kingdomdata -> id] = kohana::lang($kingdomname);
				
		//////////////////////////////////////////////////////////////////
		// Elaboro Regioni Adiacenti
		//////////////////////////////////////////////////////////////////
		
		$adjacentregions = $region_paths[$current_position -> id];
		
		// get travel info only for adjacent regions.
		
		$par['bonuses'] = $bonuses;
		$par['weightinexcess'] = 	$char -> get_weightinexcess(); 
		$par['hasshoes'] = $char -> get_bodypart_item ("feet"); 
		$par['char'] = $char;
		
		foreach ($adjacentregions as $adjacentregionid => $info)
		{
			//kohana::log('info', '-> Processing ' . $current_position -> name . '  -> adjacent region: ' . $info['data'] -> name2);

			$par['type'] = $info['data'] -> type;				
			$par['time'] = $info['data'] -> time;
			$par['sourcename'] = $info['data'] -> name1;
			$par['destname'] = $info['data'] -> name2;			
			$travelinfo  = Region_Path_Model::get_travelinfo( $par );
			$linktravel = false;
			$linktraveltext = kohana::lang('global.travel');
			$linktravelaction = 'notset';
			
			// Travel from sea to land, diplay link SAIL only if there is an harbor

			//kohana::log('info', 'cp type: [' . $current_position -> type . '] info type:[' . $info['data'] -> type . ']' );	
			
			if ( $current_position -> type == "sea" and in_array( $info['data'] -> type, array('mixed', 'sea')) )
				//kohana::log('info', '-> checking if ' . $info['data'] -> name2 . ' has harbor');
				if ( array_key_exists( $info['data'] -> id2, $regions_with_structures['harbor'] ) )
				{
					//kohana::log('info', '-> ' . $info['data'] -> name2 . ' has harbor.');
					$linktravel = true;
					$linktravelaction = 'character/sail/' . $info['data'] -> id2;
				}

			// Travel from land to sea, display link SAIL only if there is an harbor
								
			if ( $current_position -> type == "land" and in_array( $info['data'] -> type, array('mixed', 'sea')) )
			{
				kohana::log('debug', $current_position -> name . '-> adding sail link');
				if ( array_key_exists( $current_position -> id, $regions_with_structures['harbor'] ) )
				{
					$linktravel = true;
					$linktravelaction = 'character/sail/' . $info['data'] -> id2;
				}
			}
			
			if ( $current_position -> type == 'fastsea' and $info['data'] -> type == 'fastsea' ) 
				if ( Character_Model::get_premiumbonus( $char -> id, 'travelerpackage' ) !== false )
				{
					$linktravel = true;
					$linktravelaction = 'character/sail/' . $info['data'] -> id2;
				}
			
			if ( $current_position -> type == 'sea' and $info['data'] -> type == 'sea' ) 
			{
				$linktravel = true;
				$linktravelaction = 'character/sail/' . $info['data'] -> id2;
			}

			if ( $current_position -> type == 'sea' and $info['data'] -> type == 'fastsea' ) 
			{
				$linktravel = true;
				$linktravelaction = 'character/sail/' . $info['data'] -> id2;
			}

			if ( $current_position -> type == 'fastsea' and $info['data'] -> type == 'sea' ) 
			{
				$linktravel = true;
				$linktravelaction = 'character/sail/' . $info['data'] -> id2;
			}
		
			// Travel land to land. If the target region is fastland check if the char has
			// travel bonus
			
			if ( $current_position -> type == "land" && $info['data'] -> type == "fastland")
				if ( Character_Model::get_premiumbonus( $char -> id, 'travelerpackage' ) !== false )
				{
					$linktravel = true;
					$linktravelaction = 'character/move/' . $info['data'] -> id2;
				}
			
			if ( $current_position -> type == "land" && $info['data'] -> type == "land")
			{
				$linktravel = true;
				$linktravelaction = 'character/move/' . $info['data'] -> id2;
			}
			
			// If region is disabled, it's not possible to travel to it.
			$regions[$adjacentregionid]['travelinfo'] = $travelinfo;					
			if ($info['data'] -> status2 == 'disabled' )
				$linktravel = false;			
			
			
			
			// check for battlefield
			
			if ( 
				isset($regions_with_structures['battlefield'])
					and
				array_key_exists( $adjacentregionid, $regions_with_structures['battlefield'] ) )
				{
					$linktravelaction .= '/1';
					$linktraveltext = kohana::lang('global.traveltobattlefield');
				}
				
			$regions[$adjacentregionid]['travelinfo']['linktravel'] = $linktravel;
			$regions[$adjacentregionid]['travelinfo']['linktravelaction'] = html::anchor( $linktravelaction, $linktraveltext,
					array('class' => 'st_common_command'));
		
			
			
			// Costruisco array con solo le regioni collegate all'attuale
			// posizione del character
			
			$linked_regions[$adjacentregionid] = $regions[$adjacentregionid];			
			
			//var_dump($linked_regions); exit;
		
		}
		
		$view -> travelingtext = $travelingtext;
		$view -> kingdomlist = $kingdomlist;
		$view -> character = $char;			
		$view -> current_position = $current_position;
		$view -> linked_regions = $linked_regions;		
		$view -> jsonresources = json_encode($resources, JSON_FORCE_OBJECT);					
		$view -> jsondiplomacy = json_encode($diplomacy, JSON_FORCE_OBJECT);		
		$view -> jsonregions = json_encode($regions, JSON_FORCE_OBJECT);	
		$this -> template -> content = $view;
		$this -> template -> sheets = $sheets;
		
	}
}
