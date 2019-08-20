<?php defined('SYSPATH') OR die('No direct access allowed.');

class Course_Model
{
	const NEEDED_HOURS_X_LEVEL = 75;
	protected $tag;
	protected $coursetype = '';
	protected $name;
	protected $description;
	
	function __construct( $tag ) 
	{
		$this -> setTag( $tag );		
	}
	
	
	/**
	* Helper che costruisce l' HTML per le views.
	* @param obj $char Character_Model Personaggio che studia
	* @param obj $structure Structure_Model Struttura dove si studia
	* @return str $html HTML
	*/
	
	public function helper_show( $char, $structure )
	{
		kohana::log('debug', $this -> getCourseType());
		
		if ($this -> getCoursetype() == 'attribute' )			
		{
			if ( $this -> getLevel( $char ) == 21 )
			{
				$html = '<fieldset>';
				$html .= '<legend>' . $this -> getName() . '</legend>' ;		
				$html .=  "<p class='center'><i>" . $this -> getDescription() . '</i></p>' ;
				$html .= 	"<p class='center'>" . kohana::lang('structures.coursecompleted') . "</p>";
				$html .= '</fieldset>';
				return $html;
			}
		}
		
		if ( $this -> getCoursetype() == 'skill' )
		{
			
			if ( Skill_Model::character_has_skill( $char -> id, $this->getLinkedskill()) )
			{
				$html = '<fieldset>';
				$html .= '<legend>' . $this -> getName() . '</legend>' ;		
				$html .=  "<p class='center'><i>" . $this -> getDescription() . '</i></p>' ;
				$html .= 	"<p class='center'>" . kohana::lang('structures.coursecompleted') . "</p>";
				$html .= '</fieldset>';
				return $html;
			}
		}
		
		
		$html = '<fieldset>';
		$html .= '<legend>' . $this -> getName() . '</legend>' ;		
		$html .=  "<p class='center'><i>" . $this -> getDescription() . '</i></p>' ;
		$html .=  "<p class='center'>";
			
		$html.= kohana::lang(
			'structures.course_info', 
			$this -> getLevel( $char ), 
			$this -> getStudiedHours( $char ), 
			$this -> getNeededHours( $char ),
			$this -> getStudiedHours( $char )/($this -> getNeededHours( $char ) )*100)		
			. '</p>';
		
		$html .=  "<div class='center'>";
		$html .=  kohana::lang('structures.availablelessonshours', 
			$this -> getAvailableHours( $structure ));

		$html .= "<br/>";

		$html .=  form::open();
		$html .=  form::hidden('course', $this -> getTag());
		$html .=  form::hidden('structure_id', $structure -> id);
		
		$price = $this -> getPricePerHour( $char, $structure );
		
		$html .=  kohana::lang('structures.studyortrain', $price['pricewithtax'] );
		$html .= '&nbsp;';
		$html .=  form::input( 
			array(
				'name' => 'hours',
				'value' => 1,
				'class' => 'input-xxsmall right')); 
		$html .= "<br/>";		
		$html .=  form::submit( array (
		'id' => 'submit', 
		'class' => 'button button-medium', 			
		'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')' ), kohana::lang('global.studyortrain')) ;		
		$html .=  form::close();
		$html .= "</div>";
		$html .=  '<br/>';
		$html .= '</fieldset>';
		
		
		return $html;
	}
	
	/*
	* Aggiungi ore al personaggio
	* @param obj $char Character_Model
	* @param int $hours Ore
	* @return none
	*/
	public function addStudyHours( $char, $hours )
	{
		Character_Model::modify_stat_d(
			$char -> id,
			'studiedhours', 
			$hours,
			$this -> getTag(),
			null
		);
		
	}
	
	/*
	* Torna le ore rimanenti del corso
	* @param obj $char Character_Model	
	* @return int $hours Ore rimanenti
	*/
	public function getLeftHours( $char )
	{
		return ( $this -> getNeededHours( $char ) - $this -> getStudiedHours( $char ) );		
	}
	
	/*
	* Get Studied Hours for this course 
	* @param obj $char Character_Model
	* @return int $studiedhours Ore studiate
	*/
	
	public function getStudiedHours( $char )
	{
		$stat = Character_Model::get_stat_from_cache( $char -> id, 'studiedhours', $this->getTag() );		
		if ( $stat -> loaded )
			return $stat -> value;
		else
			return 0;		
	}	
	
	/**
	* Torna le ore necessarie per completare il livello di un corso	
	* @param obj $char Oggetto Chat	
	* @return int $n numero di ore per finire il corso
	*/

	public function getNeededHours( $char ) 
	{		 
		
		kohana::log('debug', '--- COURSE NEEDED HOURS START ----');
		kohana::log('debug', "-> course: {$this->getTag()}");
		
		$neededhours = 100000000;
		
		if ( $this -> getCoursetype() == 'attribute' )
		{
			kohana::log('debug', '-> Char will follow the Course at level: ' . ($this -> getLevel($char)));
			
			$neededhours = 
				self::NEEDED_HOURS_X_LEVEL 
				+ 3 * intval ( pow ( 1.1 , ( $this->getLevel($char)-1 ) ) ) 
				+ $this->getLevel($char) * 3 ;	
				
		}
		
		if ( $this -> getCoursetype() == 'skill' )
		{			
	
			$skills = Skill_Model::get_character_skillcount( $char -> id );			
			kohana::log('debug', "-> Char has skills: {$skills}");	
			if ($skills == 0 )
				$neededhours = 270;
			else
				$neededhours = round( 270 + 30 * pow( $skills , 3),0 );		
		}
		
		kohana::log('debug', "-> Needed hours: {$neededhours}");
		kohana::log('debug', '--- COURSE NEEDED HOURS END ----');
		return $neededhours;	
	}
	
	/**
	* Torna il prezzo per ora di studio 
	* @param obj $char Character_Model Personaggio che studia
	* @param obj $structure Structure_Model Struttura dove si studia
	* @return array $priceperhour
	*    price: prezzo base
	*    pricewithtax: prezzo con tassa;	
	*/
	
	public function getPricePerHour( $char, $structure )
	{
		
		$priceperhour = array(
			'price' => 0,
			'pricewithtax' => 0 );
		
		$valueaddedtax = Region_Model::get_appliable_tax( $structure -> region, 'valueaddedtax', $char );
		$priceperhourstat = Structure_Model::get_stat_d( $structure -> id, 'courseshourlycost');
		
		if ($priceperhourstat -> loaded == false )
			$price = 3;
		else
			$price = $priceperhourstat -> spare1;
		
		$priceperhour['price'] = $price;
		$priceperhour['pricewithtax'] = $price * ( 100 + $valueaddedtax ) / 100;
		
		return $priceperhour;
	}
	/*
	* Torna le ore disponibili da consumare per una struttura
	* @param obj $structure struttura dove si studia
	* @return int $availablehours Ore disponibili di lezione
	*/
	
	function getAvailableHours( $structure )
	{
		if ($structure -> structure_type -> supertype == 'academy' )
			$availablehours = $structure -> get_item_quantity( 'paper_piece' );
		else
		{
			$sql = "
			SELECT SUM(quantity*quality) totalquality
			FROM items i, cfgitems ci 
			WHERE i.cfgitem_id = ci.id
			AND ci.tag = 'wood_dummy' 
			AND structure_id = {$structure->id}";
			
			$rset = Database::instance() -> query( $sql ) -> as_array();
			
			$availablehours = round( $rset[0] -> totalquality / 4 );		
			
		}						
		
		return $availablehours;		
	}
	
	public function completeCourse() {
		die('Method completeCourse not ovverriden!');
	}
		
	function getName() { 
		return kohana::lang('structures.course_' . $this -> getTag() . '_name'  );
	}
	
	function getDescription() { 
		return kohana::lang('structures.course_' . $this -> getTag() . '_description' );
	}
	
	function setTag($tag) { $this->tag = $tag; }
	function getTag() { return $this->tag; }
		
	function getCoursetype() { return $this->coursetype; }	
	function getLinkedskill() { return $this->linkedskill; }
	
}
