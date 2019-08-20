<?php defined('SYSPATH') OR die('No direct access allowed.');

class Kingdom_Nobletitle_Model extends ORM
{
	protected $belongs_to = array
	(
		'kingdom'
	);
    
	/**
	* Cerca tutti i titoli nobiliare personalizzati di un regno
	* @param    int     $kingdom_id       id del regno da ricercare
	* @return   array   $modifiedtitles   array di titoli modificati
	*/
	public static function get_customisedtitles($kingdom_id)
	{
		
		$titles = Database::instance()->query
		( 
			"select *
			from    kingdom_nobletitles knb
			where   knb.kingdom_id = {$kingdom_id}"
		)
		->as_array();
		
		$modifiedtitles = array();
		
		foreach ($titles as $title)
		{
			$modifiedtitles[$title->title]['customisedtitle_m'] = $title->customisedtitle_m;
			$modifiedtitles[$title->title]['customisedtitle_f'] = $title->customisedtitle_f;
		}
			
		return $modifiedtitles;		
	}
	
	/**
	* Inserisce o aggiorna il titolo nobiliare personalizzato
	* @param    int     $kingdom_id       id del regno da ricercare
	* @param    string  $originaltitle    titolo nobiliare originale
	* @param    string  $m                versione modificata maschile
	* @param    string  $f                versione modificata femminile
	* @param    array   $_FILES           immagine custom
	* @return   none
	*/
	public static function insert_or_update($kingdom_id, $originaltitle, $m, $f, $file)
	{
		$title = ORM::factory('kingdom_nobletitle')
		->where
		(
			array
			( 
			'kingdom_id' => $kingdom_id,
			'title' => $originaltitle
			)
		)
		-> find();
		
		// Se esiste già il record allora lo aggiorno
		// altrimenti inserisco una nuova riga
		if ($title->loaded) 
		{
			Database::instance() -> query
			(
				"update kingdom_nobletitles 
				set customisedtitle_m = '{$m}', customisedtitle_f = '{$f}'
				where kingdom_id = {$kingdom_id} and title = '{$originaltitle}'"
			);
		}
		else
		{
			Database::instance() -> query
			(
				"insert into kingdom_nobletitles 
				values (null, {$kingdom_id}, '{$originaltitle}', '{$m}', '{$f}')"
			);
		}
		
		// Verifico se è stato fatto l'upload di una
		// immagine custom
		//var_dump($_FILES['custom_title_image']);exit;
		if ($_FILES['custom_title_image']['size'] > 0)
		{
			$image = Validation::factory( $_FILES )
			-> add_rules('custom_title_image', 'upload::valid', 'upload::type[png]', 'upload::size[512K]');
			
			if ( $image -> validate() )
			{
					
				// Imposto la directory relativa il regno
				$path = DOCROOT . 'media/images/badges/nobletitles/custom/'.$kingdom_id;
				
				// Se non esiste la directory, la creo
				if (! is_dir($path))
					mkdir ($path, 0755, true);
				// Memorizzo l'immgaine custom con il titolo nobiliare originale
				$imagecustom = upload::save($_FILES['custom_title_image']);
 
				Image::factory($imagecustom)
				->resize(50, 50, Image::AUTO)
				->save($path.'/'.$originaltitle.'.png');
				// Rimuovo l'immagine temp
				//unlink($imagecustom);
			}
			else
			{
				return false;
			}
		}
		// Tutte le operazioni sono andate a buon fine
		return true;
	}
}
