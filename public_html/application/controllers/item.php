<?php defined('SYSPATH') OR die('No direct access allowed.');

class Item_Controller extends Template_Controller
{
	// Imposto il nome del template da usare
	public $template = 'template/gamelayout';
	
	public function __construct()
	{
		parent::__construct();    
	}
	
	public function testadd( $tag )
	{
	
		$o = ORM::factory('character', Session::instance()->get('char_id'));
		$o->add_item( $tag, 1 );
		
		url::redirect( '/character/inventory' );
	
	}
	
	
	/*
	* Recupera Ferro
	* @param int $item_id ID Oggetto
	* @return none
	*/
	
	function recuperateiron( $item_id )
	{
		
		$char = Character_Model::get_info( Session::instance()->get('char_id') );
		$action = Character_Action_Model::factory("recuperateiron");
		
		$par[0] = $char;
		$par[1] = $item_id;	
		
		if ( $action -> do_action( $par,  $message ) )
			Session::set_flash('user_message', "<div class=\"info_msg\">". $message . "</div>");	
		else		
			Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");		
		
		url::redirect( request::referrer() );
		
	}
	
	
	/*
	* Prende un item dal terreno
	* @param int $itemid id Oggetto
	* @param str $n number of items to take
	*/
	
	public function takefromground( $item_id, $n = 1 )
	{
		
		$this -> auto_render = false;	
		$char = Character_Model::get_info( Session::instance()->get('char_id') );
		
		$ca = Character_Action_Model::factory("takefromground");
			
		$par[0] = $char;
		$par[1] = $item_id;
		$par[2] = $n;
		
		if ( $ca -> do_action( $par,  $message ) )			
			Session::set_flash('user_message', "<div class=\"info_msg\">". $message . "</div>");	
		else		
			Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");		
		
		url::redirect( '/region/view' );
	
	}
	
	/** 
	* Usa (mangia, beve ecc) un oggetto
	* @param: int $item_id ID dell'oggetto da mangiare
	* @param int $n numero di oggetti
	*/
	
	public function apply( $item_id, $n = 1 )
	{
		
		$message = '';				
		
		$char = Character_Model::get_info( Session::instance()->get('char_id') );
		$ca_apply = Character_Action_Model::factory("apply");
		
		$par[0] = $item_id;
		$par[1] = $char;
		$par[2] = $n;
		
		if ( $ca_apply -> do_action( $par,  $message ) )
			Session::set_flash('user_message', "<div class=\"info_msg\">". $message . "</div>");	
		else		
			Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");		
		
		url::redirect( request::referrer() );		
		
	}

	/* 
	* Indossa l'oggetto
	* @param int $item_id Id dell' oggetto
	* @return none
	*/
	
	public function wear( $item_id )
	{
		$message = "";		
		$char = Character_Model::get_info( Session::instance()->get('char_id') );
		
		$ca_wear = Character_Action_Model::factory("wear");		
		$par[0] = $item_id;
		$par[1] = $char;
		
		if ( $ca_wear->do_action( $par,  $message ) )
			Session::set_flash('user_message', "<div class=\"info_msg\">". $message . "</div>");	
		else		
			Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");		
		
		url::redirect( request::referrer() );			
		
	}


	/**
	* Azione undress, rimuove l'oggetto
	* @param: int $item_id dell'oggetto da rimuovere
	* @return none
	*/
	
	public function undress( $item_id )
	{
		
		$message = "";				
		$char = Character_Model::get_info( Session::instance()->get('char_id') );

		$ca_undress = Character_Action_Model::factory("undress");
		
		$par[0] = $item_id;
		$par[1] = $char -> id;
		
		if ( $ca_undress -> do_action( $par,  $message ) )
			Session::set_flash('user_message', "<div class=\"info_msg\">". $message . "</div>");	
		else		
			Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");		
		url::redirect( "character/inventory");			
	}
	
	/**
	* Funzione dedicata ad ADR per inviare dobloni.
	* @param int $recipient_id ID personaggio che riceve i dobloni
	* @return none
	*/
	
	function senddoubloons( $recipient_id = null )
	{
		$char = Character_Model::get_info( Session::instance()->get('char_id') );
		$view = new View( 'item/senddoubloons');
		$sheets  = array('gamelayout'=>'screen', 'submenu'=>'screen');		
				
		if ($_POST)
		{
						
			if (! Auth::instance() -> logged_in('doubloonreseller') )
			{
				Session::set_flash('user_message', 
					"<div class=\"error_msg\">". kohana::lang('global.operation_not_allowed') . "</div>");		
				url::redirect('region/view');				
			}
			
			$recipient = ORM::factory('character', $this -> input -> post('recipient_id')); 
				
			if ($recipient -> id == $char -> id )
			{
				Session::set_flash('user_message', 
					"<div class=\"error_msg\">". kohana::lang('ca_senditem.error-sourceandreceiverarethesame') 
						. "</div>");					
				url::redirect('item/senddoubloons/'. $recipient->id);
			}				
			
			// log purchase 
			
		    kohana::log('info', 'Saving payment data ...');
        
			$payment                     = new Electronicpayment_Model();
			$payment -> item_name        = 'ADR';
			$payment -> currency		 = 'USD';
			$payment -> quantity         = $this -> input -> post('quantity');
			$payment -> status           = 'valid';
			$payment -> grossamount      = 0;
			$payment -> netamount        = 0;
			$payment -> txn_id           = uniqid();
			$payment -> transaction_date = date("Y-m-d H:i:s", time());
			$payment -> user_id          = $recipient -> user_id;
			$payment -> save();			
			
			// give doubloons 
			
			$par[0] = $recipient;
			$par[1] = $this -> input -> post('quantity');
			$par[2] = 'purchase';
			$par[3] = '';		
			$par[4] = $char -> name;
			$par[5] = $char;
				
			$ca = Character_Action_Model::factory("givedoubloons");
				
			if ( $ca -> do_action( $par, $message ) )
			{ 								
				$char -> modify_doubloons( - $this -> input -> post('quantity'), 'doubloonsalefromadr', 'Doubloon Sale');
				Session::set_flash('user_message', "<div class=\"info_msg\">". $message . "</div>");
				url::redirect('item/senddoubloons/'.$recipient->id);
			}
			else
			{ 								
				Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");				
				url::redirect('item/senddoubloons/'.$recipient->id);
			}	
		
		}
		
		if (!is_null($recipient_id))
		{	
			$recipient = ORM::factory('character', $recipient_id );
			
		}
		
		$view -> recipient = $recipient;
		$view -> doubloons = Character_Model::get_item_quantity_d( $char -> id, 'doubloon' );
		$this -> template->content = $view;
		$this -> template->sheets = $sheets;
		
	}
	
	
	/** 
	* funzione che permette di specificare
	* gli oggetti da inviare e vedere il riepilogo
	* @param int $item_id ID oggetto da inviare	
	* @return none
	*/
	
	function send( $item_id = null )
	{
		
		$view = new View( 'item/send');
		$sheets  = array('gamelayout'=>'screen', 'submenu'=>'screen');		
		$subm    = new View ('template/submenu');
		$lnkmenu = array( 'character/inventory' => kohana::lang('global.returntoinventory') );
		$form = array ( 'quantity' => 1, 'to' => '');
		$char = Character_Model::get_info( Session::instance()->get('char_id') );
				
		if ( !$_POST )
		{
			$item = ORM::factory('item', $item_id );
			
			if ( !$item -> loaded )
			{ 								
				Session::set_flash('user_message', "<div class=\"error_msg\">". kohana::lang('global.error-itemnotfound') . "</div>");
				url::redirect('region/view/');
			}
			
			$form['quantity'] = $item -> quantity;
			
		}		
		else
		{
			$item = ORM::factory('item', $this -> input -> post('item_id'));
			$quantity = $this -> input -> post('quantity');
			$recipient = ORM::factory('character')
				-> where ( 'name', $this -> input -> post('to')) 
				-> find();
		
			$par[0] = $char;
			$par[1] = $recipient;
			$par[2] = $quantity;
			$par[3] = $item;
			$par[4] = 'send';
			
			$ca = Character_Action_Model::factory("senditem");		
			if ( $ca -> do_action( $par, $message ) )
			{ 								
				Session::set_flash('user_message', "<div class=\"info_msg\">". $message . "</div>");
				url::redirect('region/view/');
			}
			else
			{ 								
				Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
				$item = ORM::factory('item', $this -> input -> post ('item_id') );
				$form = arr::overwrite($form, $this -> input -> post());       
			}
				
		}		
		
		$view -> form = $form;
		$view -> item = $item;
		$subm -> submenu = $lnkmenu;		
		$view -> submenu = $subm;
		$this -> template->content = $view;
		$this -> template->sheets = $sheets;
		
	}
	
		/** 
		* Legge un item (scroll)
		* @param int $item_id ID oggetto
		* @return none
		*/
		
		function read( $item_id ) 
		{
		
			$char = Character_Model::get_info( Session::instance()->get('char_id') ); 
			$item = ORM::factory('item', $item_id ) ;			
			$subm    = new View ('template/submenu');
			$lnkmenu = array( 'character/inventory' => kohana::lang('global.returntoinventory') );
			$view = new View ( '/item/read_' . $item -> cfgitem -> tag );
			$sheets  = array('gamelayout'=>'screen', 'submenu'=>'screen'); 
						
			if ( ! $item -> loaded or $item -> cfgitem -> category != 'scroll'  ) 
			{
				Session::set_flash('user_message', "<div class=\"info_msg\">". kohana::lang('global.operation_not_allowed') . "</div>");
				url::redirect ( $this -> input -> server( 'HTTP_REFERER' ) ); 
			}
						
			
			// istanzia la classe corretta tramite la factory
			$s = Item_Model::factory( 
				null, $item -> cfgitem -> tag ) -> find ( $item_id ); 			
			$bodycontent = $s -> expandcontent ( );

			//var_dump( $bodycontent ); exit; 
			
			$subm -> submenu = $lnkmenu;
			$view -> submenu = $subm;
			$view -> bodycontent = $bodycontent; 
			$view -> item = $item; 
			$this -> template -> content = $view;
			$this -> template -> sheets = $sheets;
		
		}

		/** 
		* Scrive e sigilla una pergamena
		* @param int $item_id ID oggetto
		* @return none
		*/
		
		function write( $item_id = null ) 
		{			
			
			$subm    = new View ('template/submenu');
			$lnkmenu = array( 'character/inventory' => kohana::lang('global.returntoinventory') );
			$view    = new View ( '/item/write_generic_scroll' );
			$sheets  = array('gamelayout'=>'screen', 'submenu'=>'screen'); 
			$form    = array('subject' => '', 'body' => '');
			$errors = $form;
			$char = Character_Model::get_info( Session::instance()->get('char_id') ); 
			
			if ( !$_POST )
			{
				
				$paper_piece = ORM::factory('item', $item_id);								
						
				if ( 
					Character_Model::has_item( $char -> id, $paper_piece -> cfgitem -> tag, 1 ) == false )
				{
					Session::set_flash('user_message', "<div class=\"error_msg\">". 
						kohana::lang('charactions.item_notininventory') . "</div>");
					url::redirect ( request::referrer() ); 			
				}
							
				// Controllo il char possieda almeno 5 punti di intelligenza
				
				if ( $char -> intel < 5 ) 
				{
					Session::set_flash('user_message', "<div class=\"error_msg\">". 
						kohana::lang('message.not_enough_int') . "</div>");
					url::redirect ( request::referrer() ); 
				}
				
				// Controllo che il container possieda un sigillo di ceralacca
				
				if ( ! Character_Model::has_item( $char -> id, 'waxseal', 1 ) ) 
				{
					Session::set_flash('user_message', "<div class=\"error_msg\">". kohana::lang('message.missing_waxseal') . "</div>");
					url::redirect ( request::referrer() ); 
				}
				
			}
			else
			{
				
				$paper_piece = ORM::factory('item', $this -> input -> post('item_id'));
				$post = Validation::factory($_POST)				
					->add_rules('subject', 'required', 'length[1,255]')
					->add_rules('body', 'required');								
				
				if ( $post -> validate() )
				{
					
					// Creo l'oggetto scroll_generic
					// param1 = titolo del documento
					// param2 = testo del documento
					// param3 = firma del documento
					
					$o = Item_Model::factory( null, 'scroll_generic' );
					$o -> param1 = htmlspecialchars($post -> subject, ENT_QUOTES);
					$o -> param3 = $post -> body;
					$o -> param2 = $char -> signature;
					$o -> character_id = $char -> id;
					$o -> save();
					
					// Rimuovo l'oggetto di carta					
					
					$paper_piece -> removeitem('character', $char -> id, 1);
					
					// Cero e rimuovo un sigillo
					
					$waxseal = Item_Model::factory( null, 'waxseal');		
					$waxseal -> removeitem( 'character', $char -> id, 1 );
									
					// Torno all'inventario del char
					
					Session::set_flash('user_message', "<div class=\"info_msg\">". kohana::lang('charactions.info-scrollwritten') . "</div>");
					
					url::redirect( request::referrer() );
				
				}
				else
				{      					
					
					$errors = $post->errors('form_errors');                             
					$view -> bind('errors', $errors);
					$form = arr::overwrite($form, $post->as_array());      
				}
			}
					
			$view -> form = $form;
			$subm -> submenu = $lnkmenu;
			$view -> submenu = $subm;
			$view -> item = $paper_piece;
			$this -> template -> content = $view;
			$this -> template -> sheets = $sheets;
			
		}


	/* 
	* Funzione che permette di esibire un oggetto (scroll)
	* ad un altro char presente nello stesso nodo
	* @param: int $item_id ID oggetto da inviare
	* @return: none
	*/
	
	function exhibit( $item_id = null ) 
	{
		$view    = new View('item/exhibit');
		$sheets  = array('gamelayout'=>'screen', 'submenu'=>'screen');
		$subm    = new View ('template/submenu');
		$lnkmenu = array( 'character/inventory' => kohana::lang('global.back') );
		$subm -> submenu = $lnkmenu;
		
		$form = array ('to' => '', 'to_id' => '' );

		$item = ORM::factory('item', $item_id );		
		
		if ( !$_POST )
		{
			$view -> form = $form;
			$view -> item = $item;
			$view -> submenu = $subm;
			$this -> template->content = $view;
			$this -> template->sheets = $sheets;
			return;
		}
		else
		{			
			
			$post = Validation::factory($this->input->post())
				->pre_filter('trim', TRUE)
				->add_rules('to','required');				
			
			if ($post->validate() )
			{	
				$targetchar = ORM::factory('character')
					-> where (
						array('name' => $this->input->post('to'))						
					)
					-> find()	;				
				
				$sender = Character_Model::get_info( Session::instance()->get('char_id') );
				
				if ( !$targetchar->loaded )
				{
					Session::set_flash('user_message', "<div class=\"error_msg\">". kohana::lang('global.error-characterunknown') ."</div>");
					url::redirect ( 'item/exhibit/' . $item->id );
					return;
				}
				
				if ( $item->cfgitem->tag != 'scroll_generic' )
				{
					Session::set_flash('user_message', "<div class=\"error_msg\">". kohana::lang('charactions.only_generic_scroll') ."</div>");
					url::redirect ( 'character/inventory' );
					return;
				}
								
				// Invia evento notifica al ricevente
				
				Character_Event_Model::addrecord( 
				$targetchar->id, 
				'normal', 
				'__events.exhibit_scroll'.
				';'.$sender->name.
				';'.$item->param1.
				';'.$item->param2.
				';'.$item->param3,
				'normal'
				);

				// Invia evento notifica al ricevente
				
				Character_Event_Model::addrecord( 
				$sender->id, 
				'normal', 
				'__events.exhibit_scroll_sender'.
				';'.$targetchar->name.
				';'.$item->param1.
				';'.$item->param2.
				';'.$item->param3,
				'normal'
				);
				
				Session::set_flash('user_message', "<div class=\"info_msg\">". kohana::lang('charactions.info-scrollshown') ."</div>");				
				url::redirect ( 'character/inventory' );
				
				return;				
			}
			else
			{				
				$errors = $post->errors('form_errors'); 
				$view->bind('errors', $errors);
				//ripopolo la form  
				$form = arr::overwrite($form, $post->as_array());				
				$view->form = $form;						
				$view->item = $item;
				$view -> submenu = $subm;
				$this->template->content = $view;
				$this->template->sheets = $sheets;
			};
			
			
			
		}
	}	

		/** 
		* Tinteggia l'item
		* @param int $item_id ID Item
		* @return none
		*/
		
		function tint( $item_id ) 
		{
			
			$char = Character_Model::get_info( Session::instance()->get('char_id') ); 
			$item = ORM::factory('item', $item_id );
			
			if ( $item -> loaded == false )
			{
				Session::set_flash('user_message', "<div class=\"error_msg\">". kohana::lang('global.error-itemnotfound') . "</div>");
				url::redirect ( $this -> input -> server( 'HTTP_REFERER' ) ); 				
			}
			
			// Controllo subito che il char possieda una dye bowl
			
			if ( ! Character_Model::has_item( $char -> id, 'dyebowl', 1) ) 
			{
				Session::set_flash('user_message', "<div class=\"error_msg\">". kohana::lang('charactions.missing_dyebowl') . "</div>");
				url::redirect ( $this -> input -> server( 'HTTP_REFERER' ) ); 
			}		
			
			$subm    = new View ('template/submenu');
			$lnkmenu = array( 'character/inventory' => kohana::lang('global.returntoinventory') );
			$view    = new View ( '/item/tint' );
			$sheets  = array('gamelayout'=>'screen', 'submenu'=>'screen'); 

			if ( $_POST )
			{ 
				
				
				
				// path to png image
				if ( !is_null( $item -> cfgitem -> subcategory ) )
				{ 
					$file = './media/images/items/wearable/clothes/'.$item->cfgitem->tag.'_'.$item -> cfgitem -> subcategory.'.png'; 
				}
				else
				{ 
					$file = './media/images/items/wearable/clothes/'.$item->cfgitem->tag.'_'.$char->sex.'.png'; 
				}
																
				$img = imagecreatefrompng($file); // open image
				imagealphablending($img, true); // setting alpha blending on
				imagesavealpha($img, true);

				$r = $_POST['red'];
				$b = $_POST['blue'];
				$g = $_POST['green'];

				$col = array($r/255,$b/255,$g/255);
				$height = imagesy($img);
				$width = imagesx($img);

				for($x=0; $x<$width; $x++)
				{
					for($y=0; $y<$height; $y++)
					{
						$rgb = ImageColorAt($img, $x, $y);						
						$colors = imagecolorsforindex($img, $rgb);						 
						//print 'x: ' . $x . 'y: ' . $y ; print_r($colors); exit();					
						if (
							$colors['red'] = '255' and 
							$colors['green'] = '255' and 
							$colors['blue'] = '255' and 							
							$colors['alpha'] == 127)
							;
							//echo ' skipping pixel: x:' . $x . 'y: ' . $y . '</br>' ;
						else
						{
							//echo '--> shifting pixel: x:' . $x . 'y: ' . $y . '</br>' ;
							$r = ($rgb >> 16) & 0xFF;
							$g = ($rgb >> 8) & 0xFF;
							$b = $rgb & 0xFF;
						
							$newR = $r*$col[0];
							$newG = $g*$col[2];
							$newB = $b*$col[1];
							imagesetpixel($img, $x, $y,imagecolorallocate($img, $newR, $newG, $newB));
						}
					}
				}

				// File di destinazione
				if ( !is_null( $item -> cfgitem -> subcategory ) )
				{
					$dest = './media/images/items/wearable/clothes/colored/'.$item->cfgitem->tag.'_'.$item -> cfgitem -> subcategory .'_'. $_POST['red'] .'_'. $_POST['blue'] .'_'. $_POST['green'] .'.png'; 
					If ( ! file_exists($dest) ) { imagepng($img, $dest, 0, null); }
				}
				else
				{
					$dest1 = './media/images/items/wearable/clothes/colored/'.$item->cfgitem->tag . '_' . $char -> sex . '_' . $_POST['red'] .'_'. $_POST['blue'] .'_'. $_POST['green'] .'.png'; 
					imagepng($img, $dest1, 0, null);					
				}

				// Libero la memoria allocata
				
				imagedestroy($img);
    
				// Aggiorno le informazioni sull'oggetto (colore)
				$item -> color = $_POST['red'] .'_'. $_POST['blue'] .'_'. $_POST['green'];
				$item -> hexcolor = $_POST['hexcolor'];
				$item -> save();
				
				// Rimuovo la tinozza del colore
				
				$dyebowl = Item_Model::factory( null, 'dyebowl' ); 
				$dyebowl -> removeitem('character', $char -> id, 1 );				

				// Torno all'inventario del char
				
				Session::set_flash('user_message', "<div class=\"info_msg\">". kohana::lang('charactions.dye_ok') . "</div>");
				url::redirect( '/character/inventory');
			}
			else	
				$item = ORM::factory('item', $item_id );			
			
			$subm -> submenu = $lnkmenu;
			$view -> submenu = $subm;
			$view -> item = $item;
			$this -> template -> content = $view;
			$this -> template -> sheets = $sheets;
		}	
		
	/**
	* Riposa nel cart
	* @param item_id ID item
	* @return none
	*/
	
	public function rest( $item_id )
	{
		$character = Character_Model::get_info( Session::instance()->get('char_id') );
		$item = ORM::factory('item', $item_id );
		if ( $item -> cfgitem -> tag != 'cart_3' )
		{
			Session::set_flash('user_message', "<div class=\"error_msg\">". kohana::lang('global.operation_not_allowed') . "</div>");				
			url::redirect ( 'character/inventory' );
		}
		
		$par[0] = $character;
		$par[1] = NULL;
		$par[2] = true;
		
		$ca = Character_Action_Model::factory("rest");		

		if ( $ca -> do_action( $par,  $message ) )
		{ 				
			Session::set_flash('user_message', "<div class=\"info_msg\">". kohana::lang($message) . "</div>");
			url::redirect ( 'character/inventory' );
		}	
		else	
		{ 
			Session::set_flash('user_message', "<div class=\"error_msg\">". kohana::lang($message) . "</div>");	
			url::redirect ( 'character/inventory' );
		}
		
	
	}
	
	/**
	* Ritorna un item prestato
	* @param int $item_id ID Item
	* @return none
	**/
	
	function returnlentitem( $item_id )
	{
	
		$character = Character_Model::get_info( Session::instance()->get('char_id') );
		$item = ORM::factory('item', $item_id );
		
		$par[0] = $character;
		$par[1] = $item;
		
		$ca = Character_Action_Model::factory("returnlentitem");		

		if ( $ca->do_action( $par,  $message ) )
		{ 				
			Session::set_flash('user_message', "<div class=\"info_msg\">". $message . "</div>");				
			url::redirect ( 'character/inventory' );
		}	
		else	
		{ 
			Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");	
			url::redirect ( 'character/inventory' );
		}
		
	}
	
	/**
	* Funzione che computa il tempo ed il costo di un invio
	* @param none
	* @return none
	*/
	
	function computesenddata()
	{
		
		$info = array ( 
			'cost' => 10, 
			'time' => '', 
			'rc' => 'ok', 
			'message' => null
		);		
		
		kohana::log('debug', kohana::debug($_POST)); 
		
		$this -> auto_render = false;
		
		$character = Character_Model::get_info( Session::instance()->get('char_id') );
		
		$info = Item_Model::computesenddata( 
			$this -> input -> post('quantity'),
			$this -> input -> post('item_id'),
			$character,
			$this -> input -> post('target'),
			'send'			
		);
				
		$info['time'] = Utility_Model::secs2hmstostring( $info['time'], 'hours' );
		
		echo json_encode( $info );
	}
	
	/*
	* Scassina un contenitore
	* @param: int   $item_id   dell'oggetto da scassinare
	public function unlock($item_id)
	{
		$message = "";		
		$char = Character_Model::get_info( Session::instance()->get('char_id') );
		
		$ca_unlock = Character_Action_Model::factory("unlockcontainer");		
		
		$par[0] = $item_id;
		$par[1] = $char;
		
		if ( $ca_unlock->do_action( $par,  $message ) )
			Session::set_flash('user_message', "<div class=\"info_msg\">". $message . "</div>");	
		else		
			Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
		
		url::redirect( "character/inventory");			
	}
	*/
	
	/*
	* Apre un contenitore
	* @param: int   $item_id   dell'oggetto da scassinare
	*/
	public function open($item_id)
	{
		$message = "";		
		$char = Character_Model::get_info( Session::instance()->get('char_id') );
		
		$ca_open = Character_Action_Model::factory("opencontainer");		
		
		$par[0] = $item_id;
		$par[1] = $char;
		
		if ( $ca_open->do_action( $par,  $message ) )
			Session::set_flash('user_message', "<div class=\"info_msg\">". $message . "</div>");	
		else		
			Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
		
		url::redirect( "character/inventory");			
	}
}
