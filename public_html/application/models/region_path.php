<?php defined('SYSPATH') OR die('No direct access allowed.');

class Region_Path_Model extends ORM
{
	const ENERGY_FOR_30_MIN = 2;
	const GLUT_FOR_30_MIN = 1;

	protected $table_name = "regions_paths";
	protected $belongs_to = array('region');

	/**
	* Ritorna il traveltime da regione a a regione b,
	* tenendo conto anche delle penali dovute al peso
	* @param array $par vettore di parametri
	* par['time']: tempo percorso in minuti.
	* @return array $travelinfo
	*/

	function get_travelinfo( $par )
	{

		$travelinfo = array(
			'normaltraveltime' => 0,
			'realtraveltime' => 0,
			'cost' => 0,
		);

		// Tempo base di spostamento
		$travelinfo['normaltraveltime'] = $par['time'] ;

		// Tempo reale di spostamento dopo applicazione di malus/bonus
		$travelinfo['realtraveltime'] = $travelinfo['normaltraveltime'];

		// Peso in eccesso trasportato dal char
		$weightinexcess = $par['weightinexcess'];

		/////////////////////////////////////////////////////////////////////////
		// Penalit� per il peso:
		// Via terra, � il 10% di tempo in pi�, per ogni 5 Kili in eccesso.
		// Via mare, il tempo rimane sempre lo stesso, ma la penalit� per il peso
		// comporta un costo maggiore.
		// Se il cliente ha un carro, ha un bonus poich� le fasi di carico/scarico
		// sono pi� efficienti
		/////////////////////////////////////////////////////////////////////////

		// Spostamento x terra, 10% di tempo in pi� per ogni 5 kili di eccesso

		if ( $par['type'] == "land" || $par['type'] == "fastland" )
		{
			$travelinfo['realtraveltime'] = $travelinfo['normaltraveltime'] * (100 + ( 10 * ( $weightinexcess/5000 )) ) / 100;
			$travelinfo['cost'] = 0;
		}

		// Spostamento x mare, il costo cresce a seconda del prezzo

		if ( $par['type'] == "mixed" || $par['type'] == "sea" || $par['type'] == "fastsea" )
		{
			$travelinfo['cost'] = round( $travelinfo['normaltraveltime']/60, 0 );
			$travelinfo['realtraveltime'] = $travelinfo['normaltraveltime'];

			// per viaggi via mare, il carretto non conta, ricalcoliamo il peso in eccesso

			$weightinexcess = $par['char'] -> get_weightinexcess( false );
			$parameter = 20000;
			if (
				Character_Model::has_item( $par['char']->id, 'cart_1', 1 ) or
				Character_Model::has_item( $par['char']->id, 'cart_2', 1 ) or
				Character_Model::has_item( $par['char']->id, 'cart_3', 1 ) )
					$parameter = 40000;

			$travelinfo['cost'] += 1 * round($weightinexcess/$parameter,0);

		}


		kohana::log('info', '========================================');
		kohana::log('info', '-> Travel type: ' . $par['type'] );
		kohana::log('info', '-> Source: ' . $par['sourcename'] );
		kohana::log('info', '-> Destination: ' . $par['destname'] );
		kohana::log('info', '-> Original time: ' . $travelinfo['normaltraveltime'] . ' minutes - ' . Utility_Model::secs2hmstostring($travelinfo['normaltraveltime'] * 60, 'hours' ));
		kohana::log('info', '-> Excess weight: ' . $weightinexcess );
		kohana::log('info', '-> Time after weight penalty: ' . $travelinfo['realtraveltime'] .' - '. Utility_Model::secs2hmstostring($travelinfo['realtraveltime'] * 60, 'hours'));
		kohana::log('info', '-> Travel costs: ' . $travelinfo['cost'] ) ;

		/////////////////////////////////////////////////////////////////////////
		// Penalit�: 50% in pi� se il char non indossa un paio di scarpe
		// Solo applicabile ai viaggi via terra.
		/////////////////////////////////////////////////////////////////////////

		if ( ($par['type'] == "land" || $par['type'] == "fastland" ) and is_null($par['hasshoes']) )
		{
			$travelinfo['realtraveltime'] = $travelinfo['realtraveltime'] * 150 / 100;
		}

			kohana::log('info', '-> Time after shoes malus penalty: ' . $travelinfo['realtraveltime'] .' - '. Utility_Model::secs2hmstostring($travelinfo['realtraveltime']* 60, 'hours') );

		/////////////////////////////////////////////////////////////////////////
		// Energy and glut
		/////////////////////////////////////////////////////////////////////////

		$travelinfo['energy'] = min( 50, round(($travelinfo['realtraveltime'] / 30 ) * self::ENERGY_FOR_30_MIN ) );
		$travelinfo['energytext'] = kohana::lang('map.requiredenergy'). ": <span class='value'>" . intval($travelinfo['energy'] / 50 * 100) . "%</span>";
		$travelinfo['glut'] = min( 50, round(($travelinfo['realtraveltime'] / 30 ) * self::GLUT_FOR_30_MIN ) );
		$travelinfo['gluttext'] = kohana::lang('map.requiredglut'). ": <span class='value'>" . intval($travelinfo['glut'] / 50 * 100) . "%</span>";
		$travelinfo['type'] = $par['type'];

		//kohana::log('debug', '-> Required energy: ' . $travelinfo['energy'] );
		//kohana::log('debug', '-> Required glut: ' . $travelinfo['glut'] );

		//////////////////////////////////////////////////////////////////////////
		// Penalit�: se il char non ha abb. energia o saziet�
		// solo per viaggi via terra.
		//////////////////////////////////////////////////////////////////////////

		//kohana::log('debug', 'Checking for energy and glut...' );

		if ( ($par['type'] == "land" || $par['type'] == "fastland" )
			and
				(
				$par['char']->energy < ( $travelinfo['energy'] )
				or
				$par['char']->glut < $travelinfo['glut'] )
				)
			$travelinfo['realtraveltime'] = $travelinfo['realtraveltime'] * 4;

		kohana::log('info', '-> Time after glut/energy malus: ' . $travelinfo['realtraveltime'] .' - '. Utility_Model::secs2hmstostring($travelinfo['realtraveltime']* 60, 'hours') );

		/////////////////////////////////////////////////////////////////////////
		// Penalit�: 400% in pi� se il char si sposta via mare e non ha i
		// soldi necessari
		/////////////////////////////////////////////////////////////////////////

		if ( Character_Model::get_premiumbonus($par['char'] -> id, 'travelerpackage') !== false )
			$travelinfo['cost'] = 0;

		//kohana::log('debug', '-> Type: ' . $par['type']);

		if ($par['type'] == "sea" || $par['type'] == "mixed" || $par['type'] == 'fastsea' )
		{
			if ( ! $par['char'] -> check_money( $travelinfo['cost'] ) )
				$travelinfo['realtraveltime'] = $travelinfo['realtraveltime'] * 4;
		}

		kohana::log('debug', '-> Time after Cost check: ' . $travelinfo['realtraveltime'] . ' - '. Utility_Model::secs2hmstostring($travelinfo['realtraveltime']* 60, 'hours') );

		/////////////////////////////////////////////////////////////////////////
		// Applicazione bonus su viaggi via terra e mare
		/////////////////////////////////////////////////////////////////////////

		if ( Character_Model::get_premiumbonus($par['char'] -> id, 'travelerpackage') !== false	)
		{
			$travelinfo['realtraveltime'] = intval( $travelinfo['realtraveltime'] * 50 / 100 );
		}

		kohana::log('info', '-> Real Travel time after traveler package check: ' . Utility_Model::secs2hmstostring($travelinfo['realtraveltime'] * 60, 'hours'));

		// server speed

		$travelinfo['realtraveltime'] = max (1, $travelinfo['realtraveltime']/Kohana::config('medeur.serverspeed'));

		kohana::log('info', '-> Real Travel time after applying bonus speed: ' . Utility_Model::secs2hmstostring($travelinfo['realtraveltime'] * 60, 'hours'));

		$travelinfo['realtraveltimetext'] = kohana::lang('global.traveltime') . "<span class='value'>" .
			Utility_Model::secs2hmstostring($travelinfo['realtraveltime'] * 60, 'hours') . "</span>";

		$travelinfo['costtext'] = kohana::lang('global.price') . ": <span class='value'>" . $travelinfo['cost'] . "</span>";

		kohana::log('info', '-> Time after horse or noble bonuses: ' . $travelinfo['realtraveltime'] .'-'. Utility_Model::secs2hmstostring($travelinfo['realtraveltime']* 60) );

		kohana::log('info', '========================================');

		return $travelinfo;

	}

	/**
	* Return the distance between two regions in km
	* @param source_region source region
	* @param dest_region target region
	* @return distance
	*/

	function compute_distance( $source_regionname, $target_regionname )
	{
		$cfg_regions = Configuration_Model::get_cfg_regions();

		// debug missing coordinates
		if( empty($cfg_regions[$source_regionname]) || empty($cfg_regions[$target_regionname]) ) {
			kohana::log('info', '-> Attempting to compute distance between region: ' . $source_regionname . ' and ' . $target_regionname . ' failed.' );
		}

		list( $source_x, $source_y) = explode( '.', $cfg_regions[$source_regionname] -> coords);
		list( $target_x, $target_y) = explode( '.', $cfg_regions[$target_regionname] -> coords);

		$delta_x = abs( $source_x - $target_x );
		$delta_y = abs( $source_y - $target_y );

		kohana::log( 'debug', 'coord_s: ' . $source_x . '-' . $source_y);
		kohana::log( 'debug', 'coord_t: ' . $target_x . '-' . $target_y);
		kohana::log( 'debug', 'delta_x: ' . $delta_x);
		kohana::log( 'debug', 'delta_y: ' . $delta_y);

		$distance = intval(sqrt( $delta_x*$delta_x + $delta_y*$delta_y));

		return $distance;
	}

	/**
	* Verifies if a fastpath track crosses hostile regions.
	* @param obj $char Character
	* @param obj $currentregion Region where char is
	* @param array $fasttrackpath Path (Crossed Regions)
	* @return boolean
	*/

	function ispathcrossable( $char, $currentregion, $fasttrackpath )
	{
		//var_dump($fasttrackpath['crossedregions'])	;exit;

		// per ogni regione attraversata controlliamo se � possibile attraversarla
		// movetobattlefield � forzato a false poich� nel caso di fastland  e delle
		// regioniintermedie non � applicabile

		foreach ($fasttrackpath['crossedregions'] as $crossedregion )
		{


			$crossedregionobj = ORM::factory('region', $crossedregion -> region_id);
			kohana::log('debug', "-> {$char -> name}: Checking if its possible to cross region: " . $crossedregionobj -> name );
			$possibletocross = Region_Model::canmoveto( $char, $crossedregionobj, $currentregion, false, $message );
			kohana::log('debug', "-> {$char -> name}: Checking if its possible to cross region: " . $crossedregionobj -> name . ': ' . $possibletocross );

			if ( $possibletocross == false )
				return false;
		}

		return true;

	}
}
