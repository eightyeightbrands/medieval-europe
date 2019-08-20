<?php defined('SYSPATH') OR die('No direct access allowed.');

class PremiumBonus_Automatedsleep_Model extends PremiumBonus_Model
{
			
	function __construct()
    {
        $this -> name = 'automatedsleep';
	}
	
	function get_tutorial_html()
	{
		
		$html = 
		"<div class='center'>" . 
			html::anchor('https://wiki.medieval-europe.eu/index.php?title=Automated_Rest_Bonus', kohana::lang('global.tutorial'), 	array('target' => 'new')) . 
		"</div>";
		
		return $html;
	}
}