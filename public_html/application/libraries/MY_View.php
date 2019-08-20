<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Loads and displays Kohana view files. Can also handle output of some binary
 * files, such as image, Javascript, and CSS files.
 *
 * $Id: View.php 4072 2009-03-13 17:20:38Z jheathco $
 *
 * @package    Core
 * @author     Kohana Team
 * @copyright  (c) 2007-2008 Kohana Team
 * @license    https://kohanaphp.com/license.html
 */
class View extends View_Core {
	
	
	/**
	 * Attempts to load a view and pre-load view data.
	 *
	 * @throws  Kohana_Exception  if the requested view cannot be found
	 * @param   string  view name
	 * @param   array   pre-load data
	 * @param   string  type of file: html, css, js, etc.
	 * @return  void
	 */
	public function __construct($name = NULL, $data = NULL, $type = NULL)
	{		
		if (is_string($name) AND $name !== '')
		{
			
			// Set the filename
			
			$char_id = Session::instance()->get('char_id');			
			$skinstat = Character_Model::get_stat_d( $char_id, 'skin');
			if (!$skinstat -> loaded )
				$skin = 'classic';
			else
				$skin = $skinstat -> stat1;
			
			$_name = $name;
			$view = explode("/",$name);
			//var_dump($view);
			$view[count($view)-1] = $skin . '_' . $view[count($view)-1];
			//var_dump($view);
			
			$name = implode( "/", $view);
			$file = 'application/views/' . $name . '.php';
			//var_dump($file);
			if (!file_exists($file))
				$name = $_name;
			
			//kohana::log('debug', "-> View Factory: Current Skin: [{$skin}]");
			//kohana::log('debug', "-> View Factory: Page Requested: [{$name}]");
			
			$this->set_filename($name, $type);
		}

		if (is_array($data) AND ! empty($data))
		{
			// Preload data using array_merge, to allow user extensions
			$this->kohana_local_data = array_merge($this->kohana_local_data, $data);
		}
	}
}