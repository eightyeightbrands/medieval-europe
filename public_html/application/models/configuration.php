<?php defined('SYSPATH') OR die('No direct access allowed.');

class Configuration_Model
{
	
		
	/**
	* Carica tutte le guerre
	* @param: none
	* @return: array $cfg array cosi composto:
	*   [id guerra]
	*      ['war'] -> contiene dati guerra come selezionati
	*      ['kingdoms'] -> dati dei regni coinvolti nella guerra
	*/
	
	public function get_kingdomswars()
	{
		$cachetag = '-cfg-kingdomswars' ;		
		$cfg = My_Cache_Model::get( $cachetag );		
		
		if ( is_null( $cfg ) )		
		{
			
			$db = Database::instance();			
			$sql = "
			SELECT k.name sourcekingdomname, k2.name targetkingdomname, kw.*, ka.kingdom_id, ka.role    
			FROM kingdom_wars kw, kingdom_wars_allies ka, kingdoms_v k, kingdoms_v k2
			WHERE ka.kingdom_war_id = kw.id
			AND   kw.source_kingdom_id = k.id			
			AND   kw.target_kingdom_id = k2.id ";			
						
			$res = $db -> query( $sql ) -> as_array();
			
			foreach ($res as $row)
			{
				$cfg[$row->id]['war'] = $row;
				$cfg[$row->id]['kingdoms'][$row -> kingdom_id]['id'] = $row -> kingdom_id;
				$cfg[$row->id]['kingdoms'][$row -> kingdom_id]['role'] = $row -> role;
				
			}
			
			My_Cache_Model::set( $cachetag, $cfg );
			
			//var_dump($cfg);exit;
		}
		
		return $cfg;
		
	}
	
	
	/**
	* Carica la lista dei moduli
	* @param: none
	* @return: none
	*/
	
	public function get_disabledmodules()
	{
		$cachetag = '-cfg-modules' ;		
		$cfg = My_Cache_Model::get( $cachetag );		
		
		if ( is_null( $cfg ) )		
		{
			
			$db = Database::instance();			
			 $sql = "SELECT * FROM cfgmodules where status = 'disabled'";
			$res = $db -> query( $sql ) -> as_array();
			foreach ($res as $row)
			{
				$cfg[$row->module] = $row->status;				
			}			
			My_Cache_Model::set( $cachetag, $cfg );
		}
		//var_dump($cfg);exit;
		return $cfg;
		
	}
	
	
	/**
	* Carica Configurazione Quests
	* @param: none
	* @return array $cfg Configurazione
	*/
	
	public static function get_questscfg()
	{
		$cachetag = '-cfg-quests' ;		
		$cfg = My_Cache_Model::get( $cachetag );		
		
		if ( is_null( $cfg ) )		
		{
			
			$db = Database::instance();			
			$sql = "SELECT * FROM cfgquests order by sortorder asc";
			$res = $db -> query( $sql ) -> as_array();
			foreach ($res as $row)
			{
				$cfg[$row -> name] = $row;		
			}
			
			My_Cache_Model::set( $cachetag, $cfg );
		}
		//var_dump($cfg);exit;
		return $cfg;
		
	}
	
	/**
	* Carica configurazione armi
	* @param: none
	* @return array $cfg Array con informazioni configurazione armi
	*/
	public static function get_weaponscfg()
	{
	
		$cachetag = '-cfg-weapons' ;		
		$cfg = My_Cache_Model::get( $cachetag );		
		$maxweight = 0;
		if ( is_null( $cfg ) )		
		{
			
			$db = Database::instance();			
			$sql = "
				SELECT *
				FROM cfgitems where subcategory = 'weapon' 
			";
			$res = $db -> query( $sql ) -> as_array();
			foreach ($res as $row)
			{
				$cfg['weaponlist'][$row -> tag]['obj'] = $row;				
				if ($maxweight < $row -> weight)
					$maxweight = $row -> weight;
			}
			$cfg['maxweight'] = $maxweight;
			My_Cache_Model::set( $cachetag, $cfg );
		}
		//var_dump($cfg);exit;
		return $cfg;
		
	}
	
	public static function get_badwordscfg()
	{
	
		$cachetag = '-cfg-badwords' ;		
		$cfg = My_Cache_Model::get( $cachetag );		
		
		if ( is_null( $cfg ) )		
		{
			
			$db = Database::instance();			
			$sql = "
				SELECT * FROM cfgbadwords; 
			";
			
			$res = $db -> query( $sql ) -> as_array();
			
			foreach ($res as $row)
			{
				$cfg[$row->word] = true;				
			}
			
			My_Cache_Model::set( $cachetag, $cfg );
		}
		//var_dump($cfg);exit;
		return $cfg;
		
	}
	
	public static function get_armorscfg()
	{
	
		$cachetag = '-cfg-armors' ;		
		$cfg = My_Cache_Model::get( $cachetag );		
		$maxweight = 0;
		if ( is_null( $cfg ) )		
		{
			
			$db = Database::instance();			
			$sql = "
				SELECT *
				FROM cfgitems where subcategory  in ('armor', 'shield')
			";
			$res = $db -> query( $sql ) -> as_array();
			foreach ($res as $row)
			{
				$cfg['armorlist'][$row -> tag]['obj'] = $row;				
				$coveredparts = explode("|", $row -> coverage);
				foreach ($coveredparts as $key => $part)				
				{				
					$cfg['armorlist'][$row -> tag]['coverage'][] = $part;
				}
			}
			
			My_Cache_Model::set( $cachetag, $cfg );
		}
		//var_dump($cfg);exit;
		return $cfg;
		
	}
		
	public static function get_wardrobeitemcfg()
	{
		
		$cachetag = '-cfg-wardrobeitemcfg' ;		
		$cfg = My_Cache_Model::get( $cachetag );		
		
		if ( is_null( $cfg ) )		
		{
			
			$db = Database::instance();			
			$sql = "SELECT c.*, cb.name bonusname, cc.id cut_id
			FROM cfgwardrobeitems c, cfgpremiumbonuses cb, cfgpremiumbonuses_cuts cc
			WHERE c.cfgpremiumbonus_id = cb.id
			AND   cb.id = cc.cfgpremiumbonus_id";
			$res = $db -> query( $sql ) -> as_array();
			foreach ($res as $row)
				$cfg[$row->tag] = $row;
			
			My_Cache_Model::set( $cachetag, $cfg );
		}
//		var_dump($cfg);exit;
		return $cfg;
		
		
	}
	
	/**
	* Carica la configurazione dei path della mappa
	* e li mette in un vettore in cache
	* @param none
	* @return array data
	*/
	
	public static function get_cfg_regions_paths()
	{
	
		$cachetag = '-cfg-regions_paths' ;		
		$cfg = My_Cache_Model::get( $cachetag );
				
		if ( is_null( $cfg ) )		
		{
			
			$db = Database::instance();			
			$sql = "select rp.id , r1.id id1, r1.name name1, r2.name name2, r2.id id2, 
					rp.type, rp.time 
					from regions_paths rp, regions r1, regions r2
					where rp.region_id = r1.id
					and   rp.destination = r2.id";
			$cfg = $db -> query( $sql ) -> as_array();								
			My_Cache_Model::set( $cachetag, $cfg );
		}
		
		return $cfg;
	
	}
	
	/**
	* Carica la configurazione dei path della mappa
	* e li mette in un vettore associativo target -> dest
	* @param none
	* @return array data
	* 
	*/
	
	public static function get_cfg_regions_paths2()
	{
	
		$cachetag = '-cfg-regions_paths2' ;		
		$cfg = My_Cache_Model::get( $cachetag );
				
		if ( is_null( $cfg ) )		
		{
			
			$db = Database::instance();			
			
			$sql = "select rp.id, r1.id id1, r2.id id2, r1.name name1, r2.name name2, rp.type, rp.time, 
			    r1.status status1, r2.status status2 
					from regions_paths rp, regions r1, regions r2 
					where rp.region_id = r1.id
					and   rp.destination = r2.id";
			
			$res = $db -> query( $sql );			
			
			foreach ($res as $row)
			{
				$cfg[$row -> id1][$row -> id2]['data'] = $row;
				if ( $row -> type == 'fastland' )
				{
					$sql1 = "select * from regions_paths_fasttracksroutes where regions_path_id = " . $row -> id ;
					$res1 = $db -> query ($sql1);				
					
					foreach ($res1 as $row1 )						
						$cfg[$row -> id1][$row -> id2]['crossedregions'][]= $row1;
				}				
			}			
			
			My_Cache_Model::set( $cachetag, $cfg );
		}
		
		return $cfg;
	
	}
	
	/**
	* Loads regions configuration, key name
	* @param none
	* @return array 
	*/
		
	public static function get_cfg_regions()
	{
		$cachetag = '-cfg-regions'; 				
		$cfg = My_Cache_Model::get( $cachetag );
		
		if ( is_null( $cfg ) )
		{
		
			$sql = "
				select
				r.id, r.name, r.type, k.image, k.name kingdom_name, r.status, 
				k.id kingdom_id, r.geography, r.clima, r.coords, k.color kingdom_color
				from  regions r, kingdoms_v k
				where r.kingdom_id = k.id ";				
			
			$res = Database::instance() -> query( $sql );
			foreach ( $res as $region )
			{
				$cfg[$region->name] = $region;				
			}
			
			My_Cache_Model::set( $cachetag, $cfg ); 
			
		}
		
		return $cfg ;
		
	}
	
	/**
	* Loads regions configuration, key id
	* @param none
	* @return array 
	*/
		
	public static function get_cfg_regions_byid()
	{
		$cachetag = '-cfg-regions-byid'; 				
		$cfg = My_Cache_Model::get( $cachetag );
		
		if ( is_null( $cfg ) )
		{
		
			$sql = "
				select
				r.id, r.name, r.type, r.capital, k.image kingdom_image, k.name kingdom_name, 
				k.id kingdom_id, r.geography, r.clima, r.coords, k.color kingdom_color, r.status 
				from  regions r, kingdoms_v k
				where r.kingdom_id = k.id ";				
			
			$res = Database::instance() -> query( $sql );
			foreach ( $res as $region )
			{
				$cfg[$region->id]['id'] = $region -> id;
				$cfg[$region->id]['name'] = $region -> name;
				$cfg[$region->id]['capital'] = $region -> capital;
				$cfg[$region->id]['kingdom_name'] = $region -> kingdom_name;
				$cfg[$region->id]['kingdom_image'] = $region -> kingdom_image;
				$cfg[$region->id]['kingdom_id'] = $region -> kingdom_id;
				$cfg[$region->id]['geography'] = $region -> geography;				
				$cfg[$region->id]['clima'] = $region -> clima;				
				$cfg[$region->id]['kingdom_color'] = $region -> kingdom_color;				
				$cfg[$region->id]['status'] = $region -> status;				
				
				
			}
			
			My_Cache_Model::set( $cachetag, $cfg ); 
			
		}
		//var_dump($cfg);exit;
		return $cfg ;
		
	}

/**
	* Loads kingdoms configuration
	* @param none
	* @return array configuration
	*/
		
	public static function getcfg_kingdoms()
	{
		$cachetag = '-cfg-kingdoms'; 				
		$cfg = My_Cache_Model::get( $cachetag );
		
		if ( is_null( $cfg ) )
		{
		
			$sql = "select * from kingdoms_v where name != 'kingdoms.kingdom-independent' order by SUBSTRING_INDEX(image, '-', -1) asc";				
			
			$res = Database::instance() -> query( $sql );
			foreach ( $res as $kingdom )
				$cfg[$kingdom -> name] = $kingdom;
			
			My_Cache_Model::set( $cachetag, $cfg ); 
			
		}
		
		return $cfg ;
		
	}	
	
	/**
	* Get all diplomatic relations
	* @param none
	* @return none
	*/

	public static function get_cfg_diplomacyrelations()
	{
		
		$cachetag = '-diplomacyrelations'; 				
		$data = My_Cache_Model::get( $cachetag );
		
		if ( is_null( $data ) )
		{
			
			$sql = 
				"select 
				k1.name sourcekingdom_name, k2.name targetkingdom_name, dr.* 
				from diplomacy_relations dr, kingdoms_v k1, kingdoms_v k2
				where k1.id = dr.sourcekingdom_id
				and k2.id = dr.targetkingdom_id	
				and k1.name != 'kingdoms.kingdom-independent' 
				and k2.name != 'kingdoms.kingdom-independent'
				order by k1.id, k2.id ";			
			
			$drs = Database::instance() -> query( $sql ) -> as_array();			
			
			foreach ( $drs as $dr 	)
			{
				$data[ $dr -> sourcekingdom_id ][ $dr -> targetkingdom_id ]['id'] = $dr -> id;
				$data[ $dr -> sourcekingdom_id ][ $dr -> targetkingdom_id ]['type'] = $dr -> type;
				$data[ $dr -> sourcekingdom_id ][ $dr -> targetkingdom_id ]['timestamp'] = $dr -> timestamp;
				$data[ $dr -> sourcekingdom_id ][ $dr -> targetkingdom_id ]['signedby'] = $dr -> signedby;
			}			
			
			My_Cache_Model::set( $cachetag, $data ); 

		}
		
		return $data ;
		
	}
	
	/**
	* Loads Achievements configuration
	* @param none
	* @return array $cfg Achievements Configuration
	*/
		
	public static function getcfg_achievements()
	{
		
		$cachetag = '-cfgachievements'; 				
		$cfg = My_Cache_Model::get( $cachetag );
		
		if ( is_null( $cfg ) )
		{
		
			$sql = "select * from cfgachievements";
			
			$res = Database::instance() -> query( $sql );
			foreach ( $res as $achievement )
			{
				
				$cfg[$achievement -> tag][$achievement -> level] = $achievement;
			}
			
			My_Cache_Model::set( $cachetag, $cfg ); 
			
		}
		
		return $cfg ;
		
	}
	
	/**
	* Loads not expired promos
	* @param none
	* @return array $cfg
	*/
	
	static function get_valid_promo()
	{
		$cachetag = '-cfg-validpromo';        
		$cfg = My_Cache_Model::get( $cachetag );
        
		if (is_null($cfg)) {
            
			$sql = "
			SELECT * 
			FROM cfgpremiumbonuses_promos cp
			WHERE cp.enddate > NOW() ";
			
			$cfg = Database::instance() -> query($sql) -> as_array();

			if (!empty($cfg))
				$cfg = $cfg[0];
			
        My_Cache_Model::set($cachetag, $cfg);
    }        		
    //var_dump($cfg);exit;
		
		return $cfg;
		
	}
	
	/**
	* Get resources of all regions
	* @param   none
	* @return  array  $resources_a  array with info (region => resource)
	*/
	
	function get_resources_all_regions()
	{
		
		$data = array();
		$cachetag = '-cfg-regions-resources';        
		$data = My_Cache_Model::get( $cachetag );			
		if (is_null($data)) {
			
			$db = Database::instance();		
			$sql = "select r.id as rid, r.name, s.id as sid, st.type, sr.resource, sr.current, sr.max, sr.next_recharge
				from structures s, structure_types st, regions r, structure_resources sr
				where s.region_id = r.id
				and   s.structure_type_id = st.id
				and   sr.structure_id = s.id 
				order by r.name";
			
			$resources = $db -> query( $sql ) ;
		
			foreach ( $resources as $resource )
			{
				$data[$resource -> rid]['structuretype'] = $resource -> type;								
				$data[$resource -> rid]['resources'][$resource -> resource]['current']=$resource -> current;
				$data[$resource -> rid]['resources'][$resource -> resource]['max']=$resource-> max;
			}
			
			My_Cache_Model::set($cachetag, $data);
		
		}
		//var_dump($data);exit;
		return $data;
		
	}
	
	/*
	* Get all regions with structures
	* @param none
	* @return array $data
	*/
	
	function get_regions_structures( )
	{
		$data = array();
		$cachetag = '-cfg-regions-structures';        
		$data = My_Cache_Model::get( $cachetag );			
		
		if (is_null($data)) {
			
			$sql = "
			select st.supertype structure_type, r.id region_id, r.name region_name, s.id structure_id 
			from regions r, structures s, structure_types st
			where s.region_id = r.id 
			and   s.structure_type_id = st.id
			and   st.subtype != 'player'";
		
			$regions = Database::instance() -> query ( $sql ); 
		
			$i=0;
			foreach ( $regions as $region )
			{
				$data[$region-> structure_type][$region-> region_id]['structure_id'] = $region -> structure_id;				
				$data[$region-> structure_type][$region-> region_id]['region_name'] = $region -> region_name;				
			}
			
			My_Cache_Model::set($cachetag, $data);
		}
		
		return $data; 
		
	}
	
	/**
	* Loads Bonus Configuration
	* @param none
	* @return array $cfg
	*/
	
	static function get_premiumbonuses_cfg()
  {
    
		$cachetag = '-cfg-premiumbonuses';        
		$cfg = My_Cache_Model::get( $cachetag );
        
		if (is_null($cfg)) {
            
            $db = Database::instance();
            
            $sql = "select cp.id as id_bonus, cp.cutunit as cutunit, cp.name as bonusname, 
			cc.id as id_cut,cc.cut , cc.price, ce.id as id_promo, ce.name as promo_name, ce.discount , ce.startdate,ce.enddate
			from cfgpremiumbonuses cp 
			left outer join cfgpremiumbonuses_cuts cc on cp.id=cc.cfgpremiumbonus_id
			and cc.enddate > NOW()
			left outer join cfgpremiumbonuses_promos ce on ce.cfgpremiumbonus_id=cp.id
			and (ce.enddate > NOW() and ce.startdate <= NOW());";
            
            $res = $db -> query($sql);
            
            foreach ($res as $row) {
                /*nel vettore metto i dati da mettere nella cache*/
                $cfg[$row->bonusname]['id']                          = $row->id_bonus;
                $cfg[$row->bonusname]['cutunit']                     = $row->cutunit;
                $cfg[$row->bonusname]['name']                        = $row->bonusname;
                $cfg[$row->bonusname]['discount']                    = $row->discount;
                $cfg[$row->bonusname]['startdate']                   = $row->startdate;
                $cfg[$row->bonusname]['enddate']                     = $row->enddate;
                $cfg[$row->bonusname]['cuts'][$row->cut]['id']    = $row->id_cut;
                $cfg[$row->bonusname]['cuts'][$row->cut]['cut']   = $row -> cut;
                $cfg[$row->bonusname]['cuts'][$row->cut]['price'] = $row->price;
            }
			
            My_Cache_Model::set($cachetag, $cfg);
        }
        //var_dump($cfg);exit;
        return $cfg;
               
    }
		
		/**
		* Carica tutti gli item che una struttura può
		* craftare
		*/
		
		public function get_craftableitems_structuretype()		
		{
		
			$cachetag = '-cfg-craftableitems-structuretype';        
			$cfg = My_Cache_Model::get( $cachetag );
        
			if (is_null($cfg)) 
			{
    
				$sql = "select 
					st.type structure_type, 
					c1.id cfgitem_id, 
					cd.type, 
					c1.crafting_slot crafting_slot, 
					c1.tag destination_item_tag, 
					c1.name destination_item_name, 
					c1.description description, 
					c1.spare5 destination_item_minquantity, 
					c1.spare6 destination_item_maxquantity, 
					c1.church_id, 
					c1.spare2 craftingtime, 
					c2.id source_item_id, 
					c2.tag source_item_tag, 
					c2.name source_item_name, 
					cd.quantity, 
					c2.spare7 source_item_spare7
					from 
						cfgitems c1, 
						cfgitem_dependencies cd, 
						cfgitems c2, 
						structure_types_cfgitems sc,
						structure_types st
					where
						c1.id = cd.cfgitem_id
						and sc.structure_type_id = st.id
						and sc.cfgitem_id = c1.id		
						and c1.craftingenabled = true
						and c2.id = cd.source_cfgitem_id order by c1.tag asc";
					
				$res = Database::instance() -> query ( $sql );
				
				foreach ($res as $row) {
					
					$cfg[$row->structure_type][$row->destination_item_tag]['cfgitem_id'] = $row -> cfgitem_id ;
					$cfg[$row->structure_type][$row->destination_item_tag]['type'] = $row -> type ;
					$cfg[$row->structure_type][$row->destination_item_tag]['crafting_slot'] = $row -> crafting_slot ;
					$cfg[$row->structure_type][$row->destination_item_tag]['destination_item_name'] = $row -> destination_item_name ;
					$cfg[$row->structure_type][$row->destination_item_tag]['description'] = $row -> description ;
					$cfg[$row->structure_type][$row->destination_item_tag]['destination_item_minquantity'] = $row -> destination_item_minquantity ;
					$cfg[$row->structure_type][$row->destination_item_tag]['destination_item_maxquantity'] = $row -> destination_item_maxquantity ;
					$cfg[$row->structure_type][$row->destination_item_tag]['church_id'] = $row -> church_id ;
					$cfg[$row->structure_type][$row->destination_item_tag]['craftingtime'] = $row -> craftingtime ;
					$cfg[$row->structure_type][$row->destination_item_tag]['source_item_spare7'] = $row -> source_item_spare7 ;
					$cfg[$row->structure_type][$row->destination_item_tag]['requireditems'][$row -> source_item_tag]['source_item_name'] = $row -> source_item_name;
					$cfg[$row->structure_type][$row->destination_item_tag]['requireditems'][$row -> source_item_tag]['quantity'] = $row -> quantity;
					
				}
		
				My_Cache_Model::set($cachetag, $cfg);
			}
			
		
			//var_dump($cfg); exit;
			return $cfg;
		}
		
		/**
		* Carica tutti gli item craftabili
		* @param none
		* @return array		
		*/
		
		public function get_craftableitems()		
		{
		
			$cachetag = '-cfg-craftableitems';        
			$cfg = My_Cache_Model::get( $cachetag );
        
			if (is_null($cfg)) 
			{
    
				$sql = "
					select
					c1.id cfgitem_id, 
					cd.type, 
					c1.crafting_slot crafting_slot, 
					c1.tag destination_item_tag, 
					c1.name destination_item_name, 
					c1.description description, 
					c1.spare5 destination_item_minquantity, 
					c1.spare6 destination_item_maxquantity, 
					c1.church_id, 
					c1.spare2 craftingtime, 
					c2.id source_item_id, 
					c2.tag source_item_tag, 
					c2.name source_item_name, 
					cd.quantity, 
					c2.spare7 source_item_spare7
					from 
						cfgitems c1, 
						cfgitem_dependencies cd, 
						cfgitems c2
					where
						c1.id = cd.cfgitem_id
					and c2.id = cd.source_cfgitem_id 
					order by c1.tag asc";
					
				$res = Database::instance() -> query ( $sql );
				
				foreach ($res as $row) {
					
					$cfg[$row->destination_item_tag]['cfgitem_id'] = $row -> cfgitem_id ;
					$cfg[$row->destination_item_tag]['type'] = $row -> type ;
					$cfg[$row->destination_item_tag]['crafting_slot'] = $row -> crafting_slot ;
					$cfg[$row->destination_item_tag]['destination_item_name'] = $row -> destination_item_name ;
					$cfg[$row->destination_item_tag]['description'] = $row -> description ;
					$cfg[$row->destination_item_tag]['destination_item_minquantity'] = $row -> destination_item_minquantity ;
					$cfg[$row->destination_item_tag]['destination_item_maxquantity'] = $row -> destination_item_maxquantity ;
					$cfg[$row->destination_item_tag]['church_id'] = $row -> church_id ;
					$cfg[$row->destination_item_tag]['craftingtime'] = $row -> craftingtime ;
					$cfg[$row->destination_item_tag]['source_item_spare7'] = $row -> source_item_spare7 ;
					$cfg[$row->destination_item_tag]['requireditems'][$row -> source_item_tag]['source_item_name'] = $row -> source_item_name;
					$cfg[$row->destination_item_tag]['requireditems'][$row -> source_item_tag]['quantity'] = $row -> quantity;
					
				}
		
				My_Cache_Model::set($cachetag, $cfg);
			}
			
		
			//var_dump($cfg); exit;
			return $cfg;
		}
		
	
}	
