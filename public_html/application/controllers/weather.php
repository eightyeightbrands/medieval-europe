<?php defined('SYSPATH') OR die('No direct access allowed.');
class Weather_Controller extends Template_Controller
{

	public $template = 'template/gamelayout';

	public function __construct()
	{
		parent::__construct();
	}

	public function index()
	{

    $this -> auto_render = false;

		if(empty($_REQUEST['region'])) {
      echo json_encode(array('status' => false, 'msg' => 'missing region'));
      exit;
    }


    $output = My_Cache_Model::get('weather' . md5($_REQUEST['region']));
    if(empty($output)) {
      $ch = curl_init("http://api.openweathermap.org/data/2.5/weather?q=" . urlencode($_REQUEST['region']) . "&appid=9eb73f43a15087716c7fc95cf2d70e84&units=metric");
      curl_setopt($ch, CURLOPT_HEADER, 0);
      curl_setopt($ch, CURLOPT_POST, 1);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      $output = curl_exec($ch);
      curl_close($ch);
      $cached = false;
    } else {
      $cached = true;
    }

    if(empty($output)) {
      echo json_encode(array('status' => false, 'msg' => 'missing output from api'));
      exit;
    }

    $weather_data = json_decode($output);
    if(empty($weather_data->weather)) {
      echo json_encode(array('status' => false, 'msg' => 'invalid output from api'));
      exit;
    }

    My_Cache_Model::set('weather' . md5($_REQUEST['region']), $output);

    echo json_encode(array('status' => true, 'msg' => $weather_data->weather[0]->description . ', ' . str_replace(",", ".", $weather_data->main->temp) . '&#176; <!-- ' . (($cached) ? '' : 'not') . ' cached -- >'));

	}


}
