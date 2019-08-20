<?php defined('SYSPATH') OR die('No direct access allowed.');

class Market_Controller extends Template_Controller
{
	// Imposto il nome del template da usare
	public $template = 'template/gamelayout';
	
	
	/**
	* Gestione azione sell
	* @param int structure_id ID struttura
	* @param string $category categoria degli oggetti da visualizzare
	* @return none
	*/
	
	public function sell( $structure_id = null, $category = 'all' )
	{
	
		$view = new View ('/market/sell');
		$sheets  = array('gamelayout'=>'screen', 'submenu'=>'screen', 'character' => 'screen');
		$character = Character_Model::get_info( Session::instance()->get('char_id') );
		$subm    = new View ('template/submenu');

		if ( !$_POST )
		{			
			$structure = StructureFactory_Model::create( null, $structure_id );
			if ($structure -> loaded == false )
			{
				Session::set_flash('user_message', "<div class=\"error_msg\">". kohana::lang('global.operation_not_allowed') . "</div>");
				url::redirect('region/view/');		
			}
			$vat = Region_Model::get_tax( $structure -> region, 'valueaddedtax' );
			//var_dump($vat); exit;
		}		
		else
		{		
			$structure = StructureFactory_Model::create( null, $this -> input -> post('structure_id') );
			$vat = Region_Model::get_tax( $structure -> region, 'valueaddedtax' );			
			
			// creo un oggetto della classe di item scelta.
			$cfgitem = Item_Model::factory( $this -> input-> post( 'item_id' ), null );
			if ( is_null ( $cfgitem ) )
			{
				Session::set_flash('user_message', "<div class=\"error_msg\">". kohana::lang('global.operation_not_allowed') . "</div>");
				url::redirect('region/view/');		
			}
			
			$ca = Character_Action_Model::factory("marketsellitem");		
			$par[0] = $structure;
			$par[1] = $character; 
			$par[2] = $cfgitem -> find( $this -> input -> post( 'item_id') );
			$par[3] = $this -> input -> post( 'quantity' );			
			$par[4] = $this -> input -> post( 'sellingprice' );			
			$par[5] = $vat;
			$par[6] = $this -> input -> post( 'recipient'); 
			
			if ( $ca->do_action( $par,  $message ) )
				{Session::set_flash('user_message', "<div class=\"info_msg\">". $message . "</div>"); }	
			else	
				{ Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>"); }
		
			$view->structure = $par[0];		
		
		}
		
		$lnkmenu = $structure -> get_horizontalmenu( 'sell' );	
		$view -> charitems = Character_Model::inventory( $character -> id );	
		$view -> structure = $structure;
		$view -> valueaddedtax = $vat;
		$subm -> submenu = $lnkmenu;		
		$view -> currentcategory = $category;
		$view -> submenu = $subm;
		$view -> char_transportableweight = $character -> get_transportableweight() ; 
		$view -> ownedcoins = Character_Model::get_item_quantity_d( $character -> id, 'silvercoin' );
		$view -> character = $character;
		$this -> template -> content = $view ;
		$this -> template -> sheets = $sheets;
	}
	
	/**
	* Compra un item oppure lo ritira dal mercato.
	* @param none
	* @return none
	*/
	
	public function marketaction()
	{
		

		// creo un oggetto della classe di item scelta.
		
		$cfgitem = Item_Model::factory( $this -> input-> post( 'item_id' ), null );		
		if ( is_null ( $cfgitem ) )
		{
			Session::set_flash('user_message', "<div class=\"error_msg\">". kohana::lang('global.operation_not_allowed') . "</div>");
			url::redirect('region/view/');		
		}
		
		// buy item
		
		if  ( $this -> input -> post('buy') )
		{
			$ca = Character_Action_Model::factory("marketbuyitem");								
			
			$par[0] = ORM::factory("structure",  $this->input->post( 'structure_id' ) );
			$par[1] = Character_Model::get_info( Session::instance()->get('char_id') );
			$par[2] = $cfgitem -> find( $this -> input -> post( 'item_id') );
			$par[3] = $this -> input -> post( 'quantity' );			
		}
		
		// cancel sale
		
		if  ( $this -> input -> post('marketcancelsell') )
		{
			
			$ca = Character_Action_Model::factory("marketcancellsell");		
			
			$par[0] = ORM::factory("structure",  $this->input->post( 'structure_id' ) );
			$par[1] = Character_Model::get_info( Session::instance()->get('char_id') );
			$par[2] = $cfgitem ->find( $this->input->post( 'item_id') );
			$par[3] = $this->input->post( 'quantity' );			
		}
		
		if  ( $this -> input -> post('confiscate') )
		{
			 // Disabled
                        //Session::set_flash('user_message', "<div class=\"error_msg\">This function is temporary disabled.</div>");
                        ////url::redirect( 'market/buy/' . $this -> input -> post( 'structure_id' ));

			$ca = Character_Action_Model::factory("confiscateitem");		
			
			$char = Character_Model::get_info( Session::instance()->get('char_id') );
			$item = ORM::factory('item', $this -> input -> post('item_id'));
			$seller = ORM::factory('character', $item -> seller_id );			
			$par[0] = $char;
			$par[1] = $seller;
			$par[2] = $item;
			$par[3] = intval($this->input->post( 'quantity' ));			
			$par[4] = $this->input->post( 'confiscatereason' );	
		}		
		
		if ( $ca -> do_action( $par,  $message ) )
		 	{Session::set_flash('user_message', "<div class=\"info_msg\">". $message . "</div>"); }	
		else	
			{Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>"); }
		
		url::redirect( 'market/buy/' . $this -> input -> post( 'structure_id' ));
			
	}
		
	
	/**
	* Funzione che mostra gli item in vendita
	* @param int $structure_id ID struttura
	* @param string  $category categoria da visualizzare
	* @return none
	*/
	
	public function buy( $structure_id, $category = 'all' )	
	{
		
		$view = new View('market/buy');
		$sheets  = array('gamelayout'=>'screen', 'submenu'=>'screen', 'character' => 'screen');
		$subm    = new View ('template/submenu');		
		$result = null;
				
		$structure = StructureFactory_Model::create( 'market', $structure_id );
		
		$character = Character_Model::get_info( Session::instance()->get('char_id') );
		$role = $character -> get_current_role(); 		
		
		// controllo permessi		
		if ( ! $structure -> allowedaccess( $character, $structure -> getParentType(), $message, 'public' ) )
		{
			Session::set_flash('user_message', "<div class=\"error_msg\">". $message . "</div>");
			url::redirect('region/view/');
		}		
		$vat = Region_Model::get_appliable_tax( $structure -> region, 'valueaddedtax', $character );
		
		// Pulisce eventuali vendite private expired
		Item_Model::cleanupexpiredprivatesales();
		
		$message=null;
		$db = Database::instance();
		
		if ( $category == 'all' )
			$where = "parentcategory = parentcategory" ;
		else
			$where = "parentcategory = " . Database::instance() -> escape($category);
			
		$sql = 
			"select i.id item_id, ci.*, i.*, c.name seller_name
				from items i, cfgitems ci, characters c
				where i.cfgitem_id = ci.id 
				and   i.seller_id = c.id 
				and   i.equipped = 'unequipped' 
				and   i.structure_id = " . $structure -> id .  " and " .
				$where . "
				order by ci.tag asc, i.price asc, i.salepostdate asc ";
				
		$db = Database::instance();
		$items = $db -> query ( $sql );		
		$lnkmenu = $structure -> get_horizontalmenu( 'buy' );		
		
		$subm -> submenu = $lnkmenu;		
		$view -> submenu = $subm;
		$view -> role = $role; 
		$view -> structure = $structure;
		$view -> items = Structure_Model::inventory( $structure -> id, true );	
		$view -> valueaddedtax = $vat;
		$view -> character = $character;		
		$view -> char_transportableweight = $character -> get_transportableweight() ; 
		$view -> currentcategory = $category;		
		$this -> template->content = $view;
		$this -> template->sheets = $sheets;
		
	}
	
	/** 
	* Statistiche di prezzo per gli item
	* @param id id item
	* @return none
	*/
		
	
	public function stats_items( $structure_id = null, $id = null )
	{
		
		$view = new View( 'market/stats_items' ); 
		$sheets  = array('gamelayout'=>'screen', 'submenu'=>'screen' ); 
		$subm    = new View ('template/submenu');
		
		if ( request::is_ajax() )
		{
			kohana::log('debug', 'Received an ajax call.'); 
			kohana::log('debug', $this -> input -> post() ); 
			$this -> auto_render = false;
			
			$isadmin = Auth::instance()->logged_in('admin');
			$doubloons = ORM::factory('cfgitem') -> where ( 'tag', 'doubloon' ) -> find(); 
			$silvercoins = ORM::factory('cfgitem') -> where ( 'tag', 'silvercoin' ) -> find(); 

			
			if ( !$isadmin and 
			(
				$doubloons -> id == $this -> input -> post('id' ) 
				or 
				$silvercoins -> id == $this -> input -> post('id' ) 
			))
				$id = 1;
			else
				$id = $this -> input -> post( 'id' ); 
			
			$db = Database::instance();
			$data = $db -> query( "
			select ci.name, ci.marketsellable marketsellable, ci.structuresellable structuresellable, si.*
			from stats_items si, cfgitems ci
			where ci.id = si.cfgitem_id
			and   ci.id = " . $id . "
			and timestamp >= ( unix_timestamp() - ( 12 * 30 * 24 * 3600 ) ) order by timestamp asc" ) -> as_array(); 				
			
			$a['data'] = $data;
			$a['name'] = kohana::lang( $data[0] -> name ); 
			$a['sellable'] = ($data[0] -> marketsellable == true or $data[0] -> structuresellable ) ? kohana::lang('global.yes') : kohana::lang('global.no') ; 

			kohana::log( 'debug', kohana::debug( $a ) ); 
			
			echo json_encode( $a );
		}
		else
		{
		
			$structure = StructureFactory_Model::create( null, $structure_id );
			
			$items = ORM::factory('cfgitem') -> find_all();
			$isadmin = Auth::instance()->logged_in('admin');
				
			foreach ( $items as $item )
				if ( $isadmin == false and $item -> tag == 'doubloon' )
					;
				else
					$v_items[$item->id] = kohana::lang( $item->name ); 			
			
			asort( $v_items ); 
			
			$view -> items = $v_items;
			$lnkmenu = $structure -> get_horizontalmenu( 'stats_items' );
			$subm -> submenu = $lnkmenu;		
			$view -> submenu = $subm;
			$view -> structure = $structure;
			$this -> template -> sheets = $sheets;
			$this -> template -> content = $view;

		}
		

	}
	
	/**
	* carica info della struttura
	*/
	
	function info( $structure_id )
	{
		url::redirect( '/structure/info/' . $structure_id );
	}
	
}
