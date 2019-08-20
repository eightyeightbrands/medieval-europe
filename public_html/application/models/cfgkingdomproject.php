<?php defined('SYSPATH') OR die('No direct access allowed.');

class CfgKingdomproject_Model extends ORM
{
	protected $has_many = array( 'cfgkingdomproject_dependency' );	
	
	
	const FPFACTOR = 6.3; // 7 days expected of pray with an average of 0,0 faith points.
	
	
	/**
	* ritorna info sul progetto
	* @param none
	* @return array
	*    - general: contiene oggetto cfgkingdomprojects	
	*    - required_structure: contiene oggetto structure_type della struttura prodotta
	*    - produced_structure: contiene oggetto structure_type della struttura prodotta
	*/
	
	function get_info()
	{
		
		$info = array();	
		$char = Character_Model::get_info( Session::instance()->get('char_id') );
		
		// oggetto completo
		$info['obj'] = $this ;
		
		//required structure
		if ( !is_null ( $this->required_structure_type_id ) )
			$info['required_structure'] = ORM::factory('structure_type', $this->required_structure_type_id );
		else
			$info['required_structure'] = NULL;
		
		// struttura prodotta
		if ( in_array( $this -> owner, array ('church_level_1', 'church_level_2', 'church_level_3' ) ) )			
			$info['produced_structure'] = ORM::factory('structure_type')
				-> where ( array( 
					'type' => $this -> tag, 
					'church_id' => $char -> church_id ) ) -> find();					
		else
			$info['produced_structure'] = ORM::factory('structure_type')
				-> where ( array( 
					'type' => $this -> tag)) -> find();	
		
		foreach( $this -> cfgkingdomproject_dependency as $dep )		
			$info['required_items'][$dep -> cfgitem -> name] = $dep -> quantity;		
		
		//kohana::log('debug', kohana::debug($info));
		
		return $info;
	
	}
	
	/**
	* Verifies if its possible to build a structure
	* @param: obj $cfgkingdomproject CfgKingdomproject_Model Project that needs to be Built 
  * @param: obj $producedstructuretype Structure_Type_Model Type of structure produced
	* @param: obj $sourceregion Region_model region from where the project is launched
	* @param: obj $destregion Region_Model region where the building should be created
	* @param: obj $sourcestructure Structure_Model Structure from where the project is launched
	* @return: array $v
	*          result: true or false
	*          cost: cost in faith point
	*          message: message
	*/
	
	function checkprojectfeasibility( 
		$cfgkingdomproject, 
		$producedstructuretype, 
		$sourceregion, 
		$destregion, 
		$sourcestructure )
	{		
		
		
		$v = array( 'result' => false, 'message' => null, 'cost' => 0 );				
		$character = Character_Model::get_info( Session::instance()->get('char_id') );
		$db = Database::instance();
		
		///////////////////////////////////////////////
		// Controlli comuni
		///////////////////////////////////////////////
				
		///////////////////////////////////////////////
		// controlliamo se il char ha il ruolo giusto
		///////////////////////////////////////////////
		
		$role = $character -> get_current_role ();		
		
		if ( is_null( $role ) or $role -> tag != $cfgkingdomproject -> owner )
		{
			kohana::log('debug', '-> Char is no owner or not enough permissions');
			$v['message'] = kohana::lang('global.operation_not_allowed');
			$v['result'] = false;
			return $v;
		}
		
		///////////////////////////////////////////////
		// Se è richiesta una struttura, la struttura 
		// richiesta deve esistere.
		///////////////////////////////////////////////
		
		if ( ! is_null( $cfgkingdomproject -> required_structure_type_id ) )
		{
			$required_structure = ORM::factory('structure_type', $cfgkingdomproject -> required_structure_type_id );
			
			if ( is_null ( $destregion -> get_structure( $required_structure -> type, 'type' )))
			{
				kohana::log('debug', '-> Required structure do not exist.');
				$v['message'] = kohana::lang('kingdomprojects.neededstructurenotexisting');
				$v['result'] = false;
				return $v;
			}
		}
		
		/////////////////////////////////////////////////////
		// Non deve esistere una struttura dello stesso tipo
		/////////////////////////////////////////////////////
		
		$structure = $destregion 
			-> get_structure( $producedstructuretype -> supertype ); 		
		
		if ( !is_null( $structure ) 
				and $structure -> structure_type -> type == $producedstructuretype -> type )
		{
			kohana::log('debug', '-> Structure already exists.');
			$v['message'] = kohana::lang('kingdomprojects.structurealreadyexist');
			$v['result'] = false;
			return $v;
		}		
		
		///////////////////////////////////////////////
		// controlliamo se un progetto con la stesso 
		// tipo di struttura è in corso
		///////////////////////////////////////////////
		
		$runningprojects = Database::instance() -> query("
		SELECT *
		FROM  kingdomprojects 
		WHERE region_id = {$destregion->id}
		AND   cfgkingdomproject_id = {$cfgkingdomproject->id}
		AND   status in ('collectingmaterial','building')") -> as_array();
		
		if ( count($runningprojects) > 0 )		
		{
			kohana::log('debug', '-> Similar project is already existing.');
			$v['message'] = kohana::lang('kingdomprojects.projectisinprogress');
			$v['result'] = false;
			return $v;
		}			

		///////////////////////////////////////////////
		// Controlli per strutture di tipo religioso
		///////////////////////////////////////////////
						
		if ( $producedstructuretype -> subtype == 'church' )
		{
					
			// Due o piu' cantieri possono coesistere se
			// la chiesa è diversa (chi finisce prima, conquista il diritto)
			// non possono coesistere due cantieri per la stessa chiesa
			
			$runningprojects = ORM::factory('kingdomproject') ->
					where ( array( 
					'region_id' => $destregion ->  id, 
					'cfgkingdomproject_id' => $cfgkingdomproject -> id) ) -> find_all();
	
			foreach ( $runningprojects as $runningproject )
			{
				$info = $runningproject -> get_info();
				
				kohana::log('debug', $info['project'] -> id  . '-' . $info['builtstructure'] -> church_id ); 
				
				if ( $info['builtstructure'] -> church_id == $producedstructuretype -> church_id and $info['project'] -> status != 'completed' )
				{					
					$v['message'] = kohana::lang('kingdomprojects.projectisinprogress');
					$v['result'] = false;
					return $v;
				}
			}			
			
			// nella regione di destinazione non deve esistere
			// nessuna altra struttura religiosa di un altra chiesa
			
			$sql = "select count(s.id) n from structures s, structure_types st
			where s.structure_type_id = st.id
			and   s.region_id = " . $destregion -> id . "
			and   st.subtype = 'church'
			and   st.church_id != " . $producedstructuretype -> church_id ;			
			
			$res = $db -> query ( $sql ) -> as_array();
			if ( $res[0] -> n > 0 )
			{
				$v['message'] = kohana::lang('kingdomprojects.churchstructureexists');
				$v['result'] = false;
				return $v;
			}

			// find distance from source structure
			
			$distancedata = Region_Model::findminmaxdistance( $destregion, array($sourceregion -> name ));	
			//kohana::log('debug', kohana::debug($distancedata));
			kohana::log('debug', "-> Max Distance: {$distancedata['maxdistance']}");
			
			// Costs is distance from parent structure * followers * fpfactor.
			
			$churchinfo = Church_Model::get_info($producedstructuretype -> church_id);
			kohana::log('debug', "-> Followers: {$churchinfo['followers']}");
			$v['cost'] = round($distancedata['maxdistance'] * self::FPFACTOR * $churchinfo['followers'],0);
			kohana::log('debug', "-> Cost in FP: {$v['cost']}");
				
		}
			
		///////////////////////////////////////////////
		// Controlli per strutture di altro tipo
		///////////////////////////////////////////////
		
		// For Government Buildings, destination region must be in the same kingdom
		
		if ( $producedstructuretype -> subtype != 'church' )
		{

      if ( $destregion -> kingdom -> id != $sourceregion -> kingdom -> id )
			{
				kohana::log('debug', '-> Region is not in kingdom.');
				$v['message'] = kohana::lang('global.operation_not_allowed');
				$v['result'] = false;
				return $v;
			}
		
			///////////////////////////////////////////////
			// controlliamo che la regione di destinazione
			// sia controllata (se il progetto non è religioso)
			///////////////////////////////////////////////
			
			if ( $destregion -> kingdom -> get_name()  == 'kingdoms.kingdom-independent' )
			{
				kohana::log('debug', '-> Region is not controlled.');
				$v['message'] = kohana::lang('kingdomprojects.regionisnotcontrolled');
				$v['result'] = false;
				return $v;
			}
		
			////////////////////////////////////////////////////
			// se il progetto è harbor, allora la regione deve 
			// avere almeno un path mixed			
			////////////////////////////////////////////////////
			
			if ( $producedstructuretype -> type == 'harbor' )
			{
				$existsmixed = false;
				foreach ( $destregion -> region_paths as $path )
					if ( $path -> type == 'mixed' )
						$existsmixed = true;
				
				if ( $existsmixed == false )
				{
					$v['message'] = kohana::lang('kingdomprojects.cantbuildharborhere');
					$v['result'] = false;
					return $v;
				}
			}
		}	
		
		if ( $producedstructuretype -> subtype != 'church' )
			$v['message'] = kohana::lang('kingdomprojects.projectisfeasible');
		else
			$v['message'] = kohana::lang('kingdomprojects.projectisfeasiblecost', $v['cost']);
			
		$v['result'] = true;
		return $v;
			
	}

}
?>
