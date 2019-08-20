<?php defined('SYSPATH') OR die('No direct access allowed.');

class Wardrobe_Model
{

const APPROVALPRICEPERREQUEST = 150;

function get_approvalprice()
{
	return self::APPROVALPRICEPERREQUEST;
}

/**
* Elenca le richiesta di approvazione pendenti
* @param char oggetto char
* @return array
*/

function listpendingapprovalrequest( $char )
{

	$pendingrequest = ORM::factory('wardrobe_approvalrequest') ->	
		where ( array ( 
			'character_id' => $char -> id,
			'status' => 'new' ) ) -> find();
			
	return $pendingrequest;
}

/*
* Get wardrobe item cfg
* @param string $name item name
* @return data
*/

function get_wardrobeitemdata( $name )
{
	
	$cfg = Configuration_Model::get_wardrobeitemcfg();
	return $cfg[$name];
	
}


/**
* Moves customized imaged making them active
* and defining the slot
* @param  mixed Customized clothes approval request
* @return none
*/

function approvecustomizeditems( $request )
{
	$path = DOCROOT . 'media/images/characters/wardrobe/' . $request -> character_id ;	
	$character = Character_Model::get_info( $request -> character_id );

	foreach ( array( 
		'clothes',
		'armors',
		'weapons',		
		'aspect'
		) as $itemtype ) 
	{
		// Forza la creazione della directory 
		
		if ( !is_dir ( $path . '/' . $itemtype ))
			mkdir($path . '/' . $itemtype );
		if ( !is_dir ( $path . '/' . $itemtype . '/' . 'temp' ) )
			mkdir($path . '/' . $itemtype . '/' . 'temp');
		if ( !is_dir ( $path . '/' . $itemtype . '/' . 'approved' ) )
			mkdir($path . '/' . $itemtype . '/' . 'approved');
				
		$tempdir = opendir( $path . '/' . $itemtype . '/' . 'temp' );
		$approveddir = opendir( $path . '/' . $itemtype . '/' . 'approved' );
		
		while (($file = readdir($tempdir)) !== false)
		{
			// se esiste un file, copialo nella directory approved e setta lo slot
			
			if( $file != "." and $file != ".." and strstr( $file, '.png' ) )
			{					
				rename( $path . '/' . $itemtype . '/temp/' . $file, 
						$path . '/' . $itemtype . '/approved/' . $file );
				
				list($tag, $slot) = explode( "-", basename($file, ".png") );
				
				$character -> modify_stat( 
					'wardrobeset', 
					$slot,
					$tag,
					null,
					true,
					$tag.'-'.$slot,
					$itemtype . '/approved/' . $tag . '-' . $slot . '.png' );
					
			}
			
		}
	}
}

/**
Stampa codice HTML per gli item di default
@param type type of helper (changes produced html) (items)
@param char_id id of char
@param category category of item 
@param tag item tag
@param sex char sex
@param name item name 
@return html
*/

function helper_defaultitem
(
	$type,	
	$category, 	
	$tag,
	$sex,
	$name
)
{
	
	$filename = 'media/images/' . $type . '/' . $category . '/' . $tag . '_' . $sex . '.png';
	$nocustomimage = 'media/images/template/noimage.png';
	
	$html = 
		"<div class='center' width='20%' style='float:left;margin-left:1%'>".
		"<b>" . 
		Utility_Model::truncateHtml(
					kohana::lang($name), 17, '...', true, false ). '</b><br/>' .
		html::image( $filename, 
				array( 
				'class' => 'ownwardrobeitem',
				'title' => kohana::lang( $name)));
		$html .= "</div>";
	
	return $html;

}


/**
* Stampa codice HTML Per il guardaroba
* @param type type of helper (changes produced html) (items)
* @param int id of character
* @param category parentcategory of item
* @param tag item tag
* @param sex char sex
* @param name item name 
* @return html
*/

function helper_ownwardrobe
(
	$type,
	$char,
	$category, 	
	$tag,	
	$name
)
{
	
	// nome del file
	$path = "media/images/characters/wardrobe/" . $char -> id ;
	// filename dell' immagine default
	if ($type =='items')
		$defaultfilename = 
			'media/images/' . $type . '/wearable/' . $category . '/' . $tag . '_' . $char -> sex . '.png';
	
	if ($type =='characters')
		$defaultfilename = 
			'media/images/characters/aspect/' . $tag . '_' . $char -> sex . '.png';
		
	//nome del file senza estensione
	$char -> sex;
	//filename dell' immagine da visualizzare se la cust.ne non è stata caricata
	$nocustomimage = 'media/images/template/noimage.png';
	
	$i = 0;		
	$stat = Character_Model::get_stat_d( $char -> id, 'wardrobeset', $tag . '_' . $char -> sex, null );		
	
	$html = '';
	$html .= "<tr>";
		
	while ( $i <= 5 )
	{
	
		$commands = '';
		// Prima Cella: Immagini di Default		
		if ( $i == 0 ) 
		{
			$html .= "<td class='center' width='16.6%'>";
			if ( $i == $stat -> value )
				$html .= "<span style='color:#cc0000'>" . Utility_Model::truncateHtml(
						kohana::lang($name), 15, '...', true, false ) . "* </span>";
			else
				$html .= Utility_Model::truncateHtml(
						kohana::lang($name), 15, '...', true, false );
			
			$html .= html::image( $defaultfilename, 
					array( 
					'class' => 'ownwardrobeitem',	
					'title' => kohana::lang($name)));			
			
			
			$commands .=
					"<div style='float:right' title='" .
						kohana::lang('wardrobe.setslottoshow') . "'>" .	html::anchor( '/wardrobe/selectslot/' . $tag  . '/' . $i,
							html::image('media/images/template/select_icon.png')) .				
					"</div>";
		}
		else
		{
	
			// carico lo slot selezionato
			// HEADER
			
			$html .= 		
				"<td class='center' width='16.6%'>";					
			
			// header
			
			
			if ( $i == $stat -> value )
				$html .= "<span style='color:#cc0000'>" . kohana::lang('wardrobe.customization', $i ) . "* </span>";
			else
				$html .= kohana::lang('wardrobe.customization', $i );
				
			$html .= "</span>";
			
			
			// altrimenti controllo se c'è una immagine in 
			// attesa di approvazione
			
			$displayselectslot = false;
		
			// esiste una immagine approvata?
			
			$approvedfilename = $path . '/' . $category . '/approved/' . 
				$tag . '_' . $char -> sex . '-' . $i . '.png' ;	
				
			$tempfilename = $path . '/' . $category . '/temp/' . 
				$tag . '_' . $char -> sex . '-' . $i . '.png' ;									
			
			
			//kohana::log('debug', '-> tempimage: ' . $tempfilename . ' approvedimage: ' . $approvedfilename );
			
			$image = 
				html::image( $nocustomimage,
					array( 
					'class' => 'ownwardrobeitem',
					'title' => kohana::lang($name)));
			
			$imagetype = 'noimage';
			
			if ( is_file( $approvedfilename ) )
			{
				$image = 
				html::image( $approvedfilename,
					array( 
					'class' => 'ownwardrobeitem',
					'title' => kohana::lang($name)));
				$imagetype = 'approved';
				
				
			}
			
			// esiste un immagine uploadata, in attesa di approvazione?							
			if ( is_file( $tempfilename ) )
			{
				$image = 
				html::image( $tempfilename,
					array( 
					'class' => 'ownwardrobeitem',
					'title' => kohana::lang($name)));								
				$imagetype = 'needapproval';
			}
			//kohana::log('debug', '-> item: ' . $name . ', slot: ' . $i . ' type: ' . $imagetype );
			$html .= $image;
			
			if ( $imagetype == 'approved' )
				$commands .= "<div style='float:right' title='" .
					kohana::lang('wardrobe.setslottoshow') . "'>" .	html::anchor( '/wardrobe/selectslot/' . $tag  . '/' . $i,
						html::image('media/images/template/select_icon.png')) .				
				"</div>";				
			
			if ( $imagetype == 'approved' or $imagetype == 'noimage' )
				$commands.= "<div title='" . 
					kohana::lang('wardrobe.uploadcustomisedimage') . "' class='upload-file-container' data-type=" . $tag . " data-slotid=" . $i. ">
						<input id='file-" . $tag . '-' . $i . "' type='file' name='" . $tag . '-' . $i . '-' . $category . "' />
						  </div>";
			if ( $imagetype == 'needapproval' )
				$commands .= "<div>" . kohana::lang('wardrobe.tobeapproved') . "</div>";						
			
		}		
		
						  
		$html .= "<div style='height:20px;border:0px solid'>" . $commands . "</div>";
		$html .= "</td>";
		
		$i++;
	}	
	
	$html.= '</tr>';
		
	return $html;
}

function helper_ownwardrobeitem(
	$type,
	$category, 	
	$tag,
	$sex,
	$name
)
{
	
	$html = 
		"<div class='atelieritemwrapper'>" .
		"<div class='center'><b>" . 
			Utility_Model::truncateHtml(
					kohana::lang($name), 17, '...', true, false ). '</b></div>' .
		"<div class='framecustomizeditems'>".			
			html::image( 'media/images/' . $type . '/' . $category . '/' . $tag . '_' . $sex . '.png', 
				array( 
				'class' => 'ownwardrobeitem',
				'title' => kohana::lang( $name),
		)) . 
		"</div>". 
		"</div>";
		
	return $html;
}

/**
* Helper, stampa HTML
* @param filenamewithoutextension file name senza estensione
* @param filename filename
* @param section sezione (avatars, armors, weapons ...)
* @param subsection sottosezione (helmets, ... )
* @returns string $html
*/

function helper_itemform( 
	$filenamewithoutextension,
	$filename,
	$section,
	$subsection,		
	$description = '',
	$buyflag = true
	)
{
	if ( $section == 'avatars' )
	{
		$class = 'frame_avatar';
		$width = 111;
		$height = 138;
	}
	else
	{
		$class = 'frame_customizeditem';
		$width = 149;
		$height = 250;
	}
	
	
	$cfg = Configuration_Model::get_wardrobeitemcfg();
	$cfgbonuses = Configuration_Model::get_premiumbonuses_cfg();	
	//var_dump($cfgbonuses);
	//var_dump($filenamewithoutextension);exit;
	//var_dump($cfgbonuses[$cfg[$filenamewithoutextension]->bonusname]);exit;
	//ORM::factory('cfgwardrobeitem') -> where( 'tag', $filenamewithoutextension) -> find();	
	
	if (!isset($cfg[$filenamewithoutextension]) )	
		return '';
	
	$html = "
	<div class='atelieritemwrapper'>
		<div class='center' title='" . kohana::lang($description) . "'>
		<b> " . Utility_Model::truncateHtml(kohana::lang($description), 30) . "</b>" . "</div>" .
		"<div id='$filenamewithoutextension' class='atelier'>" .
		form::open('bonus/buy') .
		form::hidden( 'name', $cfg[$filenamewithoutextension] -> bonusname ) .
		form::hidden( 'cut', 1) .
		form::hidden( 'itemname', $filenamewithoutextension ) .
		form::hidden( 'section', $section ) .
		form::hidden( 'subsection', $subsection ) .				
		
		"<div class='" . $class . "'>".		
		html::image( 
			$filename,			
			array(
				'class' => $section . '_customizeditem',
				'align'=>'center',				
			), 
			false). 
		"</div>" . 
			"ID: " . $filenamewithoutextension . "<br/>" .
			"Author: " . $cfg[$filenamewithoutextension] -> author;
		//var_dump($cfgbonuses[$cfg[$filenamewithoutextension] -> bonusname]['cuts']); exit;
		if ( $buyflag )
		{
			$html .= "<br/>" . 
			"<div class='evidence'>" . 
				$cfgbonuses[$cfg[$filenamewithoutextension] -> bonusname]['cuts'][1]['price'] . '&nbsp;' . kohana::lang('global.doubloons') . "</div>".
			"<center>" . 
			form::submit( 
				array( 
					'id' => 'submit', 
					'name' => 'buy',
					'class' => 'button button-small', 
					'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')', 
					'value' => Kohana::lang('global.buy'))) .
			"</center>" ;
			
		}
		$html .= form::close();
		$html .= "</div></div>";
		
	return $html;

}
	
/**
* funzione per caricare le immagini
* @param char oggetto char
* @param equippeditem vettore contenente gli item
* @param mode 'wardrobe', 'preview'
* @param itemtype 'face' o 'items'
* @return path immagine
*/

function get_correctimage( $char, $equippeditem, $mode, $itemtype = 'item' )
{
		
	
	$items_alternativesetpath = 'media/images/characters/wardrobe/' . $char -> id . '/' ;
	$items_defaultsetpath = 'media/images/items/wearable/';
	$aspect_alternativesetpath = 'media/images/characters/wardrobe/' . $char -> id . '/' ;
	$aspect_defaultsetpath = 'media/images/characters/aspect/';
	$image = 'notset';
	$customizaziondisabledstat = Character_Model::get_stat_d( $char -> id, 'disablecustomwardrobe' );
	$haswardrobebonus = is_array(Character_Model::get_premiumbonus($char -> id, 'wardrobe'));
	
	//kohana::log('debug', kohana::debug(Character_Model::get_premiumbonus($char -> id, 'wardrobe')));
	kohana::log('debug', "-> ----- Finding correct Image mode: {$mode} ----- <-");
	//kohana::log('debug', '-> Itemtype: ' . $itemtype );	
	//kohana::log('debug', '-> Character ' . $char -> id . ' has wardrobe bonus: <' . $haswardrobebonus . '>');
	//kohana::log('debug', '-> Character did disable customizations: <' . ( $customizaziondisabledstat -> value  . '>' ));
	
	// controllo se il char ha il bonus, se non ce l'ha
	// falldown a modo default.	
	
	if ( $mode == 'wardrobe' )
	{
		
		//var_dump($customizaziondisabledstat); exit;
		if ($haswardrobebonus == false or
			 ($customizaziondisabledstat -> loaded and $customizaziondisabledstat -> value == true )
		)
		{
			//kohana::log('debug', '-> Character does not have Wardrobe Bonus, falldown to DEFAULT MODE.');	
			$mode = 'default' ;
		}
	}
	
	if ( $mode == 'wardrobe' )
	{
		// items (weapons, clothes etc)
		
		if ( $itemtype == 'profile' )
		{
			$skincolorstat = $char -> get_stat( 'skincolorset' ); 
			if ( $skincolorstat -> loaded == false )
				$skincolorset = 'default';
			else
				$skincolorset = $skincolorstat -> stat1;
			$image = $aspect_defaultsetpath . "profile_{$char -> sex}_{$skincolorset}.png";
			
		}
		elseif ( $itemtype == 'item')
		{
			// troviamo lo slot selezionato dall' utente.
			
			$slotstat = Character_Model::get_stat_d( 
				$char -> id, 'wardrobeset', $equippeditem -> tag . '_' . $char -> sex );
			
						
			// se lo slot non esiste o è 0, carichiamo l' immagine di default.
			if ( !$slotstat -> loaded or $slotstat -> value == 0 )
			{
				// se il file è colorato, overrida la immagine.						
				if ( ! is_null( $equippeditem -> color) )
				{
					//kohana::log('debug', '-> Item is colored, trying to load the image.');
					$image = $items_defaultsetpath . $equippeditem -> parentcategory . '/colored/' . $equippeditem -> tag . '_' . $char -> sex . '_' . $equippeditem -> color . '.png';								
				}
				else
					$image = $items_defaultsetpath . $equippeditem -> parentcategory . '/' . $equippeditem -> tag . '_' . $char -> sex . '.png';
			}
			else
				$image = $items_alternativesetpath . $equippeditem -> parentcategory . '/approved/' . $equippeditem -> tag 
					. '_' . $char -> sex . '-' . $slotstat -> value . '.png';
			
		}		
		elseif ( $itemtype == 'hair' or $itemtype == 'face' or $itemtype == 'background')
		{				
			$image = $aspect_alternativesetpath . $itemtype . '/' .  $itemtype . '_' . $char -> sex . '.png';
			
			// get current slot, if stat is not set display the 
			// default item
			
			$slotstat = Character_model::get_stat_d( $char -> id, 'wardrobeset', $itemtype . '_' . $char -> sex );
			//kohana::log('debug', '-> item: ' . $itemtype . ', slot is : ' . $slotstat -> value );
			
			if ( !$slotstat -> loaded or $slotstat -> value == 0 )
				$image = $aspect_defaultsetpath . $itemtype . '_' . $char -> sex . '.png';
			else					
				$image = $aspect_alternativesetpath . 'aspect/approved/' . $itemtype . '_' . $char -> sex . '-' . $slotstat -> value . '.png';
			
			if ( !is_file ( DOCROOT . $image ))						
				$image = $aspect_defaultsetpath . $itemtype . '_' . $char -> sex . '.png';
		}
	}
	elseif ( $mode == 'default' )
	{
		
		if ( $itemtype == 'profile' )
			{
				$skincolorstat = $char -> get_stat( 'skincolorset' ); 
				if ( $skincolorstat -> loaded == false )
					$skincolorset = 'default';
				else
					$skincolorset = $skincolorstat -> stat1;
				$image = $aspect_defaultsetpath . "profile_{$char -> sex}_{$skincolorset}.png";
				
			}
			
		elseif ( $itemtype == 'item' )
		{
			// immagine di default
			$image = $items_defaultsetpath . $equippeditem -> parentcategory . '/' . $equippeditem -> tag. '_' . $char -> sex . '.png';
			
			
			
			// se il file è colorato, overrida la immagine.						
			if ( ! is_null( $equippeditem -> color) )
			{
				//kohana::log('debug', '-> Item is colored, trying to load the image.');
				$image = $items_defaultsetpath . $equippeditem -> parentcategory . '/colored/' . $equippeditem -> tag . '_' . $char -> sex . '_' . $equippeditem -> color . '.png';								
			}
			
			// se non esiste, falldown al default
			if ( !is_file ( DOCROOT . $image ))
			{
				//kohana::log('debug', '->  Falldown to default image.');
				$image = $items_defaultsetpath . $equippeditem -> parentcategory . '/' . $equippeditem -> tag. '_' . $char -> sex . '.png';
			}
		}		
		else
			$image = $aspect_defaultsetpath . $itemtype . '_' . $char -> sex . '.png';		
		
	
	}	
	elseif ( $mode == 'preview' )	
	{
		
		if ( $itemtype == 'profile' )
		{
			$skincolorstat = $char -> get_stat( 'skincolorset' ); 
			if ( $skincolorstat -> loaded == false )
				$skincolorset = 'default';
			else
				$skincolorset = $skincolorstat -> stat1;
			$image = $aspect_defaultsetpath . "profile_{$char -> sex}_{$skincolorset}.png";
			
		}		
		elseif ( $itemtype == 'item' )
		{
			
			// vediamo se c'è una immagine in attesa di approvazione (è nella dir. temp...)		
			kohana::log('debug', '-> Finding image in temp directory...' . $items_alternativesetpath . $equippeditem -> parentcategory . '/temp/' . 
				$equippeditem -> tag  . '_' . $char -> sex . '-*' . '.png');
			
			$i = glob($items_alternativesetpath . $equippeditem -> parentcategory . '/temp/' . 
				$equippeditem -> tag  . '_' . $char -> sex . '-*' . '.png' );
				
						
			if ( $i ) 
			{
				kohana::log('debug', '-> **** Items: Found an image in temp directory. ****');
				
				$image = $i[0];
				
				kohana::log('debug', "-> Image: {$image}");
			}
			else			
			{
				
				// troviamo lo slot selezionato dall' utente, se non è selezionato, prendiamo il
				// default item
			
				$slotstat = Character_Model::get_stat_d( $char -> id, 'wardrobeset', $equippeditem -> tag . '_' . $char -> sex );			
				//kohana::log('debug', '-> item: ' . $equippeditem -> tag . ', slot is : ' . $slotstat -> value );
			
				if ( !$slotstat -> loaded or $slotstat -> value == 0 ) {
					// se il file è colorato, overrida la immagine.						
					if ( ! is_null( $equippeditem -> color) )
					{
						//kohana::log('debug', '-> Item is colored, trying to load the image.');
						$image = $items_defaultsetpath . $equippeditem -> parentcategory . '/colored/' . $equippeditem -> tag . '_' . $char -> sex . '_' . $equippeditem -> color . '.png';								
					}
					else
						$image = $items_defaultsetpath . $equippeditem -> parentcategory . '/' . $equippeditem -> tag 
						. '_' . $char -> sex . '.png';
				}				
				else
				{
					
					$image = $items_alternativesetpath . $equippeditem -> parentcategory . '/approved/' . $equippeditem -> tag 
					. '_' . $char -> sex . '-' . $slotstat -> value . '.png';
					//kohana::log('debug', '-> Loading existing customized image:' .$image);
				}
				
			}
			
		}
		// aspect
		elseif ( $itemtype == 'hair' or $itemtype == 'face' or $itemtype == 'background'  )
		{				
			//var_dump($aspect_alternativesetpath . 'aspect/temp/' .  $itemtype . '_' . $char -> sex . '-*' . '.png');			

			// vediamo se c'è una immagine in attesa di approvazione (è nella dir. temp...)		

			$i = glob($aspect_alternativesetpath . 'aspect/temp/' .  $itemtype . '_' . $char -> sex . '-*' . '.png');	if ( $i ) 
			{
				kohana::lang('debug', '-> Found an image in temp directory.');
				$image = $i[0];
			}
			else
			{
			
				// get current slot, if stat is not set display the 
				// default item
			
				$slotstat = Character_model::get_stat_d( $char -> id, 'wardrobeset', $itemtype . '_' . $char -> sex );
				kohana::log('debug', '-> item: ' . $itemtype . ', slot is : ' . $slotstat -> value );
				
				if ( !$slotstat -> loaded or $slotstat -> value == 0 )
					$image = $aspect_defaultsetpath . $itemtype . '_' . $char -> sex . '.png';
				else {
					$image = $aspect_alternativesetpath . 'aspect/approved/' . $itemtype . '_' . $char -> sex . '-' . $slotstat -> value . '.png';
					kohana::log('debug', '-> Loading existing customized image:' .$image);
				}
				if ( !is_file ( DOCROOT . $image ))						
					$image = $aspect_defaultsetpath . $itemtype . '_' . $char -> sex . '.png';
			}
		}
		
	}	
		
	kohana::log('debug', '-> *** final image: ' . $image ); 
	kohana::log('debug', '-> ----- Loading Images ----- <-');
	
	return $image . '?rnd=' . time();
}

/**
* Elenca le immagini da approvare
* @param vettore file
* @return esito
*/

function listuploadedimages( $char )
{
	
	kohana::log('debug', '-> Listing uploaded images...');
	
	$path = DOCROOT . 'media/images/characters/wardrobe/' . $char -> id ;		
	$info = array( 
		'images' => 0, 
		'price' => 0, 
		'armors' => 0, 
		'clothes' => 0, 
		'weapons' => 0,
		'background' => 0,
		'face' => 0, 
		'hair' => 0 ); 
		
	$data = array(); 
	foreach ( array( 'armors', 'clothes', 'weapons', 'aspect') as $parentcategory )
	{
		$dir = $path . '/' . $parentcategory . '/temp';
		
		kohana::log('debug', 'Wardrobe -> Scanning dir: ' . $dir );
		
		if ( is_dir( $dir ))
		{
			$handle = opendir( $dir ); 
			while (($file = readdir($handle)) !== false)								
				if($file != "." and $file != ".." and strstr( $file, '.png' ) )			
				{				
					kohana::log('debug', 'Wardrobe -> Counting image: ' . $file );
					list($tag, $slot) = explode( '-', basename( $file, '.png' ) );
					kohana::log('debug', 'Wardrobe -> tag: ' . $tag );
					$data[substr($tag,0,strlen($tag)-2)][$slot] = $file ;
				}
			closedir($handle);
		}
		
	}
		
	return $data;
}

/**
* Elimina le immagini caricate
* @param char oggetto char
* @return none
*/

function removeuploadedimages( $char )
{
	
	$path = DOCROOT . 'media/images/characters/wardrobe/' . $char -> id ;		
	$info = array( 
		'images' => 0, 
		'price' => 0, 
		'armors' => 0, 
		'clothes' => 0, 
		'weapons' => 0,
		'background' => 0,
		'face' => 0, 
		'hair' => 0 ); 
		
	$data = array(); 
	foreach ( array( 'armors', 'clothes', 'weapons', 'aspect') as $parentcategory )
	{
		$dir = $path . '/' . $parentcategory . '/temp';
		kohana::log('debug', 'Wardrobe -> Scanning dir: ' . $dir );
		if ( is_dir( $dir ))
		{
			$handle = opendir( $dir ); 
			while (($file = readdir($handle)) !== false)								
				if($file != "." and $file != ".." and strstr( $file, '.png' ) )			
				{				
					kohana::log('debug', 'Wardrobe -> Removing image: ' . $dir . '/' . $file );
					unlink( $dir . '/' . $file  );
				}
			closedir($handle);
		}
		
	}
		
	return;
}

	/*
	* helper che stampa il submenu
	* @param selected tab selezionata
	* @return none
	*/

	public function get_horizontalmenu( $selected )
	{
		
		$character = Character_Model::get_info( Session::instance()->get('char_id') );
		if ( Character_Model::get_premiumbonus( $character -> id, 'wardrobe' ) !== false )
		{
			$lnkmenu = array(
				'/wardrobe/configureequipment/' => 
						array( 'name' => kohana::lang('wardrobe.configureequipment'),	'htmlparams' => array( 'class' => 
										( $selected == 'configureequipment' ) ? 'selected' : '' )),		
				'/wardrobe/atelier_dynamo/avatars/avatar' => 
						array( 'name' => kohana::lang('wardrobe.atelier_dynamo'),	'htmlparams' => array( 'class' => 
										( $selected == 'atelier_dynamo' ) ? 'selected' : '' )),
			);										
		}
		else
		{
			$lnkmenu = array(				
				'/wardrobe/atelier_dynamo/avatars/avatar' => 
						array( 'name' => kohana::lang('wardrobe.atelier_dynamo'),	'htmlparams' => array( 'class' => 
										( $selected == 'atelier_dynamo' ) ? 'selected' : '' )),
			);										
		}
		
		return $lnkmenu ;
	}

}
?>
