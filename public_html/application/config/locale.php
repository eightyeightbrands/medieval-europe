<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * @package  Core
 *
 * Default language locale name(s).
 * First item must be a valid i18n directory name, subsequent items are alternative locales
 * for OS's that don't support the first (e.g. Windows). The first valid locale in the array will be used.
 * @see https://php.net/setlocale
 */
$config['language'] = array('it_IT', 'Italiano_Italia');

/**
 * Locale timezone. Defaults to use the server timezone.
 * @see https://php.net/timezones
 */
 
$config['timezone'] = 'UTC';
