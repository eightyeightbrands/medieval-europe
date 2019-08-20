<?php defined('SYSPATH') OR die('No direct access allowed.');

class Language_Controller extends Controller
{
	public function change_language( $lang = 'en_US' )
	{
		User_Model::change_language( $lang );
		url::redirect(request::referrer());
	}
	
}