<?php defined('SYSPATH') OR die('No direct access allowed.');

class Skill_Model
{
	protected $tag;
	protected $decreasefactor;
	protected $increasefactor;	
	protected $type;
	
	/*
	* Add a skill to a character
	* @param obj $char Character to add the skill to
	* @return none
	*/
	
	function add( $character )
	{
		
		// se il char ha già troppi, non aggiungere skill.
		
		if ( Skill_Model::get_character_skillcount( $character -> id ) >= 3 )
		{
			Character_Event_Model::addrecord( 
			$character -> id,
			'normal',
			'__events.skilladdfailtoomanyskills' .
			';__character.skill_' . $this -> getTag() . '_name',
			'evidence'
			);		
		}
		else
		{
			Character_Model::modify_stat_d(
				$character -> id,
				'skill',
				10,
				$this -> getTag(),
				null,
				false,
				0
			);
			
			Character_Event_Model::addrecord( 
				$character -> id,
				'normal',
				'__events.skilladded' .
				';__character.skill_' . $this -> getTag() . '_name',
				'evidence'
				);		
		}
	}
	
	/*
	* Mostra un div con le informazioni dello skill
	* @param int $character_id ID Personaggio
	* @return str $html
	*/
	
	function helper_view( $character_id )
	{
		$html = "
		<table>
			<tr>
			<td width='20%'>";
		$html .= html::image('media/images/skills/' . $this -> getTag(). '.png');
		$html .= "</td>
		<td valign='top'>
		<h5>" . $this -> getName() . "</h5>";
		$html .= kohana::lang('global.proficiency');
		$html .= ": <span class='value'>" 
			. $this -> getProficiency( $character_id ) 
			. "/100 (" 
			. ($this -> getProficiency($character_id)) . "%)</span><br/>";
		$html .= "<p>" . $this -> getDescription() . "</p>";
		$html .= "<div class='right'>" . 
			html::anchor('character/removeskill/' . $this -> getTag(), 
			'Remove',
			array(
				'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')' 
			)) . "</div>";
		$html .= "</td>
		</tr>
		</table>";
		
		return $html;
	}
			
	function setTag($tag) { $this->tag = $tag; }
	function getTag() { return $this->tag; }	
	function getName(){return kohana::lang('character.skill_' . $this -> getTag() . '_name');}	
	function getDescription(){return kohana::lang('character.skill_' . $this -> getTag() . '_description');}
	function setIncreasefactor($increasefactor) { $this->increasefactor = $increasefactor; }
	function getIncreasefactor() { return $this->increasefactor; }
	function setDecreasefactor($decreasefactor) { $this->decreasefactor = $decreasefactor; }
	function getDecreasefactor() { return $this->decreasefactor; }

	/*
	* Torna la proficiency dello skill 
	* per il personaggio
	* @param int $character_id 
	* @return int $proficiency Proficienza nello skill
	*/
	
	function getProficiency( $character_id )
	{
		$skillstat = Character_Model::get_stat_d(
			$character_id,
			'skill',
			$this -> getTag());
		
		if ($skillstat -> loaded )
			return Utility_Model::number_format($skillstat -> value,2);
		else
			return 0;
	}

	/*
	* Rimuove uno skill
	* @param obj $char Character_Model
	* @return boolean
	*/	
	
	function remove( $char )
	{
		
		$skillstat = Character_Model::get_stat_d(
			$char -> id,
			'skill',
			$this->getTag() );
		if ($skillstat -> loaded )
		{
			
			Character_Event_Model::addrecord( 
			$char -> id,
			'normal',
			'__events.skillremoved' .
			';__character.skill_' . $this -> getTag(),
			'evidence'
			);	
			$skillstat -> delete();			
			return true;
		}
		else
			return false;
		
	}
	
	/*
	* Riduce proficiency
	* @param int $character_id ID Personaggio		
	* @return none
	*/
	
	function decreaseproficiency( $character_id )
	{
		
		// in meditazione gli skill non decadono
		
		if ( Character_Model::is_meditating( $character_id  ) )
			return;
		
		$oldproficiency = $this -> getProficiency($character_id);
		$newproficiency = $oldproficiency - $this -> getDecreasefactor();
		
		if ( $newproficiency < 0 )
			$newproficiency = 0;
		
		kohana::log('info', "-> Char: {$character_id}: Old proficiency: {$oldproficiency}. Decreasing proficiency. New proficiency: {$newproficiency}");
		
		
		Character_Model:: modify_stat_d( 
			$character_id, 
			'skill',
			$newproficiency,
			$this -> getTag(),			
			null,
			true
		);		
		
	}
	
	
	/*
	* Ritorna se un personaggio ha uno skill o meno
	* @param int $character_id ID Personaggio
	* @param str $tag Tag
	* @return boolean
	*/
	
	function character_has_skill( $character_id, $tag )
	{
				
		$stat =  
		Character_Model::get_stat_d( 
			$character_id,
			'skill',
			$tag,
			null,
			null );
		
		if ( $stat -> loaded )
			return true;
		else
			return false;
		
	}
	
			
	/*
	* Conta gli skill di un char
	* @param int $character_id ID Personaggio
	* @return int numero skills
	*/
	
	function get_character_skillcount( $character_id )
	{
		
		$skills = Character_Model::get_stats_d( $character_id, 'skill' );
			
		if (is_null($skills))
			return 0;
		else
			return count($skills);		
		
	}
	
	
	/*
	* Incrementa proficiency
	* @param int $character_id ID Personaggio		
	* @param int $delta numero da aggiungere
	* @param boolean $replace se true, rimpiazza valore
	* @return none
	*/
	
	function increaseproficiency( $character_id, $delta = null, $replace = false )
	{
		
		$oldproficiency = $this -> getProficiency($character_id);
		
		if ( is_null($delta) )
			$delta = $this -> getIncreasefactor();
		
		kohana::log('info', "-> Old proficiency: {$oldproficiency}. Increasing proficiency for skill {$this -> getTag()} by {$delta}");
		
		if ( $replace == false )
			$newproficiency = $oldproficiency + $delta;
		else
			$newproficiency = $delta;
		
		if ( $newproficiency > 100 )
			$newproficiency = 100;
		
		kohana::log('info', "-> New proficiency: {$newproficiency}.");
		
		if ($newproficiency != $oldproficiency)
		{
			Character_Model:: modify_stat_d( 
				$character_id, 
				'skill',
				$newproficiency,
				$this -> getTag(),
				null,
				true				
			);
				
			
			Character_Event_Model::addrecord(
				$character_id, 
				'normal',
				'__events.skillincreasedproficiency' . 
				';__character.skill_' . $this -> getTag() . '_name' . 
				';' . $delta
			);				
		}
	}
	
}


