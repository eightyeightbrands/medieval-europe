<?php defined('SYSPATH') OR die('No direct access allowed.');

class Character_NPC_Model extends Character_Model
{
	protected $table_name = 'characters';
	protected $rate;
	protected $maxhealth;
	protected $respawntime;
	protected $maxnumber;
	protected $headequipment;
	protected $bodyequipment;
	protected $legsequipment;
	protected $feetequipment;
	protected $righthandequipment;
	protected $lefthandequipment;	
	protected $silvercoins;
	protected $ishuman = false;
	
	function create( $name )
	{			
	
		$this -> setUser_id(2);		
		$this -> setType('npc');
		$this -> setName($name);
		
		// Set birth region
		
		$regions = Configuration_Model::get_cfg_regions();
		foreach ($regions as $name => $data )
			if ($data -> type == 'land')
				$regioncandidates[] = $data;				
		
		$birthregion = $regioncandidates[array_rand($regioncandidates,1)];
		
		$this -> setChurch_id(4);
		$this -> setRegion_Id($birthregion->id);
		$this -> setPosition_Id($birthregion->id);		
		$this -> setBirthDate(time());
		$this -> setBirth_Region_Id($birthregion->id);
		
	}
	
	function specializeNpc() {}
	
	function npcai() {
		
		kohana::log('debug', '------ NPC AI ------');
		
		$this -> modify_energy( 50, true, 'resetenergy' );
		
	}
	
	/*
	* move to a random region
	* @params none
	* @returns none
	*/
	
	function move() 
	{
			
		// find adjacent regions
		$currentregion = ORM::factory('region', $this -> getPosition_Id() );
		
		kohana::log('debug', "npc: {$this -> id}, finding adjacent regions for region: {$currentregion->name}...");
		$adjacentregions = Region_Model::find_adjacentregions( $currentregion );
		if (is_null($adjacentregions))
			return;
		
		$targetregion = ORM::factory('region', array_rand($adjacentregions, 1));
		
		kohana::log('debug', "-> Target Region: {$targetregion->name}");
		//var_dump($targetregion); exit;
		
		if ($targetregion -> type == 'land' )
		{
		
			$par[0] = $targetregion;
			$par[1] = false;
			$par[2] = $this;
			
			$ca = Character_Action_Model::factory("move");
			$rc = $ca -> do_action( $par, $message );
			
			if (!$rc)
				kohana::log('debug', "npc: {$this -> 	id}, action: move dest: {$targetregion -> name} failed, reason:" . Database::instance() -> escape ($message));
			else
				kohana::log('debug', "npc: {$this -> id}, action: move dest: {$targetregion -> name} OK");
		}	
		
	}
	
	/**
	* Respawn a NPC
	* @param none
	* @return none
	*/
	
	function respawn()
	{
				
		$this -> modify_health($this -> getMaxHealth(), true);
		$this -> modify_energy(50, true, 'respawn');
		$this -> modify_glut(50, true);
		$this -> status = null;
		//$this -> equip();
		$this -> save();			
		
	}
	
	/*
	* Equippa l' NPC con l' equipaggiamento e gli oggetti iniziali.
	* @param none
	* @return none

	
	function equip()
	{			
	
		// fornisci soldi
		
		$this->modify_coins($this->getSilvercoins());
	
		kohana::log('debug', '-> Trying to equip: ' . $this -> getHeadEquipment());
		
		$head= $this -> get_bodypart_item( 'head' );
		if (is_null($head))
		{
			if (!empty($this -> getHeadEquipment()))
			{
				$head = Item_Model::factory( null, $this -> getHeadEquipment() );						
				$head -> additem( 'character', $this -> id , 1, true );
			}
		}
		else
		{
			$head -> quality = 100;
			$head -> save();
		}		
		
		kohana::log('debug', '-> Trying to equip: ' . $this -> getBodyEquipment());
		
		$body = $this -> get_bodypart_item( 'body' );
		if (is_null($body))
		{
			if (!empty($this -> getBodyEquipment()))
			{
				$body = Item_Model::factory( null, $this -> getBodyEquipment() );						
				$body -> additem( 'character', $this -> id , 1, true );
			}
		}
		else
		{
			$body -> quality = 100;
			$body -> save();
		}	

		kohana::log('debug', '-> Trying to equip: ' . $this -> getLegsEquipment());
		
		$legs = $this -> get_bodypart_item( 'legs' );
		
		if (is_null($legs))
		{
			if (!empty($this -> getLegsEquipment()))
			{
				$legs = Item_Model::factory( null, $this -> getLegsEquipment() );						
				$legs -> additem( 'character', $this -> id , 1, true );
			}
		}
		else
		{
			$legs -> quality = 100;
			$legs -> save();
		}	
		
		kohana::log('debug', '-> Trying to equip: ' . $this -> getfeetEquipment());
		
		$feet = $this -> get_bodypart_item( 'feet' );
		if (is_null($feet))
		{
			if (!empty($this -> getFeetEquipment()))
			{
				$feet = Item_Model::factory( null, $this -> getFeetEquipment() );						
				$feet -> additem( 'character', $this -> id , 1, true );
			}
		}
		else
		{
			$feet -> quality = 100;
			$feet -> save();
		}	
		
		kohana::log('debug', '-> Trying to equip: ' . $this -> getrighthandEquipment());
		
		$righthand= $this -> get_bodypart_item( 'right_hand' );
		if (is_null($righthand))
		{
			if (!empty($this -> getRighthandEquipment()))
			{
				$righthand = Item_Model::factory( null, $this -> getRighthandEquipment() );						
				$righthand -> additem( 'character', $this -> id , 1, true );
			}
		}
		else
		{
			$righthand -> quality = 100;
			$righthand -> save();
		}	
		
		kohana::log('debug', '-> Trying to equip: ' . $this -> getlefthandEquipment());
		
		$lefthand = $this -> get_bodypart_item( 'left_hand' );
		if (is_null($lefthand))
		{
			if (!empty($this -> getlefthandEquipment()))
			{
				$lefthand = Item_Model::factory( null, $this -> getlefthandEquipment() );						
				$lefthand -> additem( 'character', $this -> id , 1, true );
			}
		}
		else
		{
			$lefthand -> quality = 100;
			$lefthand -> save();
		}	
		
		
	}
	
	*/
	
	public function getId(){
		return $this->id;
	}

	public function setId($id){
		$this->id = $id;
	}
	
	public function getType(){
		return $this->type;
	}

	public function setType($type){
		$this->type = $type;
	}

	public function getName(){
		return $this->name;
	}

	public function setName($name){
		$this->name = $name;
	}

	public function getHealth(){
		return $this->health;
	}

	public function setHealth($health){
		$this->health = $health;
	}

	public function getSex(){
		return $this->sex;
	}

	public function setSex($sex){
		$this->sex = $sex;
	}

	public function getGlut(){
		return $this->glut;
	}

	public function setGlut($glut){
		$this->glut = $glut;
	}

	public function getStr(){
		return $this->str;
	}

	public function setStr($str){
		$this->str = $str;
	}

	public function getDex(){
		return $this->dex;
	}

	public function setDex($dex){
		$this->dex = $dex;
	}

	public function getIntel(){
		return $this->intel;
	}

	public function setIntel($intel){
		$this->intel = $intel;
	}

	public function getCar(){
		return $this->car;
	}

	public function setCar($car){
		$this->car = $car;
	}

	public function getCost(){
		return $this->cost;
	}

	public function setCost($cost){
		$this->cost = $cost;
	}

	public function getEnergy(){
		return $this->energy;
	}

	public function setEnergy($energy){
		$this->energy = $energy;
	}

	public function getUser_id(){
		return $this->user_id;
	}

	public function setUser_id($user_id){
		$this->user_id = $user_id;
	}

	public function getStatus(){
		return $this->status;
	}

	public function setStatus($status){
		$this->status = $status;
	}

	public function getNpctag(){
		return $this->npctag;
	}

	public function setNpctag($npctag){
		$this->npctag = $npctag;
	}

	public function getRegion_id(){
		return $this->region_id;
	}

	public function setRegion_id($region_id){
		$this->region_id = $region_id;
	}

	public function getPosition_id(){
		return $this->position_id;
	}

	public function setPosition_id($position_id){
		$this->position_id = $position_id;
	}

	public function getBirth_region_id(){
		return $this->birth_region_id;
	}

	public function setBirth_region_id($birth_region_id){
		$this->birth_region_id = $birth_region_id;
	}

	public function getBirthdate(){
		return $this->birthdate;
	}

	public function setBirthdate($birthdate){
		$this->birthdate = $birthdate;
	}
	
	public function getChurch_Id(){
		return $this->church_id;
	}

	public function setChurch_Id($church_id){
		$this->church_id = $church_id;
	}
	
	public function getRespawntime(){
		return $this->respawntime;
	}

	public function setRespawntime($respawntime){
		$this->respawntime = $respawntime;
	}
	
	public function getMaxhealth(){
		return $this->maxhealth;
	}

	public function setMaxhealth($maxhealth){
		$this->maxhealth = $maxhealth;
	}
	
	public function getRate(){
		return $this->rate;
	}

	public function setRate($rate){
		$this->rate = $rate;
	}
	
	public function getDeathdate(){
		return $this->deathdate;
	}

	public function setDeathdate($deathdate){
		$this->deathdate = $deathdate;
	}
	
	public function getMaxnumber(){
		return $this->maxnumber;
	}
	
	public function setMaxnumber($maxnumber) {
		$this->maxnumber = $maxnumber;
	}
	
	public function setHeadEquipment($headequipment) { $this->headequipment = $headequipment; }
	public function getHeadEquipment() { return $this->headequipment; }
	public function setBodyEquipment($bodyequipment) { $this->bodyequipment = $bodyequipment; }
	public function getBodyEquipment() { return $this->bodyequipment; }
	public function setLegsEquipment($legsequipment) { $this->legsequipment = $legsequipment; }
	public function getLegsEquipment() { return $this->legsequipment; }
	public function setFeetEquipment($feetequipment) { $this->feetequipment = $feetequipment; }
	public function getFeetEquipment() { return $this->feetequipment; }
	public function setRighthandEquipment($righthandequipment) { $this->righthandequipment = $righthandequipment; }
	public function getRighthandEquipment() { return $this->righthandequipment; }
	public function setLefthandEquipment($lefthandequipment) { $this->lefthandequipment = $lefthandequipment; }
	public function getLefthandEquipment() { return $this->lefthandequipment; }
	public function setSilvercoins($silvercoins) { $this->silvercoins = $silvercoins; }
	public function getSilvercoins() { return $this->silvercoins; }
	public function setIshuman($ishuman) { $this->ishuman = $ishuman; }
	public function getIshuman() { return $this->ishuman; }

	
	/**
	* Crea tooltip html
	* @param obj $npc Character_NPC_Model
	* @return string $html Html
	*/
	
	public function helper_tooltip( $npc )
	{
		$html = '';
		$char = Character_Model::get_info( Session::instance()->get('char_id') ); 
		$tooltiptext = "ID: {$npc->id}<br/>";
		$tooltiptext .= kohana::lang('global.name') . ": " . Character_Model::create_publicprofilelink($npc -> id, $npc->name) . "<br/>";
		$tooltiptext .= html::anchor(
			"character/attackchar/{$char->id}/{$npc->id}",
			kohana::lang('charactions.attack') ); 
		$html .= "<span style='cursor:pointer' class='npc' data-tooltipcontent='{$tooltiptext}'>" . kohana::lang('npc.' . $npc -> npctag . '_name' ) . "</span>";
		
		return $html;
		
		
	}
	
	/**
	* Esegue azioni quando l' NPC muore
	* @param none
	* @return none
	*/
	
	public function die_aftermath()
	{
		kohana::log('debug', '-> Die Aftermath not overriden.');		
	}
	
	public function death()
	{	
			$this -> setStatus('dead');
			$this -> setDeathdate(time());
			$this -> save();
	}
	
}
?>
	