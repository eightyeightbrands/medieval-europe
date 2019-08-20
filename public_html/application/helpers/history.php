<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * History helper class.
 *
 * $Id$
 *
 * @package    Custom
 * @author     -
 * @copyright  -
 * @license    -
 */

class history
{
    public static function push($url = false)
    {
        if (isset(Kohana::instance()->exclude_history) and Kohana::instance()->exclude_history === true)
            return;

        $current_url = ($url) ? $url : url::current(true);

        if ( ! ($tmp = Session::instance()->get('url_history')))
            $_SESSION['url_history'] = (array) $current_url;

        if (current($_SESSION['url_history']) != $current_url)
            array_unshift($_SESSION['url_history'], $current_url);

        $_SESSION['url_history'] = array_unique(array_slice($_SESSION['url_history'], 0, 5));
    }

    public static function pop()
    {
        return isset($_SESSION['url_history'][0]) ? array_shift($_SESSION['url_history']) : history::referer();
    }

    public static function end()
    {
        return isset($_SESSION['url_history'][0]) ? $_SESSION['url_history'][0] : history::referer();
    }

    public static function referer()
    {
        $ref = '';

        if (isset($_SERVER['HTTP_REFERER']) and strpos($_SERVER['HTTP_REFERER'], url::base(true, true)) !== false)
            $ref = (strpos($_SERVER['HTTP_REFERER'], url::current(true)) !== false) ? '' : $_SERVER['HTTP_REFERER'];

        return $ref;
    }

    public static function clear()
    {
        $_SESSION['url_history'] = array();
    }

} 
?>