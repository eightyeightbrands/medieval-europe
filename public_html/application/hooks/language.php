<?php defined('SYSPATH') OR die('No direct access allowed.');

// Hook per la gestione della lingua
function Selectlang()
{

	// Quando richiamo il costruttore controllo se esiste il cookie impostato della lingua
	// Se non è impostato allora utilizzo la lingua di default
	// Se è impostato allora setto la lingua appropriata
	// La creazione del cookies dovrebbe essere demandata alla funzione di login

	// E' possibile inoltre cambiare il cookies dall'interfaccia. In questo caso dovrò ricaricare la pagina  

	$cookie_value = cookie::get('lang', $default_value = "en_US");	

	switch ( $cookie_value )
	{
	
		case 'en_US': Kohana::config_set('locale.language', array('en_US', 'English_United States'));	break;
		case 'fr_FR': Kohana::config_set('locale.language', array('fr_FR', 'French'));	break;
		case 'cz_CZ': Kohana::config_set('locale.language', array('cz_CZ', 'Czech'));	break;
		case 'sk_SK': Kohana::config_set('locale.language', array('sk_SK', 'Slovak'));	break;
		case 'it_IT': Kohana::config_set('locale.language', array('it_IT', 'Italian_Italy')); break;
		case 'de_DE': Kohana::config_set('locale.language', array('de_DE', 'German_Germany')); break;
		case 'ro_RO': Kohana::config_set('locale.language', array('ro_RO', 'Romanian_Romania')); break;
		case 'nl_NL': Kohana::config_set('locale.language', array('nl_NL', 'Dutch_Netherlands')); break;		
		case 'rs_RS': Kohana::config_set('locale.language', array('rs_RS', 'Serbia')); break;
		case 'al_AL': Kohana::config_set('locale.language', array('al_AL', 'Albania')); break;
		case 'bg_BG': Kohana::config_set('locale.language', array('bg_BG', 'Bulgaria')); break;
		case 'tr_TR': Kohana::config_set('locale.language', array('tr_TR', 'Turkish')); break;
		case 'ru_RU': Kohana::config_set('locale.language', array('ru_RU', 'Russian')); break;
		case 'hu_HU': Kohana::config_set('locale.language', array('hu_HU', 'Hungary')); break;
		case 'gr_GR': Kohana::config_set('locale.language', array('gr_GR', 'Greek')); break;
		case 'pt_PT': Kohana::config_set('locale.language', array('pt_PT', 'Portuguese')); break;
		default: Kohana::config_set('locale.language', array('en_US', 'English_United States'));	break;
	}
	
	
}

// Aggiungo l'evento prima di istanziare un controllore
Event::add('system.pre_controller', 'Selectlang');
?>
