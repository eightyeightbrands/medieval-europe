	<?php defined('SYSPATH') OR die('No direct access allowed.');

class Wardrobe_Controller extends Template_Controller
{	
	// Imposto il nome del template da usare
	
	public $template = 'template/gamelayout';
		
	/*
	* Displays a page where the player can choose 
	* which custom item wants to wear.
	* @param none
	* @return none
	*/

	public function configureequipment()
	{
		$char = Character_Model::get_info( Session::instance()->get('char_id') );		
		$view    = new View ('wardrobe/configureequipment');		
		$subm    = new View ('template/submenu');
		$path = DOCROOT . 'media/images/characters/wardrobe/' . $char -> id ;
		$sheets  = array('gamelayout' => 'screen','character'=>'screen', 'pagination'=>'screen', 'submenu'=>'screen');		
		
		$pendingapprovalrequest = Wardrobe_Model::listpendingapprovalrequest( $char );		
		$uploadedimages = Wardrobe_Model::listuploadedimages( $char );	

		if ( Character_Model::get_premiumbonus( $char -> id, 'wardrobe' ) == false )
		{
			Session::set_flash('user_message', "<div class=\"error_msg\">". kohana::lang('global.operation_not_allowed') . "</div>");
			url::redirect('/wardrobe/atelier_dynamo/avatars/avatar');		
		}
		
		if ( !$_POST )
			$tab = 'clothes';
		else
		{	
		
			//var_dump($_POST); 
			
			///////////////////////////////////
			// upload
			///////////////////////////////////
			
			if ( key_exists('upload', $this -> input -> post()) )
			{
				//var_dump($_FILES);exit;
				$path = DOCROOT . 'media/images/characters/wardrobe/' . $char -> id ;
				$errs = array();
				foreach( $_FILES as $key => $value)
				{	
					
					$file = null;
					$file [$key] = $value ;
					if ( $file[$key]['name'] != '' )
					{
						
						list($tag, $slot, $category) = explode( '-', $key);
						kohana::log('debug', 'Wardrobe -> tag: ' . $tag );
						kohana::log('debug', 'Wardrobe -> slot: ' . $slot );
						kohana::log('debug', 'Wardrobe -> category: ' . $category );
						
						$image = Validation::factory( $file )
							-> add_rules($key, 'upload::valid', 'upload::type[png]', 'upload::size[512K]');
						
						if ( $image -> validate() )
						{				

							if ( in_array( $key, array ( 'face', 'hair' ) ))
								$completepath = $path . '/' . $tag . '/temp/aspect/';
							else
							{
								$cfgitem = ORM::factory('cfgitem') -> where ( 'tag', $tag ) -> find();							
								$completepath = $path . '/' . $category . '/temp';
							}
							
							// se non esiste la directory temp, creala
							if ( !is_dir( $completepath ) )
								mkdir ( $completepath, 0755, true );	
							
							$filename = upload::save( $value, $tag . '_' . $char -> sex . '-' . $slot  . '.png', $completepath );		
							

						}
						else
						{	
							$errs[] = $image -> errors('form_errors');  							
						}
					}
				}
				
				//kohana::log('debug', kohana::debug($errs) ); 
				
				if ( count( $errs ) > 0 )
				{
					foreach ( $errs as $err )
						$errors[key($err)] =  kohana::lang('form_errors.wardrobe_parts.default');
				
				
				}
				
				//kohana::log('debug', kohana::debug($errors) ); 
				
				if ( count( $errs ) > 0 )
					Session::set_flash('user_message', "<div class=\"error_msg\">". kohana::lang('wardrobe.imagesloadederror') . "</div>");
				else
					Session::set_flash('user_message', "<div class=\"info_msg\">". kohana::lang('wardrobe.imagesloaded') . "</div>");				
			}			
			
			///////////////////////////////////
			// Send image for approval
			///////////////////////////////////
			
			elseif ( key_exists('approval', $this -> input -> post() ))			
			{
			
				if ( count($uploadedimages) == 0 )
					Session::set_flash('user_message', "<div class=\"error_msg\">". kohana::lang('wardrobe.nothingtoapprove') . "</div>");			
				elseif ( Wardrobe_Approvalrequest_Model::add( $char, $message ) == false )
					Session::set_flash('user_message', "<div class=\"error_msg\">". kohana::lang($message) . "</div>");					
				else
					Session::set_flash('user_message', "<div class=\"info_msg\">". kohana::lang($message) . "</div>");					
			}
			
			///////////////////////////////////
			// Colore della pelle
			///////////////////////////////////
			
			elseif ( key_exists('setskincolor', $this -> input -> post()) )
			{
				//var_dump('hello');exit;
				$char -> modify_stat( 
					'skincolorset', 
					null, 
					null,
					null, 
					true,
					$this -> input -> post('skincolorset')
				);
				
				Session::set_flash('user_message', "<div class=\"info_msg\">". kohana::lang('wardrobe.info-skincolorset') . "</div>");
			}
			
			
			///////////////////////////////////
			// switcha on/off le customizzazioni
			///////////////////////////////////
			
			elseif ( key_exists('disablecustomization', $this -> input -> post()) )
			{				
				
				$char -> modify_stat( 
					'disablecustomwardrobe', 
					$this -> input -> post('disablewardrobecustomization'), 
					null,
					null, 
					true
				);

				$char -> modify_stat( 
					'hideringunderclothes', 
					$this -> input -> post('hideringunderclothes'), 
					null,
					null, 
					true
				);
				
				$char -> modify_stat( 
					'hidehairsunderclothes', 
					$this -> input -> post('hidehairsunderclothes'), 
					null,
					null, 
					true
				);
				
				Session::set_flash('user_message', "<div class=\"info_msg\">". kohana::lang('wardrobe.info-settingschanged') . "</div>");
				
			}
			
			///////////////////////////////////
			// reset uploaded
			///////////////////////////////////
			
			elseif ( key_exists('reset', $this -> input -> post()) )
			{			
				// Non è possibile pulire le immagini se esiste una 
				// richiesta da approvare
				
				$c = ORM::factory('wardrobe_approvalrequest') -> where (
				array( 
				'character_id' => $char -> id,
				'status' => 'new' )) -> count_all();
				
				if ( $c > 0 )
				{
					Session::set_flash('user_message', "<div class=\"error_msg\">". kohana::lang('wardrobe.error-unprocessedrequestexists') . "</div>");
				}
				else
				{
					Wardrobe_Model::removeuploadedimages( $char );	
					Session::set_flash('user_message', "<div class=\"info_msg\">". kohana::lang('wardrobe.cleanupok') . "</div>");
				}
				
				
				
			}
				
		}
		// refresh uploaded images data
		$uploadedimages = Wardrobe_Model::listuploadedimages( $char );	
		$lnkmenu = Wardrobe_Model::get_horizontalmenu('configureequipment');				
		
		$items = ORM::factory('cfgitem') -> 
			in( 'parentcategory', array( 
				'armors', 
				'weapons', 
				'clothes' )) -> find_all();
		
		
		$s = Character_Model::get_stat_d( $char -> id, 'disablecustomwardrobe' );
		if ( !$s -> loaded or $s -> value == false )
			$disablewardrobecustomization = false;
		else
			$disablewardrobecustomization = true;
		
		$s = Character_Model::get_stat_d( $char -> id, 'hideringunderclothes' );
		if ( !$s -> loaded or $s -> value == false )
			$hideringunderclothes = false;
		else
			$hideringunderclothes = true;
		
		$s = Character_Model::get_stat_d( $char -> id, 'hidehairsunderclothes' );
		if ( !$s -> loaded or $s -> value == false )
			$hidehairsunderclothes = false;
		else
			$hidehairsunderclothes = true;
		
		
		$s = Character_Model::get_stat_d( $char -> id, 'skincolorset' );
		if ( !$s -> loaded)
			$skincolorset = 'default' ;
		else
			$skincolorset = $s -> stat1;
		
		$view -> disablewardrobecustomization = $disablewardrobecustomization;
		$view -> hideringunderclothes = $hideringunderclothes;
		$view -> hidehairsunderclothes = $hidehairsunderclothes;
		$view -> pendingapprovalrequest = $pendingapprovalrequest;		
		$view -> uploadedimages = $uploadedimages;
		$view -> equippeditems = $equippeditems = Character_Model::get_equipment( $char -> id );
		$view -> skincolorset = $skincolorset;
		$view -> items = $items;
		$view -> submenu = $subm;
		$subm -> submenu = $lnkmenu;
		$view -> char = $char;
		$this -> template -> content = $view;
		$this -> template -> sheets = $sheets;

	}
	
	/*
	* Visualizza alcuni template per il disegno	
	* @param none
	* @return none
	*/
	
	public function atelier_default()
	{
		
		$char = Character_Model::get_info( Session::instance()->get('char_id') );		
		$view    = new View ('wardrobe/atelier_default');		
		$subm    = new View ('template/submenu');
		
		$sheets  = array(
			'gamelayout' => 'screen',			
			'pagination'=>'screen', 
			'submenu'=>'screen');		
			
		if ( Character_Model::get_premiumbonus( $char -> id, 'wardrobe') == false )			
		{	
		 
			Session::set_flash('user_message', "<div class=\"error_msg\">". kohana::lang('global.operationnotallowed') . "</div>");
			url::redirect( 'character/inventory' );
		}
			
		$lnkmenu = Wardrobe_Model::get_horizontalmenu('atelier_default');		
		$items = ORM::factory('cfgitem') -> 
			in( 'parentcategory', array( 'armors', 'weapons', 'clothes' )) -> find_all();
		
		$view -> items = $items;
		$view -> submenu = $subm;
		$subm -> submenu = $lnkmenu;
		$view -> char = $char;
		$this -> template -> content = $view;
		$this -> template -> sheets = $sheets;
		
	}
	
	/**
	* Visualizza una lista degli avatar disponibili all'acquisto
	* @param none
	* @return none
	*/
	
	function atelier_dynamo()
	{
		$subm = new View ('template/submenu');
		$view = new view( 'wardrobe/atelier_dynamo');
		
		$sheets  = array(
			'gamelayout' => 'screen',
			'character'=>'screen', 
			'pagination'=>'screen', 
			'submenu'=>'screen');		
				
		$char   = Character_Model::get_info( Session::instance() -> get('char_id') );			
		$lnkmenu = Wardrobe_Model::get_horizontalmenu('atelier_dynamo');
		
		// load files 
		
		$view -> sex = strtolower($char -> sex);
		$view -> basedirectory = "media/images/wardrobe/atelier/dynamo";
		$subm -> submenu = $lnkmenu;
		$view -> submenu = $subm;
		$this -> template -> content = $view;
		$this -> template -> sheets = $sheets;
	}
	
	/**
	* Set the slot for a wardrobe item
	* @param string tag of item type (hair, face...)
	* @param int slot slot to be set
	* @return none
	*/
	
	function selectslot( $tag, $slot )
	{
	
		$char = Character_Model::get_info( Session::instance()->get('char_id') );		
		
		if ( $slot < 0 or $slot > 5 )
			Session::set_flash('user_message', "<div class=\"error_msg\">" . 
				kohana::lang('wardrobe.error-incorrectslot') . "</div>");				
		else
			{
			
				$stat = Character_Model::get_stat_d( $char -> id, 
					'wardrobeset',
					$tag . '_' . $char -> sex,
					null
				);
				
				$char -> modify_stat( 
					'wardrobeset', 
					$slot, 
					$tag . '_' . $char -> sex, 
					null, 
					true,
					$stat -> stat1,
					$stat -> stat2
				);		
								
				
				Session::set_flash('user_message', "<div class=\"info_msg\">" . 
					kohana::lang('wardrobe.slotset-ok', $slot, kohana::lang(
						'items.' . $tag . '_name') ) . "</div>");				
			}
		
		url::redirect( 'wardrobe/configureequipment' );

	}
	
	
}
