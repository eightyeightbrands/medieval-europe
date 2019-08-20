<?php defined('SYSPATH') OR die('No direct access allowed.');

class ST_Castle_1_Model extends Structure_Model
{


	public function init()
	{
		$this -> setCurrentLevel(1);
		$this -> setParenttype('castle');
		$this -> setSupertype('castle');
		$this -> setMaxlevel(1);
		$this -> setIsbuyable(false);
		$this -> setIssellable(false);
		$this -> setStorage(100000000);
		$this -> setWikilink('Castle');
	}


	// Funzione che costruisce i links relativi
	// @output: stringa contenente i links relativi a questa struttura

	public function build_common_links( $structure )
	{

		$links = parent::build_common_links( $structure );

		// Links peculiari per la struttura castello
		$links .= html::anchor( "/structure/donate/" . $structure -> id, Kohana::lang('structures_actions.global_deposit'), array('class' => 'st_common_command')) . "<br/>" ;

		$links .= html::anchor( "/structure/info/" . $structure -> id, Kohana::lang('structures_actions.global_info'), array('class' => 'st_common_command')) . "<br/>" ;

		$links .= html::anchor( "/terrain/buy",  Kohana::lang('structures_actions.terrain_buy'), array('class' => 'st_common_command')). "<br/>";
		$links .= html::anchor( "/house/index", Kohana::lang('structures_actions.house_buy'), array('class' => 'st_common_command')). "<br/>";
		$links .= html::anchor( "/shop/index", Kohana::lang('structures_actions.shop_buy'), array('class' => 'st_common_command')). "<br/>";
		$links .= html::anchor( "/boardmessage/index", Kohana::lang('boardmessage.announcementboard'), array('class' => 'st_common_command')) . "<br/>" ;
		return $links;
	}

	public function build_special_links( $structure )
	{

		$links = parent::build_special_links( $structure );

		$links .= html::anchor(
			"/structure/rest/" . $structure -> id, Kohana::lang('global.rest'),
				array('title' => Kohana::lang('global.rest'), 'class' => 'st_special_command'))
			. "<br/>";

		return $links;
	}

/**
* Returns all the regions controlled by
* the castle.
* @param int castle id
* @param int castle region id
* @return areay array with controlled regions plus the region itself.
*/

function get_controlled_regions( $structure_id, $region_id )
{
	kohana::log('info', "-> Getting controlled regions:" . $structure_id . " - " . $region_id);
	$regions = array();
	$db = Database::instance();
	$sql = "select r.id
	  from structures s, structure_types st, regions r
		where s.structure_type_id = st.id
		and   s.region_id = r.id
		and   st.type = 'nativevillage_1'
		and   s.parent_structure_id = " . $structure_id  ;

		kohana::log('info', "-> Controlled regions sql: " . $sql);


	$res = $db -> query ( $sql );
	foreach ( $res as $region )
		$regions[] = ORM::factory('region', $region -> id );
	$regions[] = ORM::factory('region', $region_id );

	kohana::log('info', "-> Controlled regions results:" . count($regions) );



	return $regions;

}


}
