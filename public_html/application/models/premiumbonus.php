<?php defined('SYSPATH') OR die('No direct access allowed.');

class PremiumBonus_Model
{
	var $name = '';
	var $canbeboughtonce = false;
	var $canbegifted = true;

	function __construct()
    {
        $this -> name = 'notoverriden';
		$this -> info = array();
    }

	function get_info()
	{
		$allbonuses = Configuration_Model::get_premiumbonuses_cfg();
		$cfg = $allbonuses[$this -> get_name()];

		foreach ($cfg['cuts'] as $cut => &$data)
			$data['discountedprice'] = round($data['price'] * (100-$cfg['discount']) / 100, 0);

		return $cfg;
	}

	function get_name()
	{
		return $this -> name;
	}

	function get_canbegifted()
	{
		return $this -> canbegifted;
	}

	function addextrafields()
	{
		return '';
	}

	function get_tutorial_html()
	{
		return '';
	}

	/**
	* Renders a bonus form
	* @param string name Bonus Name
	* @return string html to display
	*/

	function displaybonus()
	{

		$info = $this -> get_info();

		$pricelist_html = "<table>";
		foreach ( (array) $info['cuts'] as $cut => $data)
		{
			$dropdown[$cut] = kohana::lang('bonus.unit_' . $info['cutunit']) . ': ' . $data['cut'];
			$pricelist_html .= "<tr><td>" . $data['cut'] . ' ' . kohana::lang('bonus.unit_' . $info['cutunit']) .
				': ' . $data['price'] . ' ' . kohana::lang('global.doubloons') .
				"</td></tr>";
		}
		$pricelist_html .= "</table>";

		$html = "
		<div class='bonustitle center'>" . kohana::lang("bonus.{$this -> get_name()}_name") . "</div>

			<div id='bonuswrapper'>

				<div class='bonusimage'>" .
					html::image("media/images/template/bonuses/{$this -> get_name()}.png")."
					<br/>" .
					"<div class='center' title='{$pricelist_html}'>" .
						html::anchor('#', kohana::lang('bonus.pricelist'),
							array(
								'style' => 'pointer-events:none;cursor:default'
							)
						) . "</div>".
					$this -> get_tutorial_html() .
				"</div>" .

				"<div class='bonusdetails'>" .
					"<div class='bonusdescription'>" . kohana::lang("bonus.{$this -> get_name()}_description") .
					"</div>
				" ;

					$html .= form::open('bonus/buy');
					// price section

					$html .=
					"<div style='border:0px solid green;float:left;margin-top:5px;'>" .
						form::dropdown(
							array(
								'id' => $this -> get_name(),
								'name' => 'cut',
								'class' => 'premiumbonuscuts',
							),
							 $dropdown
						) .
						"&nbsp;" .
						kohana::lang('global.price') . ":" .
						"&nbsp;<span id='price-{$this -> get_name()}'></span>" .
						"&nbsp;<span style='font-weight:bold;color:#c00' id='discountedprice-{$this -> get_name()}'></span>" ."&nbsp;" . kohana::lang('global.doubloons') .
					"</div>" ;

					$html .= "<br style='clear:both'/>";

					$html .=
					"<div class='bonuscommands'>" .
						form::hidden('name', $this -> get_name());

						if ($this -> get_canbegifted() )
						{
							$html .= kohana::lang('bonus.searchgifttargetchar') . ":&nbsp;" .
							form::input(
								array(
									'name' => 'targetchar',
									'placeholder' => kohana::lang('bonus.targetcharplaceholder'),
									'class' => 'targetchar input input-large')) . "&nbsp;" ;
						}

						$html .= $this -> addextrafields();

					$html .=
					"<div class='center'>".
					form::button(
					array(
						'name' => 'buy',
						'value' => kohana::lang('bonus.buyorgift'),
						'class' => 'button button-red',
						'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')',
						'style' => 'width:100%')).
					"</div>";

					$html .= form::close();
					$html .= "</div>";// end bonuscommands
				$html .= "</div>";// end bonusdetails
				$html .= "<br style='clear:both'/>";
				// end wrapper
				$html .= "</div>";

		return $html;

	}

	/**
	* Make checks
	* @param obj $targetchar Character that receives the bonus
	* @param obj $targetstructure Structure that receives the bonus
	* @param string $cut Chosen Cut
	* @param array $par array of parameters
	* @param string $message feedback message
	* @param boolean $free (is bonus free of charge)
	* @returns boolean
	*/

	protected function checks( $targetchar, $targetstructure, $cut, $par = null, &$message, $free)
	{

		$info = $this -> get_info();
		$currentchar = Character_Model::get_info( Session::instance()->get('char_id') );

		if ( !$targetchar -> loaded )
		{
			$message = 'global.error-characterunknown';
			return false;
		}

		if (
			$free == false
			and
			$currentchar -> get_item_quantity( 'doubloon' ) < $info['cuts'][$cut]['discountedprice'] )
		{
			$message = kohana::lang('bonus.error-notenoughdoubloons');
			return false;
		}

		return true;
	}

	/**
	* Add Bonus to player
	* @param obj $targetchar Character that receives the bonus
	* @param int $cut Chosen cut (days or units)
	* @param array $par array of parameters
	* @param string $message feedback message
	* @param boolean $free (is bonus free of charge)
	* @returns boolean
	*/

	function add( $targetchar, $targetstructure, $cut, $par = null, &$message, $free = false )
	{

		$message = 'bonus.info-bonusbought';

		//var_dump($par);exit;
		// check data

		if ( $this -> checks( $targetchar, $targetstructure, $cut, $par, $message, $free ) == false )
		{
			return false;
		}

		$info = $this -> get_info();
		$currentbonus = false;

		if ($this -> get_name() == 'armory')
		{
			$currentbonuses = Character_Model::get_premiumbonuses($targetchar -> id);
			if(!empty($currentbonuses['armory'])) {

				foreach ( $currentbonuses['armory'] as $armorybonus )
					if ( $armorybonus['structure_id'] == $targetstructure -> id )
						$currentbonus = $armorybonus;
				//var_dump($currentbonus); exit;

			}

		}
		else
			$currentbonus = Character_Model::get_premiumbonus($targetchar -> id, $this -> get_name());

		$currentchar = Character_Model::get_info( Session::instance()->get('char_id') );
		$isatelierlicense = strpos ($this->get_name(), "atelier-license");

		//var_dump($x);
		//var_dump($this->get_name());exit;

		// estendi (eccetto licenze atelier)

		// if bonus is existing


		if ( $currentbonus !== false and $isatelierlicense === false)
		{

			if ($this -> canbeboughtonce)
			{
				$message = 'bonus.error-bonuscanbeboughtonlyonce';
				return false;
			}
			$this -> extend( $targetchar, $targetstructure, $currentbonus, $cut, $par, $free);

		}
		else
		{
			// add the bonus for the player.
			kohana::log('debug', "------- ADD BONUS -------");
			kohana::log('debug', "-> Adding bonus {$this -> get_name()}, cut: {$cut} to: {$targetchar -> name}");

			$cb = new Character_PremiumBonus_Model();
			$cb -> user_id = $currentchar -> user_id;
			$cb -> targetuser_id = $targetchar -> user_id;
			if ($targetchar -> id != $currentchar -> id )
				$cb -> targetcharname = $targetchar -> name;
			$cb -> character_id = $currentchar -> id;
			if ( !is_null($targetstructure) )
				$cb -> structure_id = $targetstructure -> id;
			$cb -> cfgpremiumbonus_id = $info['id'];
			$cb -> cfgpremiumbonus_cut_id = $info['cuts'][$cut]['id'];
			if (!empty($par))
				$cb -> param1 = $par[4];
			$cb -> starttime = time();

			if ($info['cutunit'] == 'quantity' )
			{
				$cb -> endtime = $cb -> starttime;
				if ( $free == false )
					$cb -> doubloons = $info['cuts'][$cut]['discountedprice'] * $info['cuts'][$cut]['cut'];
				else
					$cb -> doubloons = 0;
			}
			else
			{
				$cb -> endtime = time() + $info['cuts'][$cut]['cut'] * (24 * 3600);
				if ( $free == false )
					$cb -> doubloons = $info['cuts'][$cut]['discountedprice'];
				else
					$cb -> doubloons = 0;
			}

			$cb -> save();

		}

		// save bonus cache

		My_Cache_Model::delete('-charinfo_' . $targetchar -> id . '_premiumbonuses') ;

		// specific actions

		if ($this -> postsaveactions($targetchar, $cut, $par, $message) == false )
			return false;

		if ( $free == false )
		{
			$currentchar -> modify_doubloons( - $info['cuts'][$cut]['discountedprice'], $this -> get_name() );
			$currentchar -> save();
		}

		return true;

	}

	/**
	* Extends Bonus to player
	* @param obj $char Character that receives the bonus
	* @param obj $bonus Character_PremiumBonuses_Model
	* @param string $cut Chosen Cut
	* @param array $par array of parameters
	* @returns boolean
	*/

	function extend( $char, $structure, $bonus, $cut, $par = null, $free )
	{

		kohana::log('debug', "-> Extending bonus {$this -> get_name()}, cut: {$cut}");

		$info = $this -> get_info();

		$b = ORM::factory('character_premiumbonus', $bonus['id']);

		$b -> endtime += $info['cuts'][$cut]['cut'] * (24*3600);
		if ($free == false)
			$b -> doubloons += $info['cuts'][$cut]['discountedprice'];
		$b -> save();

		return true;
	}

	function postsaveactions( $targetchar, $cut, $par, &$message )
	{

		$info = $this -> get_info();
		//var_dump($info);exit;
		$sourcechar = Character_Model::get_info( Session::instance()->get('char_id') );

		// send event for normal purchase
		if ($sourcechar -> id == $targetchar -> id)
			Character_Event_Model::addrecord(
				$targetchar -> id,
				'normal',
				'__events.boughtbonus' .
				';__bonus.' . $info['name'] . '_name' .
				';' . $info['cuts'][$cut]['cut'] .
				';__bonus.unit_' . $info['cutunit'],
				'evidence');
		// send events for gift
		else
		{
			Character_Event_Model::addrecord(
				$sourcechar -> id,
				'normal',
				'__events.giftedbonus' .
				';__bonus.' . $info['name'] . '_name' .
				';' . $info['cuts'][$cut]['cut'] .
				';__bonus.unit_' . $info['cutunit'] .
				';' . $targetchar -> name,
				'evidence'
				);

			Character_Event_Model::addrecord(
				$targetchar -> id,
				'normal',
				'__events.receivedbonus' .
				';' . $sourcechar -> name .
				';__bonus.' . $info['name'] . '_name' .
				';' . $info['cuts'][$cut]['cut'] .
				';__bonus.unit_' . $info['cutunit'],
				'evidence'
				);
		}

		return true;

	}


	/**
	* Setta il menu orizzontale a seconda della struttura.
	* @param string selected voce da selezionare
	* @return array menu html;
	*/

	public function get_horizontalmenu( $selected )
	{

		$lnkmenu = array(
			'/bonus/superrewardsofferwall/' =>
					array( 'name' => 	'Super Rewards',	'htmlparams' => array( 'class' =>
					( $selected == 'superrewardsofferwall' ) ? 'selected' : '' )),
			'/bonus/personaofferwall/' =>
					array( 'name' => 	'Persona.ly',	'htmlparams' => array( 'class' =>
					( $selected == 'personaofferwall' ) ? 'selected' : '' )),
			'/bonus/adscendmediaofferwall/' =>
					array( 'name' => 	'Adscendmedia',	'htmlparams' => array( 'class' =>
					( $selected == 'adscendmediaofferwall' ) ? 'selected' : '' )),
			'/bonus/matomyofferwall/' =>
					array( 'name' => 	'Matomy',	'htmlparams' => array( 'class' =>
					( $selected == 'matomyofferwall' ) ? 'selected' : '' )),
			'/bonus/paymentwallofferwall/' =>
					array( 'name' => 	'Payment Wall',	'htmlparams' => array( 'class' =>
					( $selected == 'paymentwallofferwall' ) ? 'selected' : '' )),
			'/bonus/index/' =>
					array( 'name' => 	'Premium Purchases',	'htmlparams' => array( 'class' =>
					( $selected == 'premiumpackages' ) ? 'selected' : '' )),
		);

		return $lnkmenu;

	}

}
