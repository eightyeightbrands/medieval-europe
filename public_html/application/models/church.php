<?php defined('SYSPATH') OR die('No direct access allowed.');

class Church_Model extends ORM
{	
	protected $has_many = array('structures_type', 'church_dogmabonuses');
	protected $belongs_to = array('religion');
	
	/**
	* Carica info sulla chiesa
	* @param: int $id ID Chiesa
	* @return: vettore info
	*/
	
	static function get_info( $church_id )	
	{
		
		$db = Database::instance();
		
		$church = ORM::factory('church', $church_id);
		
		$info = array (
			'followers' => 0,
			'parishchurches' => 0,
			'percentage' => 0,
			'dogmabonuses' => null,			
			'structures' => null,
		);
				
		$info['tag'] = $church -> name;
		$info['name'] = 'religion.church-' . $church -> name;
		$info['holytexturl'] = $church -> holytexturl;
		$info['followers'] = ORM::factory('character') -> where ( 'church_id', $church -> id ) -> count_all();
		
		//var_dump($church->name);exit;
		
		if ($church -> name != 'nochurch')
		{
			
			$structures = $db -> query ( "
			select s.id, st.type, r.id region_id, r.name region_name 
			from 	structures s, structure_types st, regions r
			where s.structure_type_id = st.id 
			and   s.region_id = r.id 
			and   st.church_id = " . $church -> id );
			
			foreach ($structures as $structure)		
				$s[$structure -> type][] = $structure;		
			
			$info['structures'] = $s;
			if (isset($s['religion_4']))
				$info['parishchurches'] = count($s['religion_4']);
			
			$headquarter = current($info['structures']['religion_1']);
			reset($info['structures']['religion_1']);
			$structure_hq = ORM::factory('structure', $headquarter -> id );		
		
			if ( $structure_hq -> contains_item( 'relic_' . $info['tag']) == true )
			{
				$dogmabonuses = $db -> query("
				SELECT cd.id, cd.bonus, cd.url
				FROM  church_dogmabonuses c, cfgdogmabonuses cd
				WHERE c.cfgdogmabonus_id = cd.id
				AND   c.church_id = {$church->id}");		
					
				$info['dogmabonuses'] = $dogmabonuses -> as_array();
			}
			
		}
				
		$allchars = ORM::factory('character') -> count_all();
		$info['percentage'] = round($info['followers'] / $allchars,4) * 100;
						
		return $info;
	
	}
	
	/**
	* Ritorna i valori per una dropdown con tutte le strutture di una chiesa meno quella chiamante.
	* @param int $church_id ID chiesa
	* @param int $callerstructure_id ID struttura chiamante
	* @return: array $data dati per dropdown
	*/
	
	static public function helper_allchurchstructuresdropdown( $church_id, $callerstructure_id )
	{
		// carichiamo tutte le strutture della chiesa
		// e costruiamo il dropdown
		
		$sql = 
		"
		SELECT s.id, st.name structure_name, r.name region_name
		FROM   structures s, structure_types st, regions r
		WHERE  s.structure_type_id = st.id
		AND    s.region_id = r.id 
		and    s.id != {$callerstructure_id}
		AND    st.church_id = {$church_id}
		";
				
		$churchstructures = Database::instance() -> query($sql);
		
		$data = array();
			
		foreach ( $churchstructures as $churchstructure )
			$data[ $churchstructure -> id ] = kohana::lang($churchstructure -> structure_name) . ' - ' . kohana::lang($churchstructure -> region_name) ;
		
		return $data;
	}

	/*
	* Calcola il costo del prossimo bonus dogma
	* @param    none
	* @return   costo faith points
	*/
	public function get_cost_next_dogma_bonus()
	{
		// Conto quanti bonus possiede la chiesa
		$bonus_owned = count($this -> church_dogmabonuses);
		
		return 10000 + (10000 * pow($bonus_owned,2));
	}
	
	/*
	* Verifica se la chiesa ha un certo dogmabonus
	* @param int $church_id ID chiesa
	* @param  string $bonus nome del bonus
	* @return bool $found true/false bonus trovato
	*/
	public function has_dogma_bonus( $church_id, $bonus )
	{
		
		$found = false;
		
		$churchinfo = Church_Model::get_info( $church_id );					
		
		foreach ((array) $churchinfo['dogmabonuses'] as $dogmabonus )
		{
			if ($dogmabonus -> bonus == $bonus )
			{
				$found = true;
				break;
			}
		}
		return $found;
	}			
	
	/*
	* Restituisce il numero dei bonus posseduti dalla chiesa
	* @param int  $id ID Chiesa
	* @return int int 
	*/
	
	static public function count_dogma_bonus( $church_id )
	{		
		$info = Church_Model::get_info( $church_id );
		
		return count($info['dogmabonuses']);		
	}
	
	/*
	* Restituisce il numero di malus presenti contro la mia chiesa
	* @param   int    $church_id    id della chiesa contro cui ci sono i malus
	* @param   string $malus_name   nome del malus da trovare
	* @return  int    $num_malus    numero dei malus trovati
	*/
	public function get_num_malus_against_my_church($church_id, $malus_name)
	{
		$db = Database::instance();
		// Cerco quanto malus ci sono contro la chiesa
		$malus = $db -> query
		( 
			"select
				*
			from
				church_dogmabonuses cdb,
				cfgdogmabonuses cfg
			where
				cfg.id = cdb.cfgdogmabonus_id and
				cfg.malus_church_id = ".$church_id." and
				cfg.bonus like '%".$malus_name."%'
			"
		);
		// Restituisce il numero di record trovati
		return count($malus);
	}
	
}
