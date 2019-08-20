<?php defined('SYSPATH') OR die('No direct access allowed.');
class My_I18n_Model extends ORM
{
	/*
	*	Funzione che traduce runtime una stringa con parametri
	* Il primo parametro � la stringa da tradurre.
	* Gli altri sono parametri da espandere
	* Esempio: __global.fight;__weapon.sword;Mario Rossi
	* => sprintf( kohana::lang('global.fight'), kohana::lang('weapon.sword'), 'Mario Rossi');
	*	@param text: testo da tradurre.
	*	@return la stringa tradotta
	*/

	function translate( $text )
	{
		$i=0;

		$s = explode(';', $text);

		$parameters = array();
		$message = null;

		//kohana::log('debug', '-> Translating: ' . $text );

		foreach ( $s as $p )
		{

			if ( substr( $p, 0,2 ) == '__' )
				$p = kohana::lang(substr( $p, 2 ));

			if ( $i == 0 )
				$message = $p;
			else
				if (is_array($p))
					$parameters[] = '()';
				else
					$parameters[] = $p;

			$i++;
		}

		kohana::log('info', "translate: message: $message" . " parameters: " . kohana::debug( $parameters ) );

		try {
			$t = vsprintf ( $message, $parameters );
		} catch (Exception $e)
		{
			kohana::log('error', 'error in translating: ' . $message . ' exception: ' .$e -> getMessage());
			$t = null;
		}
		return $t;
	}
}
