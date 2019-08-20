<?php defined('SYSPATH') OR die('No direct access allowed.');

use PHPMailer\PHPMailer\PHPMailer;
//use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class Utility_Model
{

const SECURITYKEY = '';

/**
* Torna il tempo passato in minuti ecc.
* @param int $ptime tempo in formato EPOCH
* @return string tempo in minuti ecc.
*/

function time_elapsed_string($ptime)
{
	$etime = time() - $ptime;

	if ($etime < 1)
	{
			return '0 seconds';
	}

	$a = array( 365 * 24 * 60 * 60  =>  'year',
							 30 * 24 * 60 * 60  =>  'month',
										24 * 60 * 60  =>  'day',
												 60 * 60  =>  'hour',
															60  =>  'minute',
															 1  =>  'second'
							);
	$a_plural = array( 'year'   => 'years',
										 'month'  => 'months',
										 'day'    => 'days',
										 'hour'   => 'hours',
										 'minute' => 'minutes',
										 'second' => 'seconds'
							);

	foreach ($a as $secs => $str)
	{
			$d = $etime / $secs;
			if ($d >= 1)
			{
					$r = round($d);
					return $r . ' ' . ($r > 1 ? $a_plural[$str] : $str) . ' ago';
			}
	}
}

public static function get_mathcaptcha()
{
	$data = array(
		'math' => '',
		'answer' => '');

	$digit1 = mt_rand(1,20);
	$digit2 = mt_rand(1,20);

	if( mt_rand(0,1) === 1 ) {
		$data['math'] = "$digit1 + $digit2";
		$data['answer'] = $digit1 + $digit2;
	} else {
		$data['math'] = "$digit1 - $digit2";
		$data['answer'] = $digit1 - $digit2;
	}

	return $data;
}

public static function getitemaverageprices( $securitykey, $period )
{

	if ( $securitykey != self::SECURITYKEY )
	{
		kohana::log('error', "-> securitykey $securitykey is different from " . self::SECURITYKEY . ", exiting." );
		return;
	}

	$data = array();

	$sql = "select
		c.name, ts.quantity, ts.totalprice/ts.quantity averageprice
		from trace_sales ts, cfgitems c
		where ts.cfgitem_id = c.id
		and timestamp = '$period'
		group by  c.name";

	$rset = Database::instance() -> query( $sql );
	print "Item;Sold Items;Average Price\r\n";
	foreach ( $rset as $r )
		print kohana::lang($r -> name) . ";" . $r -> quantity . ";" . $r -> averageprice . "\r\n";

}

function initquest( $securitykey, $char_id, $questname )
{

	$this -> auto_render = false;

	kohana::log('debug', "-> Init Quest {$questname}");

	if ( $securitykey != self::SECURITYKEY )
	{
		kohana::log('error', "-> securitykey $securitykey is different from " . self::SECURITYKEY . ", exiting." );
		die();
	}

	$character = ORM::factory('character', $char_id );
	$quest = QuestFactory_Model::createQuest($questname);
	$quest -> activate( $character );

}

/**
* Toglie 700 anni dalla data corrente
* @param int $timestamp: time nel formato UNIX
* @return str $date Data
*/

static public function format_date( $timestamp, $format = 'd-M' )
{

	$date = date ( $format, $timestamp );

	if ( $format != 'Y' )
		$date .= "-" . (date ("Y", $timestamp ) - 700) ;
	else
		$date = date ("Y", $timestamp ) - 700;
	return $date;
}

static public function format_datetime( $timestamp, $format = 'd-M' )
{

	$date = date ( $format, $timestamp );
	$date .= "-" . (date ("Y", $timestamp ) - 700) ;
	$date .= ", ".date("H:i:s", $timestamp);

	return $date;
}


/**
* Funzione che torna ore, minuti rispetto al timestamp passato
* @param: int $targetTime tempo target in secondi
* @return: string $text stringa nel formato d h m s
*/

static public function countdown( $targetTime )
{

	$actualDate = time();

	$secondsDiff = $targetTime - $actualDate;

	if ( $secondsDiff <= 0 )
		return '0 s';

	$remainingDay     = floor($secondsDiff/60/60/24);
	$remainingHour    = floor(($secondsDiff-($remainingDay*60*60*24))/60/60);
	$remainingMinutes = floor(($secondsDiff-($remainingDay*60*60*24)-($remainingHour*60*60))/60);
	$remainingSeconds = floor(($secondsDiff-($remainingDay*60*60*24)-($remainingHour*60*60))-($remainingMinutes*60));

	$text = '';
	if ( $remainingDay > 0 )
		$text .= $remainingDay . "d " ;
	if ( $remainingHour >= 0 )
		$text .= $remainingHour . "h " ;
	if ( $remainingMinutes >= 0 )
		$text .= $remainingMinutes . "m " ;
	$text .= $remainingSeconds . "s";

	return $text;
}

/**
* Convert seconds in hour, minutes, and seconds countdown
* @param int $secs secondi
* @return array
*/

function secs2hms( $secs )
{

	if ( $secs < 0 )
		return false;

	$m = (int)($secs / 60); $s = $secs % 60;

	$h = (int)($m / 60); $m = $m % 60;

	return array($h, $m, $s, $h . "h " . $m . "m " . $s . "s ");
}

function secondsToTime($inputSeconds) {
  $then = new DateTime(date('Y-m-d H:i:s', $inputSeconds));
  $now = new DateTime(date('Y-m-d H:i:s', time()));
  $diff = $then->diff($now);
  return array('years' => $diff->y, 'months' => $diff->m, 'days' => $diff->d, 'hours' => $diff->h, 'minutes' => $diff->i, 'seconds' => $diff->s);
}

/**
* Converte secondi in anni, giorni, ore, minuti e secondi
* @param int $secs secondi
* @param string $mode
* @return string (ydhms)
*/

static function secs2hmstostring( $secs, $mode = 'hours' )
{
	//kohana::log('debug', "-> Converting $secs, $mode");

	$data = self::s2ydhms($secs, $mode) ;
	$text = '';
	//kohana::log('debug', kohana::debug($data));
	if ($data['y'] > 0)
		$text .= $data['y'] . 'y ' ;

	if ($data['m'] > 0)
		$text .= ' ' . $data['m'] . 'm ' ;

	if ($data['d'] >= 0)
		$text .= ' ' . $data['d'] . 'd ' ;

	if ($mode == 'hours' )
	{
		if ($data['h'] >= 0)
			$text .= ' ' . $data['h'] . 'h ' ;
		if ($data['i'] >= 0)
			$text .= ' ' . $data['i'] . 'm ' ;
		if ($data['s'] >= 0)
			$text .= ' ' . $data['s'] . 's' ;
	}

	return $text;
}

/**
* ritorna le ore e minuti, da minuti
* @param min minuti
* @return Stringa con ore e minuti
*/

function m2h($min)
{
	$hours = sprintf("%dh %02dm", abs((int)($min/60)), abs((int)($min%60)));
	return $hours;

}


/**
* Convert seconds to days, hours, minutes and seconds human readable format in php
* @param string $s seconds
* @return array
*/

function s2ydhms($seconds)
{
   $obj = new DateTime();
   $obj -> setTimeStamp(time()+$seconds);
   return (array)$obj->diff(new DateTime());
}

/**
 * Sends emails
 * @param string $to
 * @param string $subject
 * @param string $body
 * @param string $attachment
 * @param boolean $unsubscribelink
 * @return boolean
*/

static function mail( $to, $subject, $body, $attachment = null, $unsubscribelink = false )
{

	if ( kohana::config('medeur.sendnotifications', false) == false )
	{
		kohana::log('debug', '-> Not sending email because notifications are disabled.');
		kohana::log('debug', '------- EMAIL BEGINS -------');
		kohana::log('debug', "subject: {$subject}");
		kohana::log('debug', $body );
		kohana::log('debug', '------- EMAIL ENDS -------');
		return true;
	}

	require_once(dirname(realpath(__FILE__)) . "/../libraries/vendors/PHPMailer/src/PHPMailer.php");
	require_once(dirname(realpath(__FILE__)) . "/../libraries/vendors/PHPMailer/src/SMTP.php");
	require_once(dirname(realpath(__FILE__)) . "/../libraries/vendors/PHPMailer/src/Exception.php");

	kohana::log("info", "Sending email to: " . $to);

	$rc = true;
	$mail = new PHPMailer(true);
	$mail->isSMTP();
	$mail->Host = '';
	$mail->SMTPAuth = true;
	$mail->Username = 'support@medieval-europe.eu';
	$mail->Password = '';
	$mail->SMTPDebug  = 0;
  //$mail->Debugoutput = 'error_log';
	//$mail->Port       = 465;
	$mail->CharSet = 'UTF-8';
	//$mail->SMTPSecure = 'ssl';
	$mail->From = 'support@medieval-europe.eu';
	$mail->FromName = 'Medieval-Europe Support';
	$mail->addAddress($to);
	$mail->addReplyTo('support@medieval-europe.eu', 'Medieval-Europe Support');
	$mail->isHTML(true);
	$mail->WordWrap = 50;

	// header

	$_body = html::image(
		'https://i.imgur.com/jxjeTbI.jpg',
		array( 'alt' => 'Medieval Europe' ));
	$_body .= "<hr/>";
	$_body .= "<br>";

	// content

	$_body .= $body;

	// attachment
	if ( !is_null( $attachment ) )
	{
		$mail -> addAttachment($attachment);
	}

	// footer
	$_body .= "<br/><br/>";
	$_body .= "Regards,<br/>";
	$_body .= "Medieval-Europe Staff.";

	if ( $unsubscribelink == true )
	{
		$user = ORM::factory("user") -> where ("email", $to) -> find();

		if ( $user -> loaded )
		{
			$_body .= '<br/>';
			$_body .= "<br/><span style='font-size:11px'>If you would like to stop receiving similar emails kindly unsubscribe from our mailing list by clicking this " .
				html::anchor( "https://medieval-europe.eu/user/unsubscribe/" . $user -> username . "/" . $user -> activationtoken, 'link.' );
			$_body .= "</span><br/><br/>";
		}

	}
	$_body .= "<hr/>";
	$_body .= html::anchor('https://medieval-europe.eu', 'Medieval Europe');

	$mail -> Subject = "[Server: " . kohana::config('medeur.servername') . ", Instance: " . kohana::config('medeur.environment') . "] " . $subject;
	$mail -> Body    = $_body;
	$mail -> AltBody = strip_tags( $_body );





	try
	{
		$rc = $mail -> send();
		kohana::log('debug', '-> return code: ' . $rc );
	} catch (Exception $e) {
		kohana::log('error', $e -> errorMessage() );
		return false;
	}


	return true;

}

/**
* trasforma una diff tra due date in anni, mesi, giorni
* @param int $d1: data1 in secondi
* @param int $d2: data2 in secondi
* @return string $testo da stampare
*/

function d2y( $d1, $d2 )
{

	$date1 = new DateTime(date('M d Y',$d1));
	$date2 = new DateTime(date('M d y',$d2));
	$diff = $date1 -> diff( $date2 );

	return
		$diff -> y . ' ' . kohana::lang('global.years') . ', ' .
		$diff -> m . ' ' . kohana::lang('global.months') . ', ' .
		$diff -> d . ' ' . kohana::lang('global.days') ;

}

/**
 * formatta numeri a seconda del locale
 * @param number numero da formattare
 * @return n numero formattato
*/

static function number_format( $number, $precision = 0)
{
	$n = number_format( $number, $precision, '.', ',' );
	return $n;
}

/**
 * Crea un banner dinamico promozionale
 * @param int $char_id ID Personaggio
 * @return mixed immagine
 *
*/

function create_banner( $char_id, $language = 'en_US' )
{
	// Force English Language for Banner

	Kohana::config_set('locale.language', 'en_US' );

	$char = ORM::factory ('character', $char_id );
	if ( ! $char -> loaded )
	{
		return;
	}

	$image = imagecreatetruecolor(300,124);
	$fontcolor_red = imagecolorallocate($image, 175, 0, 0);
	$fontcolor_blue = imagecolorallocate($image, 0, 0, 139);
	$fontcolor_black = imagecolorallocate($image, 0, 0, 0);



	if ( $char -> loaded)
	{
		$x = $y = 0;
		$fontfile_normal = 'media/fonts/calibri.ttf' ;
		$fontfile_bold = 'media/fonts/calibri_bold.ttf' ;
		$fontfile_small = 'media/fonts/visitor1.ttf' ;
		$fontfile_italic = 'media/fonts/arialbi.ttf' ;

		$avatar  = $_SERVER['DOCUMENT_ROOT'] . url::base() . 'media/images/characters/'.$char->id.'_l.jpg';
		if ( ! is_file($avatar))
		{
			$avatar  = $_SERVER['DOCUMENT_ROOT'] . url::base() .'media/images/characters/aspect/noimage_l.jpg';
		}

		$kingdom = $_SERVER['DOCUMENT_ROOT'] . url::base() .'media/images/heraldry/' . $char -> region -> kingdom -> get_image('small') ;

		//kohana::log('debug', 'kingdom: ' . $kingdom );

		$background = $_SERVER['DOCUMENT_ROOT'] . url::base() .'media/images/template/banner_background.gif';


		//$kingdom_img = imagecreatetruecolor( 35, 40 );
		$kingdomtmp_img = imagecreatefrompng($kingdom);
		list( $width, $height) = getimagesize( $kingdom );
		//imagecopyresampled($kingdom_img, $kingdomtmp_img, 0, 0, 0, 0, 35, 40, $width, $height);


		$avatar_img = imagecreatetruecolor(75,75);
		$avatartmp_img = imagecreatefromjpeg($avatar);
		list( $width, $height) = getimagesize( $avatar );
		imagecopyresampled($avatar_img, $avatartmp_img, 0, 0, 0, 0, 75, 75, $width, $height);
		//$avatar_img = imagecreatefromjpeg($avatar);


		// background

		$bg_img = imagecreatefromgif( $background );
		imagecopymerge($image, $bg_img, 0, 0, 0, 0, 300, 124, 100);

		// character name
		$y = 14;
		$bonus_title = Character_Model::get_basicpackagetitle( $char -> id );
    		if ($bonus_title != '')
			$bonus_title = kohana::lang($bonus_title);
		imagettftext($image, 9, 0, 3, $y, $fontcolor_red, $fontfile_bold, $bonus_title);
		$bbox = imagettfbbox(9, 0, $fontfile_bold, $bonus_title);
		$x += $bbox[2]+6;
		//kohana::log( 'debug', kohana::debug( $bbox ));
		imagettftext($image, 9, 0, $x, $y, $fontcolor_black, $fontfile_bold, substr($char->name,0,23));

		// avatar image

		imagecopymerge($image, $avatar_img, 3, 20, 0, 0, 75, 75, 100);
		Utility_Model::imagecopymerge_alpha($image, $kingdomtmp_img, 266, 4, 0, 0, 30, 36, 0);

		$y+=12;

		$kingdom_name = kohana::lang( $char->region->kingdom -> get_name() ) ;
		//kohana::log('debug', 'kingdom: ' . $kingdom_name );

		imagettftext($image, 8, 0, 81, $y, $fontcolor_black, $fontfile_bold, $kingdom_name );
		//imagettftext($image, 8, 0, 81, $y, $fontcolor_black, $fontfile_bold, kohana::lang('global.ciao') );
		//kohana::log('debug', kohana::debug( $r) );

		$y += 12;
		$role = $char->get_current_role();

		if ( $role and $role->tag != '' )
			$role_title = $role -> get_title( true );
		else
			$role_title = 'Citizen';


		imagettftext($image, 8, 0, 81, $y, $fontcolor_red, $fontfile_bold, $role_title);

		$y += 12;
		$age = Utility_Model::d2y( time(), $char -> birthdate );

		//$age = Utility_Model::s2ydhms( time() - $char -> birthdate );
		//kohana::log('debug', 'age: ' . kohana::debug( $age ));

		$born = 'Age: ' . $age ;
		/*
		if ( $age['vals']['y'] > 0 )
			$born .= $age['vals']['y'] . ' Years ';

		if ( $age['vals']['d'] >= 0 )
			$born .= $age['vals']['d'] . ' Days';
		*/
		imagettftext($image, 8, 0, 81, $y, $fontcolor_black, $fontfile_bold, $born);

		// titoli

		$bdx = 80;
		$y = 55;

		foreach ( $char -> character_titles as $title )
		{
			if ( $char -> user -> hidemaxstatsbadges == 'Y'
					and
				in_array( $title -> name, array( 'stat_str', 'stat_dex', 'stat_intel', 'stat_car', 'stat_cost')))
				;
			elseif ( $title -> current == 'Y' )
			{
				$badge = $_SERVER['DOCUMENT_ROOT'] . url::base() .
					'media/images/badges/character/badge_' . $title -> name .'_' . $title -> stars . '.png';
				$badge_img = imagecreatefrompng($badge);
				$newimage = ImageCreateTrueColor(20, 20);
				ImageAlphaBlending($newimage, false);
				ImageSaveAlpha($newimage, false);
				imagecopyresized($newimage, $badge_img, 0, 0, 0, 0, 20, 20, 40, 40);
				Utility_Model::imagecopymerge_alpha($image, $newimage, $bdx, $y, 0, 0, 20, 20, 0);$bdx += 20;
			}
		}

		// Slogan

		$bbox = imagettfbbox(7, 0, $fontfile_italic, $char -> slogan );
		$x = $bbox[0] + (imagesx($image) ) - ($bbox[4] ) - 4;
		$y = $bbox[1] + (imagesy($image) ) - ($bbox[5] ) - 34;

		imagettftext($image, 7, 0, $x, $y, $fontcolor_black, $fontfile_italic, $char->slogan );


		return $image;
	}
	else
		return null;
}

/**
* Copia e mergia con transparenza
*/

 function imagecopymerge_alpha($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $pct){
        $opacity=$pct;
        // getting the watermark width
        $w = imagesx($src_im);
        // getting the watermark height
        $h = imagesy($src_im);

        // creating a cut resource
        $cut = imagecreatetruecolor($src_w, $src_h);
        // copying that section of the background to the cut
        imagecopy($cut, $dst_im, 0, 0, $dst_x, $dst_y, $src_w, $src_h);
        // inverting the opacity
        $opacity = 100 - $opacity;

        // placing the watermark now
        imagecopy($cut, $src_im, 0, 0, $src_x, $src_y, $src_w, $src_h);
        imagecopymerge($dst_im, $cut, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $opacity);
    }

	/**
	* inserisce il postfisso per i numeri in inglese
	* @param number numero
	* @return  stringa 1st 2nd...
	*/

	function ordinalize($number)
	{
		if (in_array(($number % 100),range(11,13)))
		{
				 return $number.'th';
		}
		else
		{
			switch (($number % 10))
			{
				case 1:
					return 'st';
					break;
				case 2:
					return 'nd';
					break;
				case 3:
					return 'rd';
					break;
				default:
				 return 'th';
				 break;
			}
		}
	}

	/**
	* parsa un testo con i bbcode e genera XHTML valido.
	* @param testo da parsare
	* @return stringa
	*/

	public function bbcode( $text )
	{

		require_once( "application/libraries/vendors/nbbc-1.4.5/nbbc.php");
		$bbcode = new BBCode;
		// set smileys dir...
		$bbcode->SetSmileyUrl ( url::base( false )  . 'media/images/smileys' );
		return $bbcode -> Parse( $text );
	}

	function cutmap( $x, $y)
	{
		$bigmap=ImageCreateFromPnG("media/images/map/regionalmap.png");
		$image_out=ImageCreate(200,96);
		$factor=3.75;
		$posx=floor($x*$factor-100);
		$posy=floor($y*$factor-48);
		$copia=ImageCopyResized($image_out, $bigmap, 0, 0, $posx, $posy, 200, 96, 200, 96);
		ImageDestroy($bigmap);
		return ImagePng($image_out);
	}


		public function buildchart()
	{

		require_once( "application/libraries/vendors/FusionChartsFree/Code/PHP/Includes/FusionCharts.php");
		$character = Character_Model::get_info( Session::instance()->get('char_id') );
		$db = Database::instance();
		$commonoptions = " hoverCapBgColor='000000' bgColor='faf2bc' chartLeftMargin ='25' chartTopMargin ='1' chartBottomMargin ='1' chartRightMargin ='35'
		rotateNames='0'	animation='0' showAlternateHGridColor='1' AlternateHGridColor='FFE0B3' divLineColor='ff5904' divLineAlpha='20'  canvasBorderColor='666666' baseFontColor='000000' canvasBgColor='FFD18B' canvasBgAlpha='90' ";
		$chartwidth = 340;
		$chartheight = 240;
		$onemonthago = time() - ( 30 * 24 * 3600 );
		$oneyearago = time() - ( 365 * 24 * 3600 );

		///////////////////////////////////////
		// categories, weekly (last month)
		///////////////////////////////////////

		$categories_w = $db -> query( "select distinct period
			from stats_historical where kingdom_id =  "
			. $character -> region -> kingdom -> id . "
			and period > " . $onemonthago ."
			order by period asc" );

		$strCategories_w = "<categories fontSize='8' >";
		foreach ( $categories_w as $category )
		{
			$year = date("Y", $category -> period) - 700;
			$month = date("M", $category -> period );
			$month_s = date("m", $category -> period );
			$week   = date("W", $category -> period );
			$day   = date("d", $category -> period );
			//kohana::log('debug', 'week: ' . $week );
			$label = $day .", ". $month;
			$strCategories_w .= "<category name='" . $label . "' />";
		}

		$strCategories_w .= "</categories>";

		///////////////////////////////////////
		// categories, monthly
		///////////////////////////////////////

		$categories_m = $db -> query( "select distinct period
			from stats_historical where kingdom_id =  "
			. $character -> region -> kingdom -> id . "
			and period > " . $oneyearago ."
			order by period asc" );

		$strCategories_m = "<categories fontSize='8' >";
		foreach ( $categories_m as $category )
		{
			$year = date("Y", $category -> period) - 700;
			$month = date("M", $category -> period );
			$month_s = date("m", $category -> period );
			$week   = date("W", $category -> period );
			$label = $month . ", w" . $week;
			$strCategories_m .= "<category name='" . $label . "' />";
		}
		$strCategories_m .= "</categories>";


		/////////////////////////////////////////////
		// data set 1, regioni possedute dal regno
		// (mensile
		/////////////////////////////////////////////

		$resultOwnedRegions = $db -> query("select * from stats_historical
		where name = 'kingdomownedregions'
		and period > " . $onemonthago . "
		and   kingdom_id = " . $character -> region -> kingdom -> id .
		"	order by period asc" );

		$strDataSet1_w = "<dataset seriesname='Owned Regions' color='1F1209' showShadow='0' showValue='0' lineThickness='2'>";
		foreach ( $resultOwnedRegions as $ownedRegion )
			$strDataSet1_w .= "<set value='" . $ownedRegion -> param1."'/>";
		$strDataSet1_w .= "</dataset>";


		/////////////////////////////////////////////
		// data set 1, regioni possedute dal regno
		// (annuale)
		/////////////////////////////////////////////

		$resultOwnedRegions = $db -> query("select * from stats_historical
		where name = 'kingdomownedregions'
		and period > " . $oneyearago . "
		and   kingdom_id = " . $character -> region -> kingdom -> id .
		"	order by period asc" );

		$strDataSet1_m = "<dataset seriesname='Owned Regions' color='1F1209' showShadow='0' showValue='0' lineThickness='2'>";
		foreach ( $resultOwnedRegions as $ownedRegion )
			$strDataSet1_m .= "<set value='" . $ownedRegion -> param1."'/>";
		$strDataSet1_m .= "</dataset>";

		$max_kingdomownedregions = $db -> query( "select * from stats_historical where name = 'max_kingdomownedregions'
			and kingdom_id = " . $character -> region -> kingdom -> id ) -> as_array();
		$min_kingdomownedregions = $db -> query( "select * from stats_historical where name = 'min_kingdomownedregions'
			and kingdom_id = " . $character -> region -> kingdom -> id ) -> as_array();

		/////////////////////////////////////////////
		// data set 2, cittadini del regno (mese)
		/////////////////////////////////////////////

		$resultKingdomCitizens = $db -> query("select * from stats_historical
		where name = 'kingdompopulation'
		and period > " . $onemonthago . "
		and   kingdom_id = " . $character -> region -> kingdom -> id .
		"	order by period asc" );


		$strDataSet2_w = "<dataset seriesname='Kingdom Citizens' color='FF0000' showShadow='0' showValue='0' alpha='100' lineThickness='2'>";
		foreach ( $resultKingdomCitizens as $KingdomCitizen )
			$strDataSet2_w .= "<set value='" . $KingdomCitizen -> param1."'/>";
		$strDataSet2_w .= "</dataset>";

		$max_kingdompopulation = $db -> query( "select * from stats_historical where name = 'max_kingdompopulation'
			and kingdom_id = " . $character -> region -> kingdom -> id ) -> as_array();
		$min_kingdompopulation = $db -> query( "select * from stats_historical where name = 'min_kingdompopulation'
			and kingdom_id = " . $character -> region -> kingdom -> id ) -> as_array();

		/////////////////////////////////////////////
		// data set 2, cittadini del regno (anno)
		/////////////////////////////////////////////

		$resultKingdomCitizens = $db -> query("select * from stats_historical
		where name = 'kingdompopulation'
		and period > " . $oneyearago . "
		and   kingdom_id = " . $character -> region -> kingdom -> id .
		"	order by period asc" );


		$strDataSet2_m = "<dataset seriesname='Kingdom Citizens' color='FF0000' showShadow='0' showValue='0' alpha='100' lineThickness='2'>";
		foreach ( $resultKingdomCitizens as $KingdomCitizen )
			$strDataSet2_m .= "<set value='" . $KingdomCitizen -> param1."'/>";
		$strDataSet2_m .= "</dataset>";

		/////////////////////////////////////////////
		// data set 3, Ricchezza regno (mese)
		/////////////////////////////////////////////

		$resultKingdomHeritage = $db -> query("select * from stats_historical
		where name = 'kingdomheritage'
		and period > " . $onemonthago . "
		and   kingdom_id = " . $character -> region -> kingdom -> id .
		"	order by period asc" );

		$strDataSet3_w = "<dataset seriesname='Kingdom Heritage' color='1F1209' showShadow='0' showValue='0' alpha='100' lineThickness='2'>";
		foreach ( $resultKingdomHeritage as $KingdomHeritage )
			$strDataSet3_w .= "<set value='" . $KingdomHeritage -> param1."'/>";
		$strDataSet3_w .= "</dataset>";

		$max_kingdomheritage = $db -> query( "select * from stats_historical where name = 'max_kingdomheritage'
			and kingdom_id = " . $character -> region -> kingdom -> id ) -> as_array();
		$min_kingdomheritage = $db -> query( "select * from stats_historical where name = 'min_kingdomheritage'
			and kingdom_id = " . $character -> region -> kingdom -> id ) -> as_array();

		/////////////////////////////////////////////
		// data set 3, Ricchezza regno (anno)
		/////////////////////////////////////////////

		$resultKingdomHeritage = $db -> query("select * from stats_historical
		where name = 'kingdomheritage'
		and period > " . $oneyearago . "
		and   kingdom_id = " . $character -> region -> kingdom -> id .
		"	order by period asc" );

		$strDataSet3_m = "<dataset seriesname='Kingdom Heritage' color='1F1209' showShadow='0' showValue='0' alpha='100' lineThickness='2'>";
		foreach ( $resultKingdomHeritage as $KingdomHeritage )
			$strDataSet3_m .= "<set value='" . $KingdomHeritage -> param1."'/>";
		$strDataSet3_m .= "</dataset>";

		/////////////////////////////////////////////
		// data set 4, ricchezza media (mese)
		/////////////////////////////////////////////

		$resultKingdomAvgHeritage = $db -> query("select * from stats_historical
		where name = 'kingdomavgheritage'
		and period > " . $onemonthago . "
		and   kingdom_id = " . $character -> region -> kingdom -> id .
		"	order by period asc" );

		$strDataSet4_w = "<dataset seriesname='Kingdom Average Heritage' color='7990BA' showShadow='0' showValue='0' alpha='100' lineThickness='2'>";
		foreach ( $resultKingdomAvgHeritage as $KingdomAvgHeritage )
			$strDataSet4_w .= "<set value='" . $KingdomAvgHeritage -> param1."'/>";
		$strDataSet4_w .= "</dataset>";

		$max_kingdomavgheritage = $db -> query( "select * from stats_historical where name = 'max_kingdomavgheritage'
			and kingdom_id = " . $character -> region -> kingdom -> id ) -> as_array();
		$min_kingdomavgheritage = $db -> query( "select * from stats_historical where name = 'min_kingdomavgheritage'
			and kingdom_id = " . $character -> region -> kingdom -> id ) -> as_array();


		/////////////////////////////////////////////
		// data set 4, ricchezza media (anno)
		/////////////////////////////////////////////

		$resultKingdomAvgHeritage = $db -> query("select * from stats_historical
		where name = 'kingdomavgheritage'
		and period > " . $oneyearago . "
		and   kingdom_id = " . $character -> region -> kingdom -> id .
		"	order by period asc" );

		$strDataSet4_m = "<dataset seriesname='Kingdom Average Heritage' color='7990BA' showShadow='0' showValue='0' alpha='100' lineThickness='2'>";
		foreach ( $resultKingdomAvgHeritage as $KingdomAvgHeritage )
			$strDataSet4_m .= "<set value='" . $KingdomAvgHeritage -> param1."'/>";
		$strDataSet4_m .= "</dataset>";

		$strXML = "<graph caption='Owned Regions, Last Month' decimalPrecision='0'" . $commonoptions . ">";
		$strXML .= $strCategories_w;
		$strXML .= $strDataSet1_w;
		$strXML .=  "</graph>";

		$strXML2 = "<graph caption='Owned Regions, Last Year' decimalPrecision='0'" . $commonoptions . ">";
		$strXML2 .= $strCategories_m;
		$strXML2 .= $strDataSet1_m;
		$strXML2 .=  "</graph>";

		$strXML3 = "<graph caption='Population, Last Month' decimalPrecision='0'" . $commonoptions . ">";
		$strXML3 .= $strCategories_w;
		$strXML3 .= $strDataSet2_w;
		$strXML3 .=  "</graph>";

		$strXML4 = "<graph caption='Population, Last Month' decimalPrecision='0'" . $commonoptions . ">";
		$strXML4 .= $strCategories_m;
		$strXML4 .= $strDataSet2_m;
		$strXML4 .=  "</graph>";


		$strXML5 = "<graph caption='Heritage, Last Month' decimalPrecision='0'" . $commonoptions . ">";
		$strXML5 .= $strCategories_w;
		$strXML5 .= $strDataSet3_w;
		$strXML5 .=  "</graph>";

		$strXML6 = "<graph caption='Heritage, Last Year' decimalPrecision='0'" . $commonoptions . ">";
		$strXML6 .= $strCategories_m;
		$strXML6 .= $strDataSet3_m;
		$strXML6 .=  "</graph>";


		$strXML7 = "<graph caption='Avg Heritage, Last Month' decimalPrecision='0'" . $commonoptions . ">";
		$strXML7 .= $strCategories_w;
		$strXML7 .= $strDataSet4_w;
		$strXML7 .=  "</graph>";

		$strXML8 = "<graph caption='Avg Heritage, Last Year' decimalPrecision='0'" . $commonoptions . ">";
		$strXML8 .= $strCategories_m;
		$strXML8 .= $strDataSet4_m;
		$strXML8 .=  "</graph>";


		//kohana::log('debug', $strXML );
		//kohana::log('debug', $strXML2 );
		//kohana::log('debug', $strXML3 );

		$chartinfo['kingdomownedregions_w']['html'] = renderChartHTML(
		url::base() . "application/libraries/vendors/FusionChartsFree/Charts/FCF_MSLine.swf", "", $strXML,  "graph1", $chartwidth, $chartheight);
		$chartinfo['kingdomownedregions_w']['max'] = $max_kingdomownedregions;
		$chartinfo['kingdomownedregions_w']['min'] = $min_kingdomownedregions;

		$chartinfo['kingdomownedregions_m']['html'] = renderChartHTML(
		url::base() . "application/libraries/vendors/FusionChartsFree/Charts/FCF_MSLine.swf", "", $strXML2, "graph2", $chartwidth, $chartheight);

		$chartinfo['kingdompopulation_w']['html'] = renderChartHTML(
		url::base() . "application/libraries/vendors/FusionChartsFree/Charts/FCF_MSLine.swf", "", $strXML3, "graph3", $chartwidth, $chartheight);
		$chartinfo['kingdompopulation_w']['max'] = $max_kingdompopulation;
		$chartinfo['kingdompopulation_w']['min'] = $min_kingdompopulation;

		$chartinfo['kingdompopulation_m']['html'] = renderChartHTML(
		url::base() . "application/libraries/vendors/FusionChartsFree/Charts/FCF_MSLine.swf", "", $strXML4, "graph4", $chartwidth, $chartheight);

		$chartinfo['kingdomheritage_w']['html'] = renderChartHTML(
		url::base() . "application/libraries/vendors/FusionChartsFree/Charts/FCF_MSLine.swf", "", $strXML5, "graph5", $chartwidth, $chartheight);

		$chartinfo['kingdomheritage_w']['max'] = $max_kingdomheritage;
		$chartinfo['kingdomheritage_w']['min'] = $min_kingdomheritage;

		$chartinfo['kingdomheritage_m']['html'] = renderChartHTML(
		url::base() . "application/libraries/vendors/FusionChartsFree/Charts/FCF_MSLine.swf", "", $strXML6, "graph6", $chartwidth, $chartheight);

		//

		$chartinfo['kingdomavgheritage_w']['html'] = renderChartHTML(
		url::base() . "application/libraries/vendors/FusionChartsFree/Charts/FCF_MSLine.swf", "", $strXML7, "graph7", $chartwidth, $chartheight);

		$chartinfo['kingdomavgheritage_w']['max'] = $max_kingdomavgheritage;
		$chartinfo['kingdomavgheritage_w']['min'] = $min_kingdomavgheritage;

		$chartinfo['kingdomavgheritage_m']['html'] = renderChartHTML(
		url::base() . "application/libraries/vendors/FusionChartsFree/Charts/FCF_MSLine.swf", "", $strXML8, "graph8", $chartwidth, $chartheight);

		return $chartinfo;

	}


	/**
	* Sends an alert to admin
	* @param string subject
	* @param string text
	* @return none
	*/

	function alertadmins( $subject, $text, $attachment = null )
	{

		$res = Database::instance() -> query("
		SELECT email
		FROM   users u, roles_users ru, roles r
		WHERE  u.id = ru.user_id
		AND    ru.role_id = r.id
		AND    r.name = 'admin'");

		foreach( $res as $row )
			Utility_Model::mail( $row -> email, 'ME Alert: ' . $subject, $text, $attachment );

		return;
	}

	function alertstaff( $subject, $text )
	{

		$res = Database::instance() -> query("

		SELECT email
		FROM   users u, roles_users ru, roles r
		WHERE  u.id = ru.user_id
		AND    ru.role_id = r.id
		AND    r.id >= 98");

		foreach( $res as $row )
			Utility_Model::mail( $row -> email, 'ME Alert: ' . $subject, $text );

		return;
	}

	/*
	* Lancia un dado, tenendo conto del bonus
	* e rimuovendo outlier
	*/

	public function rollDice(
		$numDice = 2,
		$bonus = 0,
		$highThrowsToRemove = 0,
		$lowThrowsToRemove = 0,
		$lowerLimit = 1, $upperLimit = 6)
    {
		$i = 0;
        $rolls = array();

        for ($i = 0; $i < $numDice; $i++)
			$rolls[] = rand( $lowerLimit, $upperLimit) ;

		//var_dump( '-> Original: ' );
		//var_dump( $rolls );

		asort( $rolls );

		//var_dump( '-> Sorted: ');
        //var_dump( $rolls );

		array_splice($rolls, 0, $lowThrowsToRemove);

		//var_dump( "-> Low taken off: ($lowThrowsToRemove)" );
        //var_dump( $rolls );

		if ( $highThrowsToRemove > 0 )
			array_splice($rolls, -$highThrowsToRemove);

		//var_dump( "-> High taken off: ($highThrowsToRemove)" );
		//var_dump( $rolls );

		$value = 0;
		for ( $i = 0; $i < count($rolls); $i ++ )
			$value += $rolls[$i];

		$roll = $value / count( $rolls );

		//var_dump( '-> roll: ' . $roll );

		return ( $roll );

    }

	/**
	* truncateHtml can truncate a string up to a number of characters while preserving whole words and HTML tags
	*
	* @param string $text String to truncate.
	* @param integer $length Length of returned string, including ellipsis.
	* @param string $ending Ending to be appended to the trimmed string.
	* @param boolean $exact If false, $text will not be cut mid-word
	* @param boolean $considerHtml If true, HTML tags would be handled correctly
	*
	* @return string Trimmed string.
	*/

	function truncateHtml(
		$text,
		$length = 100,
		$ending = '...',
		$exact = false,
		$considerHtml = true)
	{
		if ($considerHtml) {
			// if the plain text is shorter than the maximum length, return the whole text
			if (strlen(preg_replace('/<.*?>/', '', $text)) <= $length) {
				return $text;
			}
			// splits all html-tags to scanable lines
			preg_match_all('/(<.+?>)?([^<>]*)/s', $text, $lines, PREG_SET_ORDER);
			$total_length = strlen($ending);
			$open_tags = array();
			$truncate = '';
			foreach ($lines as $line_matchings) {
				// if there is any html-tag in this line, handle it and add it (uncounted) to the output
				if (!empty($line_matchings[1])) {
					// if it's an "empty element" with or without xhtml-conform closing slash
					if (preg_match('/^<(\s*.+?\/\s*|\s*(img|br|input|hr|area|base|basefont|col|frame|isindex|link|meta|param)(\s.+?)?)>$/is', $line_matchings[1])) {
						// do nothing
					// if tag is a closing tag
					} else if (preg_match('/^<\s*\/([^\s]+?)\s*>$/s', $line_matchings[1], $tag_matchings)) {
						// delete tag from $open_tags list
						$pos = array_search($tag_matchings[1], $open_tags);
						if ($pos !== false) {
						unset($open_tags[$pos]);
						}
					// if tag is an opening tag
					} else if (preg_match('/^<\s*([^\s>!]+).*?>$/s', $line_matchings[1], $tag_matchings)) {
						// add tag to the beginning of $open_tags list
						array_unshift($open_tags, strtolower($tag_matchings[1]));
					}
					// add html-tag to $truncate'd text
					$truncate .= $line_matchings[1];
				}
				// calculate the length of the plain text part of the line; handle entities as one character
				$content_length = strlen(preg_replace('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|[0-9a-f]{1,6};/i', ' ', $line_matchings[2]));
				if ($total_length+$content_length> $length) {
					// the number of characters which are left
					$left = $length - $total_length;
					$entities_length = 0;
					// search for html entities
					if (preg_match_all('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|[0-9a-f]{1,6};/i', $line_matchings[2], $entities, PREG_OFFSET_CAPTURE)) {
						// calculate the real length of all entities in the legal range
						foreach ($entities[0] as $entity) {
							if ($entity[1]+1-$entities_length <= $left) {
								$left--;
								$entities_length += strlen($entity[0]);
							} else {
								// no more characters left
								break;
							}
						}
					}
					$truncate .= substr($line_matchings[2], 0, $left+$entities_length);
					// maximum lenght is reached, so get off the loop
					break;
				} else {
					$truncate .= $line_matchings[2];
					$total_length += $content_length;
				}
				// if the maximum length is reached, get off the loop
				if($total_length>= $length) {
					break;
				}
			}
		} else {

			if (strlen($text) <= $length) {
				return $text;
			} else {
				$truncate = substr($text, 0, $length - strlen($ending));
			}
		}
		// if the words shouldn't be cut in the middle...
		if (!$exact) {
			// ...search the last occurance of a space...
			$spacepos = strrpos($truncate, ' ');
			if (isset($spacepos)) {
				// ...and cut the text in this position
				$truncate = substr($truncate, 0, $spacepos);
			}
		}
		// add the defined ending to the text
		$truncate .= $ending;
		if($considerHtml) {
			// close all unclosed html-tags
			foreach ($open_tags as $tag) {
				$truncate .= '</' . $tag . '>';
			}
		}
		return $truncate;
	}

	/*
	* Visualizza i nodi di una struttura ad albero
	* @parent_id ID struttura parente
	* @level Livello dell' albero
	*/


	function helper_displaychildnodes($parent_id, $level, $index, $data, &$output)
	{

		$parent_id = $parent_id === NULL ? "NULL" : $parent_id;
		if (isset($index[$parent_id])) {
			foreach ($index[$parent_id] as $id) {

				if ( !is_null ($data[$id] -> character_id ) )
				{
					$char = ORM::factory('character', $data[$id]-> character_id);
					$output .=
						"<div class='childlevel_".$level."'>" .
						"<div style='float:left;margin-right:5px'>";
						if ( $level == 0 )
							$output .= Character_Model::display_avatar( $char -> id, 'l', 'border');
						else
							$output .= Character_Model::display_avatar( $char -> id, 's', 'border');
						$output .=
						"</div>".
						"<div>" .
						kohana::lang('structures.' . $data[$id] -> type. '_' . $data[$id] -> churchname ) . "<br/>" .
						'Location: ' . kohana::lang($data[$id] -> regionname) . "<br/>" .
						Character_Model::create_publicprofilelink( $char -> id, $char -> name ) .
						':&nbsp;' . $char -> get_rolename(true) .
						"</div>" .
						"</div>" .
						"<br style='clear:both'/>";
				}
				else
				{
					$output .= "<div class='childlevel_".$level."'>" .
						"<div style='float:left;margin-right:5px'>" .
						Character_Model::display_avatar( 0, 's', 'border') .
						"</div>".
						"<div>" .
						kohana::lang('structures.' . $data[$id] -> type . '_' . $data[$id] -> churchname ) . "<br/>" .
						'Location: ' . kohana::lang($data[$id] -> regionname) .
						"<br/>" . kohana::lang('global.uncontrolledstructure') .
						"</div>" .
					"</div>" .
					"<br style='clear:both'/>";

				}

				$output .= Utility_Model::helper_displaychildnodes(
					$id, $level + 1, $index, $data, 	$output);
			}
		}

	}

	/**
	* Convert currency in real time
	* @param int amounttoconvert
	* @param string currency to convert from
	* @param string currency to convert to
	* @return int amountconverted
	*/

	function convertCurrency($amount, $from, $to){

		$url = "https://www.google.com/finance/converter?a=" . $amount . "&from=" . $from . "&to=" . $to;
		$ch = curl_init();
		$timeout = 0;
		curl_setopt ($ch, CURLOPT_URL, $url);
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		$rawdata = curl_exec($ch);
		curl_close($ch);
		$matches = array();
		preg_match_all("|<span class=bld>(.*)</span>|U", $rawdata, $matches);
		$result = explode(" ", $matches[1][0]);
		 return round($result[0], 2);


	}

	/**
	 * Send notification to a user
	 * @param int $user_id
	 * @param string $subject
	 * @param string $text Text
	 * @param string $url Relative URL to use when user clicks the notification
	 * @return String
	 */

	function send_notification( $user_id, $subject, $text, $url = null )
	{

		$rc = true;

		// get user object

		$user = ORM::factory('user', $user_id);

		if ($user -> loaded)
		{
			if ($user -> receiveigmessagesonemail == 'Y' )
			{
				kohana::log('debug', 'nowhere?: ' . strpos($user -> email, 'nowhere.com'));
				if (strpos($user -> email, 'nowhere.com') !== false)
					$rc = Utility_Model::send_fb_notification( $user -> fb_id, $subject, $url);
				else
					$rc = Utility_Model::mail( $user -> email, $subject, $text);
			}
		}

		return $rc;

	}

	/**
	 * Send Facebook notification using CURL
	 * @param string $recipientFbid Scoped recipient's FB ID
	 * @param string $text Text of notification (<150 chars)
	 * @param string $url Relative URL to use when user clicks the notification
	 * @return String
	 */


	function send_fb_notification($recipientFbid, $text, $url) {
	{

		if ( kohana::config('medeur.sendnotifications', false) == false )
		{
			kohana::log('info', '-> Not sending FB message because notifications are disabled.');
			return false;
		}

		$text = strip_tags($text);

		kohana::log('info', '-> Sending fb notification to user: ' . $recipientFbid);

		$AppID = kohana::config('medeur.facebook_app_id');
		$AppSecret = kohana::config('medeur.facebook_app_secret');

		$fb_app_token =  $AppID . "|" . $AppSecret;
		if (!is_null( $url) )
			$href = urlencode($url);
		else
			$href = '';

		$post_data = "access_token=". $fb_app_token ."&template={$text}&href={$href}";

		//var_dump($post_data);exit;

		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, "https://graph.facebook.com/v2.10/". $recipientFbid ."/notifications");
		curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 0);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		$rawdata = curl_exec($curl);
		$data = json_decode($rawdata);

		curl_close($curl);
		if (key($data) == 'error' )
		{
			return false;
		}
		else
			return true;

	}

	}

	/*
	* Crypta testo secondo SSL
	* @param str $texttoencrypt Testo da crittografare
	* @return str $encryptedtext Testo crittografato
	*/

	public function encrypt_text( $texttoencrypt )
	{
		$encryptionMethod = "AES-256-CBC";
		$secretHash = "";
		$iv = mcrypt_create_iv(16, MCRYPT_RAND);
		$encryptedText = openssl_encrypt($texttoencrypt,$encryptionMethod,$secretHash, 0, $iv);
		$encryptedText .= ':' . base64_encode($iv);
		return $encryptedtext;
	}

	/*
	* Decrypta testo secondo SSL
	* @param string $texttodecrypt Testo da decrittare
	* @return string Testo crittografato
	*/

	public function decrypt_text($texttodecrypt)
	{
		$encryptionMethod = "AES-256-CBC";
		$secretHash = "";
		list($_texttodecrypt, $iv) = explode(':', $texttodecrypt);
		return openssl_decrypt($_texttodecrypt, $encryptionMethod,$secretHash, 0, base64_decode($iv));
	}

}
?>
