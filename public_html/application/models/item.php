<?php defined('SYSPATH') OR die('No direct access allowed.');

class Item_Model extends ORM
{
	protected $belongs_to = array('cfgitem', 'character', 'structure', 'region');
	protected $table_name = 'items';
	protected $sorting = array('cfgitem_id' => 'asc', 'quantity' => 'asc' );	
	public $actions = null; // azioni permesse sull item
	public $weight; // per settare il corretto peso (quantity*peso del cfgitem)
	
	const SENDCOST_X_NUMBER = 0.4;
	
	/*
	* Pulisce le vendite private scadute
	* @param none
	* @return none
	*/
	
	public function cleanupexpiredprivatesales()
	{
		
		$privatesales = Database::instance() -> query ("
			SELECT *
			FROM items
			WHERE recipient_id is not null 
			AND   structure_id is not null
			AND   salepostdate < ( unix_timestamp() - (2*24*3600))");
			
		foreach ( $privatesales as $privatesale )
		{
			
			$recipient = ORM::factory('character', $privatesale -> recipient_id );
			$item = ORM::factory('item', $privatesale -> id );
			$structure = ORM::factory('structure', $item -> structure_id );
			
			Character_Event_Model::addrecord( 
				$item -> seller_id,
				'normal',
				'__events.privatesaleexpired' . 
				';' . $item -> quantity . 
				';__items.' . $item -> cfgitem -> tag . '_name' .				
				';' . $recipient -> name . 
				';__' . $structure -> region -> name );
				
			$item -> recipient_id = null;
			$item -> save();
			
		}
		
	}
	
	// istanzia la corretta categoria di oggetti
	// @input: $item_id: id dell' item 
	// @input: $tag: type dell' item (cfgitems.tab)
	// ritorna la classe corretta o null;
		
	public static function factory( $item_id = null, $tag = null )
	{
		
		if ( is_null($item_id) )
		{
			$o = ORM::factory('cfgitem')->where( 'tag' , $tag )->find();
			if ($o->loaded)
			{
				$model = ("IT_".ucfirst( $o -> category ) . "_Model");				
				$s = new $model;
				$s->cfgitem_id = $o->id;
				$s->quality=100;				
			}
			else
				return null;
		}
		else
		{		

			$o = ORM::factory('item', $item_id);
			if ($o -> loaded == false )
				return null;
			//print kohana::debug( $o->cfgitem);
			$model = ("IT_".ucfirst( $o->cfgitem->category ) . "_Model");			
			$s = new $model;
			$s->cfgitem_id = $o->cfgitem->id;
			
			// copio dall' item gli attributi locali dell' istanza
			
			$s->quality = $o->quality;
			$s->param1 = $o->param1;
			$s->param2 = $o->param2;
		}
		
		return $s;	
	}
		
	/**
	* Crea un tooltip per l' oggetto passato
	* @param array $item Array item costruito ad-hoc
	* @param string $page pagina in cui è chiamato il tooltip
	* @return string $html codice HTML del tooltip
	*/
	
	function helper_tooltip( $item, $page = null )
	{		
		
		$title = "<h3>" . kohana::lang($item -> name) . "</h3>";
		$title .= "<hr/>";
		$title .=  '<table>';			
		
		$title .= '<tr><td colspan="2">' . 
			html::image( array(
			'src' => 'media/images/items/'.$item -> tag . '.png', 
			'align' => 'left', 
			'class' => 'size80',			
			'style' => 'margin-right:5px;background-color: #645336;border:1px solid #999') ) .
			'<p>' . Kohana::lang($item->description) . '</p>' . '</td></tr>';
		
		$title .= '<tr><td style="left" width="100px">' . kohana::lang('items.condition'). '</td><td>' . Utility_Model::number_format($item -> quality,2) . '%</td></tr>';				
		
		///////////////////////////////////////
		// dati comuni a tutte le pagine
		///////////////////////////////////////
		
		// scroll
		
		if ( $item -> tag == 'scroll_propertylicense' )
		{
			
			list( $propertyid, $timestamp, $structuretype ) = explode(  ';', $item -> param1 ) ;
			
			$title .= '<tr><td style="left">' . 
				kohana::lang('global.documentnumber'). '</td><td>' . $item -> item_id . '</td></tr>';
			$title .= '<tr><td style="left">' . 
				kohana::lang('global.type'). '</td><td>' . kohana::lang( $structuretype ). '</td></tr>';
			$title .= '<tr><td style="left">' . 
				kohana::lang('global.propertyid'). '</td><td>' . $propertyid . '</td></tr>';
			
			// se lo scroll di proprietà punta ad una struttura non esistente, cancella l' item.
			
			$structure = ORM::factory('structure', $propertyid);
			if ($structure -> loaded == false )
			{
				
				$_i = ORM::factory('item', $item -> item_id );
				$_i -> destroy();
			}
			
			
		}		
		
		if ( $item -> parentcategory=='scrolls' && $item->subcategory=='generic')		
			$title .= '<tr><td style="left">' . kohana::lang('global.title'). '</td><td>' . $item->param1 . '</td></tr>';		
		
		// dress
		
		if ( $item -> parentcategory == 'clothes' and $item->colorable and ! is_null($item->color) )
		{
			$title .= '<tr><td>' . kohana::lang('items.color') . '</td><td style="left"><div class="box_color_inv" style="background-color:'.$item->hexcolor.'">&nbsp</div></td></tr>';
		}
		
		// food, drinks, potions
		
		if ( 
			$item -> parentcategory == 'consumables' )
		{
			$text = '';
			
			if ( 
				$item -> subcategory == 'rawfood' or
				$item -> subcategory == 'cookedfood'			
			)
				$text = ( $item -> spare1 / 50 ) * 100 . '%G';
					
			if ( $item -> subcategory == 'alcoholicdrink')
			{
				$text = ( $item -> spare3 / 50 ) * 100 . '%E';				
				$text .= ' (' . kohana::lang('items.intoxicationlevel', $item -> spare1 ) . ')';
			}
			
			$title .= '<tr><td style="left">' . 
				kohana::lang('items.restore'). '</td><td>' .  
			$text . '</td></tr>';					
		}
		
		// armors
		
		if ( $item -> parentcategory =='armors')
		{
			$title .= '<tr><td style="left">' . kohana::lang('items.defense'). '</td><td>' .  $item->defense . '</td></tr>';
		}
		
		//weapon
		
		if ( $item->parentcategory =='weapons')
		{
			$title .= '<tr><td style="left">' . kohana::lang('items.damage'). '</td><td>' .  $item->mindmg . '-' . $item->maxdmg . '</td></tr>';		
			$title .= '<tr><td style="left">' . kohana::lang('items.size'). '</td><td>' .  $item->size . '</td></tr>';		
			$title .= '<tr><td style="left">' . kohana::lang('items.bluntdamage'). '</td><td>' .  $item->bluntperc . '%' . '</td></tr>';		
			$title .= '<tr><td style="left">' . kohana::lang('items.hitdamage'). '</td><td>' .  $item->cutperc . '%' . '</td></tr>';
		}
		
		// l' item è prestato?
		
		if ( !is_null( $item -> lend_id ) )
		{
			$lend = ORM::factory('structure_lentitem', $item -> lend_id );			
			if ($lend -> loaded )
			{
				$title .= '<tr><td style="left">' . kohana::lang('items.ownerarmory') . '</td><td>' . 
					kohana::lang( $lend -> structure -> region -> name ). '</td>' ;				
				$title .= '<tr><td style="left">' . kohana::lang('items.lend_id'). '</td><td>' .  $item->lend_id . '</td></tr>';		
			}			
		}
		
		$title .= '<tr><td style="left">' . kohana::lang('items.weight'). '</td><td>' .  ($item->weight/1000) .' Kg'	 . '</td></tr>';		
		
		// l' item è loccato?
				
		if ( $item -> locked )
			$title .= '<tr><td style="left">' . kohana::lang('global.locked') . '</td><td>' . 
				kohana::lang('global.yes') . '</td>';
		else
			$title .= '<tr><td style="left">' . kohana::lang('global.locked') . '</td><td>' . 
				kohana::lang('global.no') . '</td>';
			
		///////////////////////////////////////
		// Link e azioni contestuali
		///////////////////////////////////////
	
		if ( 
			$item -> equipped != 'unequipped' 
			and 
			$page == 'inventory' 
		)
			
			$title .= '<tr><td style="left">' . kohana::lang('global.worn'). '</td><td>' . kohana::lang('items.' . $item->equipped) . '</td></tr>';		
	
		$title .= '<tr><td colspan="2"><br/></td></tr>';
		
		
		// per la pagina structureinventory 
		// abilitiamo solo alcune azioni		
		
		if ( $page == 'structureinventory' )
		{
			$title .= '<br/>';
			foreach ( $item -> actions as $key => $value )
			{
				if ( $value == 'recuperateiron' )
					$title .= '<tr><td style="left" colspan="2">' . 				
						html::anchor('/item/' . $value . '/' . $item -> item_id , 
							kohana::lang('global.'.$value), array('class'=> 'st_common_command') ) . '</td></tr>';
			}
		}
		
		// pagina inventory personale
		
		if ( $page == 'inventory' )
		{
			
			$title .= '<br/>';		
			foreach ( $item -> actions as $key => $value )
			{
				
				// APPLY
				
				if ($value == 'apply')
				{
					$title .= 
					'<tr>
						<td style="left" colspan="2">' . 				
						html::anchor('/item/' . $value . '/' . $item -> item_id , 
							kohana::lang('global.'.$value), array('class'=> 'st_common_command') ) . '&nbsp;' .				
						html::anchor('/item/' . $value . '/' . $item -> item_id .'/2', 
							' - x2', 
								array(
									'class'=> 'st_common_command',
									'onclick' => "return confirm('" .
										kohana::lang('charactions.confirm_operation_consume', 
											2, kohana::lang($item -> name )) . 
											"')"
								)
						) . 
						'&nbsp;' .
						html::anchor('/item/' . $value . '/' . $item -> item_id . '/3', 
							' - x3', 
								array(
									'class'=> 'st_common_command',
									'onclick' => "return confirm('" .
										kohana::lang('charactions.confirm_operation_consume', 
											3, kohana::lang($item -> name )) . 
											"')"
								)
						);					
				}
				
				// TUTTE LE ALTRE AZIONI
				
				else
					$title .= '<tr><td style="left" colspan="2">' . 				
						html::anchor('/item/' . $value . '/' . $item -> item_id , 
							kohana::lang('global.'.$value), array('class'=> 'st_common_command') ) . '</td></tr>';
			}
		}
		
		// page: market -- no link di azioni
		
		if ( $page == 'market' )
			if ( $item -> parentcategory == 'scrolls' )
				$title .= '<tr><td style="left" colspan="2">' . html::anchor('/item/read/' . $item -> item_id , 
					kohana::lang('global.read'), array('class'=> 'st_common_command') ) . '</td></tr>';
		
		$title .= '</table>' ;

		return $title;
	
	}
	
	/**
	* Funzione che torna tutte le azioni contestuali di un oggetto
	* @param: obj $item Item_Model
	* @return string $html html da stampare
	*/	
	
	function get_actions ( $item )
	{
		
		$actions = array();
		
		// mostra take solo se l' item è sul terreno.
		
		if ( !is_null ( $item -> region_id ) )
			$actions[] = 'take';
		else
			;

		// Se l' item è di classe container, aggiungo open
		
		if ($item -> category == 'container' )
			$actions[] = 'open';

		if ($item -> tag == 'furnace' )
		{
			$actions[] = 'recuperateiron';			
		}
		
		// Se l'item è consumabile aggiunge apply
		
		if ( $item -> parentcategory == 'consumables' 
		and in_array(
			$item -> subcategory, array( 
				'cookedfood', 'rawfood', 'healing', 'alcoholicdrink', 'potion' )))
		{ $actions[] = 'apply'; }

		// Se l'item è un armor, una weapon, un clothes o un tool allora visualizzo l'azione 'indossa'
		
		if (
			in_array($item->parentcategory, array(
				'tools',
				'armors',
				'clothes',
				'weapons' ))
			and
				$item -> equipped == 'unequipped'
			and 
				!is_null($item -> part) 
		)
		{ $actions[] = 'wear'; }
		
		// Se l'item è un armor o una weapon ed è prestato, aggiungo 'ritorna'
		if ( !is_null( $item -> lend_id ) )		
		{ $actions[] = 'returnlentitem'; }
		
		// Se l'item è un vestito colorabile allora visualizzo l'azione 'colora'
		// permesso solo per oggetti nella inventory del char.
		
		if ( !is_null($item -> character_id ) 
			and 
			$item -> parentcategory == 'clothes' 
		  and 
			$item -> colorable 
		)
		{ $actions[] = 'tint'; }
		
		// Se l' item è un armor o un weapon ed è equipaggiato visualizzo l' azione undress
		if (
			in_array($item->parentcategory, array(
				'tools',
				'armors',
				'clothes',
				'weapons' ))
		and
			$item -> equipped != 'unequipped' )			
		{ $actions[] = 'undress'; }		

		// Se l' item ha il flag canbesent true, aggiungo send.
		if ( $item->canbesent )
		{ $actions[] = 'send'; }
				
		// se l' item è scroll aggiungo leggi
		if ( $item->category=='scroll' )
		{ $actions[] = 'read'; }

		// se l' item è uno scroll generico aggiungo esibisci
		// valido solo per oggetti nell' inventory del char.
		
		if ( !is_null($item -> character_id) 
				and 
			  ($item->category == 'scroll' 
			  or 
				$item->subcategory=='generic' ))
		{ $actions[] = 'exhibit'; }
		
		// se l' item è un pezzo di carta aggiungo scrivi
		if ( $item->tag=='paper_piece' )
		{ $actions[] = 'write'; }
		
		// se l' item è un cart pro, aggiungo REST
		if ( $item -> tag == 'cart_3' )
		{ $actions[] = 'rest'; }
		
		return $actions;
	
	}

	
	/**
	* aggiunge un item
	* @param string destination 'character' or 'structure'
	* @param int item id
	* @param int quantity to add
	* @param boolean item shall be set as equipped
	* @return false or true
	*/
	
	public function additem( $destination, $id, $quantity = 1, $equipped = null)
	{
	
		kohana::log('debug', "----- ADDING ITEM -----");
		kohana::log('debug', "Adding {$quantity} {$this -> cfgitem -> tag} to: {$destination} {$id}." );

		// se la quantità è <= 0, non fare niente poichè siamo nella additem.
		
		if ( $quantity <= 0 ) 
			return;
				
		/////////////////////////////////////////////////////////////////////
		// se il tipo di item è scroll, l' item è locked, o la lend_id è 
		// popolata, non è raggruppabile per definizione e devo modificare 
		// proprio l' ID specifico
		/////////////////////////////////////////////////////////////////////
		
		if ( in_array( $this -> cfgitem -> category, array ( 'scroll' ) ) or 
		     in_array( $this -> cfgitem -> tag, array( 'secretbox', 'easteregg', 'cart_1', 'cart_2', 'cart_3')) or 
			$this -> locked == true or 
			!is_null( $this -> lend_id )
		)
		{
			$groupable = false;
			//kohana::log('debug', 'Item is NOT GROUPABLE' ); 
		}
		else
		{
			$groupable = true;
			
			/////////////////////////////////////////////////////////////////////
			// trovo tutti gli elementi con lo stesso cfgitem_id, quality	e
			// colore ecc
			/////////////////////////////////////////////////////////////////////
					
			if ( $destination == 'character' )
			{
			
				$i = ORM::factory( "item" ) 
					-> where( array( 
							'cfgitem_id' => $this -> cfgitem_id,
							'character_id' => $id,
							'quality' => $this -> quality,	
							'hexcolor' => $this -> hexcolor,
							'color' => $this -> color,
							'lend_id' => $this -> lend_id,
							'equipped' => 'unequipped',
							'locked' => $this -> locked,
						) 
					) -> find() ;
			}
			
			if ( $destination == 'region' )
			{
			
				$i = ORM::factory( "item" ) ->
						where( array( 
							'cfgitem_id' => $this -> cfgitem_id,
							'region_id' => $id, 
							'quality' => $this -> quality,	
							'hexcolor' => $this -> hexcolor,
							'color' => $this -> color,
							'lend_id' => $this -> lend_id,
							'equipped' => 'unequipped',
							'locked' => $this -> locked,
				) ) -> find() ;										
			}
			
			if ( $destination == 'structure' )
			{
					$structure = StructureFactory_Model::create( null, $id ); 
					
					// se si aggiunge un item al mercato, deve essere 
					// raggruppato per seller id, prezzo e qualità ecc.
					
					//kohana::log('debug', 'Trying to find a similar item...');
					
					if ( $structure -> structure_type -> supertype == 'market' )
					
				
						$i = ORM::factory( "item" )->
							where( array( 
								'cfgitem_id' => $this -> cfgitem_id,
								'structure_id' => $id,		
								'seller_id' => $this -> seller_id,
								'recipient_id' => $this -> recipient_id,
								'price' => $this -> price,
								'quality' => $this -> quality,
								'lend_id' => $this -> lend_id,
								'hexcolor' => $this -> hexcolor,
								'color' => $this -> color,
								'locked' => $this -> locked, 
							) )->find();			
					else
						
						$i = ORM::factory( "item" )->
						where( array( 
							'cfgitem_id' => $this -> cfgitem_id,
							'structure_id' => $id,							
							'quality' => $this -> quality,
							'hexcolor' => $this -> hexcolor,	
							'lend_id' => $this -> lend_id,
							'color' => $this -> color,
							'locked' => $this -> locked, 
							) )->find();			
			}
					
		}
		
		
		/////////////////////////////////////////////////////////////////////////////////
		// Se è groupable, ed è stato trovato un gruppo di oggetti simili, aggiungo 
		// l' item al gruppo.		
		/////////////////////////////////////////////////////////////////////////////////
		
		if ( $groupable and $i -> loaded )
		{					
			$newquantity = $i -> quantity + $quantity;
			
			//kohana::log('debug', "Item is GROUPABLE and i DID found a group of similar items.");
			
			$i -> quantity = $newquantity;	
			$i -> save(); 
			
			// Invalida cache del char		
			
			if ( $destination == 'character' )		
			{				
				My_Cache_Model::delete(  '-charinfo_' . $id . '_' . $this -> cfgitem -> tag );			
			}
		}
		
		/////////////////////////////////////////////////////////////////////////////////
		// Se non è groupable, o è groupable MA non è stato trovato un gruppo di oggetti 
		// simili, creo un nuovo oggetto.		
		/////////////////////////////////////////////////////////////////////////////////
		
		if ( $groupable == false or ($groupable and !$i -> loaded )	)
		{
			
			//kohana::log('debug', "Additem: Items is NOT GROUPABLE or is GROUPABLE but no similar group has been found, so i will add an item."); 
			
			if ( $destination == 'character' )
			{
			
				$n = new Item_Model();
				$n -> character_id = $id;
				$n -> structure_id = null;
				$n -> region_id = null;
				$n -> cfgitem_id = $this->cfgitem_id;				
				$n -> quality = $this -> quality;
				$n -> param1 = $this -> param1;
				$n -> param2 = $this -> param2;
				$n -> param3 = $this -> param3;
				$n -> sendorder = $this -> sendorder;
				$n -> structure_id = null;
				$n -> color = $this -> color;
				$n -> hexcolor = $this -> hexcolor;				
				$n -> lend_id = $this -> lend_id;
				$n -> locked = $this -> locked; 
				$n -> quantity = $quantity;
				
				if ( $equipped )
					$n -> equipped = $this -> cfgitem -> part;
		
			}
			
			if ( $destination == 'region' )
			{
			
				$n = new Item_Model();
				$n -> character_id = null;
				$n -> structure_id = null;
				$n -> region_id = $id;
				$n -> cfgitem_id = $this->cfgitem_id;				
				$n -> quality = $this -> quality;
				$n -> param1 = $this -> param1;
				$n -> param2 = $this -> param2;
				$n -> param3 = $this -> param3;
				$n -> sendorder = $this -> sendorder;
				$n -> structure_id = null;
				$n -> color = $this -> color;
				$n -> hexcolor = $this -> hexcolor;				
				$n -> lend_id = $this -> lend_id;
				$n -> locked = $this -> locked; 
				$n -> quantity = $quantity;
				
				if ( $equipped )
					$n -> equipped = $this -> cfgitem -> part;
		
			}

			if ( $destination == 'structure' )
			{
			
				$n = new Item_Model();
				$n -> character_id = null;
				$n -> region_id = null;
				$n -> cfgitem_id = $this -> cfgitem_id;				
				$n -> structure_id = $id;
				$n -> param1 = $this -> param1;
				$n -> param2 = $this -> param2;
				$n -> param3 = $this -> param3;		
				$n -> salepostdate = $this -> salepostdate;				
				$n -> quality = $this -> quality;
				$n -> quantity = $quantity;						
				$n -> seller_id = $this -> seller_id ;
				$n -> recipient_id = $this -> recipient_id ;
				$n -> price = $this -> price;
				$n -> tax_citizen = $this -> tax_citizen; 
				$n -> tax_friendly = $this -> tax_friendly; 
				$n -> tax_allied = $this -> tax_allied; 
				$n -> tax_neutral = $this -> tax_neutral; 
				$n -> hexcolor = $this -> hexcolor;				
				$n -> color = $this -> color;
				$n -> lend_id = $this -> lend_id;
				$n -> locked = $this -> locked; 
				$n -> quantity = $quantity;
				
			}
			
			$n -> save();
			
			// Invalida cache del char		
			
			if ( $destination == 'character' )		
			{			
				My_Cache_Model::delete(  '-charinfo_' . $id . '_' . $this -> cfgitem -> tag );			
			}
			
		}
		
		kohana::log('debug', '----- END add item -----' );
		
		return true;
	}
	
	/** 
	* Rimuove un item
	* @param destination: "character" oppure "structure": indica a che entità deve essere rimosso l' item
	* @param id: id della entità
	* @param quantity: quantità da aggiungere
	* @return: esito
	*/
	
	public function removeitem( $destination, $id, $quantitytoremove = 1)
	{
		
		kohana::log('debug', "----- REMOVING ITEM -----");
		kohana::log('debug', "Removing {$quantitytoremove} {$this -> cfgitem -> tag} from: {$destination} {$id}." );
		
		// queste categorie non sono raggruppabili per definizione

		if ($this -> cfgitem -> tag == 'silvercoin' and $quantitytoremove != 0 )
			kohana::log('info', "-> -$quantitytoremove silver coins from: $destination $id ");
		
		if ( 
			in_array( $this -> cfgitem -> category, array ( 'scroll' ) ) 
			or 
		    in_array( $this -> cfgitem -> tag, array( 'secretbox', 'cart_1', 'cart_2', 'cart_3')
		) or 
		$this -> locked == true 
		or 
		!is_null( $this -> lend_id )
		)
		{
		
			$groupable = false;
			kohana::log('debug', '-> Item is NOT GROUPABLE' ); 		
		}
		else
		{
			kohana::log('debug', '-> Item is GROUPABLE' ); 
			$groupable = true;
		}
		
		
		$items = array();		
		$quantityremoved = 0;
		$itemtag = $this -> cfgitem -> tag ;
		
		// se l' oggetto non è groupable, operiamo sull' item stesso.
		
		if ( ! $groupable )					
			$items = ORM::factory( "item") -> 
			where ( 
				array( 'id' => $this -> id ) ) 
					-> find_all();			
		else
		{
		
			// se l' oggetto è groupable, troviamo se esiste un gruppo di item simili
			// Gli attributi che lo rendono non groupable non sono considerati nel 
			// raggruppamento.			
			
			if ( $destination == "character")
			{			

				$items = ORM::factory( "item" ) ->
					where( array( 
						'cfgitem_id' => $this -> cfgitem_id,
						'character_id' => $id,
						'param1' => $this -> param1, 
						'quality' => $this -> quality,
						'hexcolor' => $this -> hexcolor,
						'color' => $this -> color,
					) ) -> find_all();								
			
				//	kohana::log('info', 'selecting. id: ' . $id ); 
				//	kohana::log('info', kohana::debug( $items )); 
			}
			
			if ( $destination == "region")
			{			

				$items = ORM::factory( "item" ) ->
					where( array( 
						'cfgitem_id' => $this -> cfgitem_id,
						'region_id' => $id,
						'param1' => $this -> param1, 
						'quality' => $this -> quality,
						'hexcolor' => $this -> hexcolor,
						'color' => $this -> color,
					) ) -> find_all();								
			
				kohana::log('info', 'selecting. id: ' . $id ); 
				//	kohana::log('info', kohana::debug( $items )); 
			}
			
			if ( $destination == "structure")
			{
					$structure = StructureFactory_Model::create( null, $id ); 					
					//kohana::log('debug', "Structure id: {$structure -> id}");
					//kohana::log('debug', "Structure type: {$structure -> structure_type -> type }");
					
					if ( $structure -> structure_type -> supertype == 'market' )						
					{
						$items = ORM::factory( "item" )->
							where( array( 
								'cfgitem_id' => $this->cfgitem_id,
								'structure_id' => $id,		
								'seller_id' => $this -> seller_id,
								'recipient_id' => $this -> recipient_id,
								'price' => $this -> price,								
								'quality' => $this -> quality,
								'hexcolor' => $this -> hexcolor,
								'color' => $this -> color,
							)) -> find_all();
							//kohana::log('debug', kohana::debug($items));
					}
					else									
					{
						$items = ORM::factory( "item" )->
							where( array( 
								'cfgitem_id' => $this->cfgitem_id,
								'structure_id' => $id,
								'param1' => $this -> param1, 
								'quality' => $this -> quality,
								'hexcolor' => $this -> hexcolor,
								'color' => $this -> color,					
					)) -> find_all();
					
					}
					
			}
		}
		
		kohana::log('debug', "-> Remove item: removing $quantitytoremove of " . $this -> cfgitem -> tag . " from $destination $id");
		kohana::log('debug', "-> Items: " . $items -> count());
		
		if ( $quantitytoremove > 0 and  $items -> count() == 0 )
		{
			kohana::log( 'error', "-> Attention! found 0 items for {$quantitytoremove} {$this -> cfgitem -> tag}");
			kohana::log( 'error', kohana::debug($items) );			
		}
		
		if ( $items -> count() > 0 )
		{
			while ( $quantitytoremove > 0 )
			{		
			
				kohana::log( 'debug', '-> Remove item: still to remove: '. $quantitytoremove . ' removed: ' . $quantityremoved );
				
				foreach ( $items as $item ) 
				{
					
					kohana::log('debug', '-> Remove item: item: ' . $item -> id . ' has quantity: ' . $item -> quantity );
					
					if ( $item -> quantity > $quantitytoremove )
					{
						kohana::log('debug', '-> Remove item: removing: ' . $quantitytoremove . ' from item: ' . $item->id ); 
						$item -> quantity -= $quantitytoremove;	
						$quantityremoved += $quantitytoremove ;
						$quantitytoremove = 0;
						$item -> save();
						kohana::log('debug', "-> Remove item: Now item " . $item -> id . " has quantity: " . $item -> quantity );
					}
					else
					{
						kohana::log('debug', '-> Remove item: removing: ' . $quantitytoremove . ' from item: ' . $item->id ); 
						$quantityremoved += $item->quantity;
						$quantitytoremove -= $item->quantity;				
						kohana::log('debug', '-> Remove item: deleting item: ' . $item->id ); 						
						$item-> delete ();
					}					
				}
			}
		}
		
		
		if ( $destination == 'character' )
			My_Cache_Model::delete(  '-charinfo_' . $id . '_' . $this -> cfgitem -> tag );

		kohana::log('debug', '-> --- END remove item --- ' );
		
		return true;
	
	}
	
	
	/** 
	* Funzione per consumare la qualità di un item
	* all' interno di una struttura
	* @param tag tag dell' oggetto da consumare
	* @param id struttura
	* @param amountroconsume percentuale da consumare
	* @return done (true o false)
	*/
	
	public function consumeitem_instructure( $tag, $structure_id, $amounttoconsume )		
	{
		kohana::log('debug', "------ CONSUME ITEMS ------");
		
		$lefttoconsume = $amounttoconsume;

		$sql = "
			SELECT i.id, i.quantity, i.quality, i.quality quality_copy
			FROM items i, cfgitems ci 
			WHERE i.cfgitem_id = ci.id
			AND ci.tag ='{$tag}'
			AND structure_id = {$structure_id}
			ORDER BY i.quantity ASC, i.quality ASC
		";
		
		$items = Database::instance() -> query( $sql ) -> as_array();
		kohana::log('debug', kohana::debug($items));
		
		while ($lefttoconsume > 0 )
		{
			foreach ( $items as $item )
			{
				if ( $item -> quality >= $lefttoconsume )
				{					
					$item -> quality -= $lefttoconsume ;
					$lefttoconsume = 0;
					break;
				}
				
				if ( $item -> quality < $lefttoconsume )
				{
					$lefttoconsume -= $item -> quality;
					$item -> quality = 0 ;					
				}								
			}
			
		}
		
		// scorro il vettore e applico le modifiche al dba_close
		
		foreach ( $items as $item )
		{
			
			if ( $item -> quality == 0 )
			{
					$_item = ORM::factory('item', $item -> id );
					$_item -> destroy();				
			}
			
			// se è stato toccata la auqlità e l' item ha quantity 1, salva la nuova qualità
			if (
				$item -> quality > 0 
				and 
				$item -> quality != $item -> quality_copy 
				and 
				$item -> quantity == 1 
			)
			{
					$_item = ORM::factory('item', $item -> id );
					$_item -> quality = $item -> quality;
					$_item -> save();
			}
			// se è stato toccata la auqlità e l' item ha quantity > 1, scorpora
			if ( 
				$item -> quality > 0 
				and 
				$item -> quality != $item -> quality_copy 
				and 
				$item -> quantity > 1 
				
				)
			{
					$_item = ORM::factory('item', $item -> id );
					
					// separiamo un item identico settandogli la qualità.
					
					$newitem = $_item -> cloneitem();
					$newitem -> structure_id = $structure_id;
					$newitem -> quantity = 1;
					$newitem -> quality = $item -> quality;
					$newitem -> save();						
					
					$_item -> quantity -= 1 ;
					$_item -> save();					
			}			
			
		}
		
		return true;
		
		
	}
	
	public function obs_consumeitem_instructure( $tag, $structure_id, $amount )	
	{
		kohana::log('debug', "------ CONSUME ITEMS ------");
		kohana::log('debug', "-> Consuming item $tag in structure $structure_id, amount: $amount" );
		
		$structure = StructureFactory_Model::create( null, $structure_id );
		$done = false;		
		
		// stabiliamo quanti item e qualità ci sono in totale.
		
		$sql = "
			SELECT SUM(quantity*quality) totalquality
			FROM items i, cfgitems ci 
			WHERE i.cfgitem_id = ci.id
			AND ci.tag = 'wood_dummy' 
			AND structure_id = {$structure_id}";
			
		$rset = Database::instance() -> query( $sql ) -> as_array();		
		$totalquality = $rset[0] -> totalquality;		
		
		kohana::log('debug', "-> totalquality: {$totalquality}, Amount to consume: {$amount}");
		
		if ($totalquality < $amount )
			$amount = $totalquality;

		// Cerchiamo se esiste un item scorporato e con qualità < 100. 
		// se c'è, consumiamo quello.
		
		$lefttoconsume = $amount;
		
		kohana::log('debug', "-> Left to consume: {$lefttoconsume}");
		
		while ( $lefttoconsume > 0 )
		{
				
			// ReLoad items from structures
			
			$items = ORM::factory( 'item' ) -> where ( 'structure_id', $structure -> id ) -> find_all();
			
			// priorità agli item scorporati e con qualità < 100
			
			foreach ( $items as $item )
				if ( $item -> cfgitem -> tag == $tag )
					if ( $item -> quantity == 1 and $item -> quality < 100 )
					{
						kohana::log('debug', "-> Found an item with quality: {$item -> quality}.");
						
						$lefttoconsume = max( 0, $lefttoconsume - $item -> quality);
						kohana::log('debug', "-> Left to consume: {$lefttoconsume}");
						$item -> consume( $amount, 'consumeiteminstructure' );					
						$done = true;
						break;
					}
			
			// ReLoad items from structures
			
			$items = ORM::factory( 'item' )-> where ( 'structure_id', $structure -> id ) -> find_all();
					
		
			// se l' item c'è ma è raggruppato, lo scorporo.		
			
			if ( $done == false )
			{
				foreach ( $items as $item )
					if ( $item -> cfgitem -> tag == $tag )
						if ( $item -> quantity > 1 )
						{
							$item -> quantity -= 1;
							$item -> save();							
							$newitem = $item -> cloneitem();
							$newitem -> structure_id = $structure -> id;
							$newitem -> quantity = 1;
							$newitem -> save();						
							$lefttoconsume = max( 0, $lefttoconsume - $item -> quality);
							kohana::log('debug', "-> Left to consume: {$lefttoconsume}");
							$newitem -> consume( $amount, 'consumeiteminstructure' );						
							$done = true;
							break;
						}
			}
		}
		
		return $done;
		
	}
	

	/** 
	* Calcola il consumo e consuma un item
	* @param obj Character_Model
	* @param string $action azione correlata
	* @param int $multiplier moltiplicatore
	* @return none
	*/
	
	public function consumeclothes( $char, $action, $multiplier = 1 )
	{
		kohana::log('debug', '-> Consumeclothes multiplier: ' . $multiplier );
		
		$actionsconsume = array( 
			'butcher' => kohana::config('medeur.consume_medium'),
			'cleanprisons' => kohana::config('medeur.consume_verylow'),
			'craft' => kohana::config('medeur.consume_low'),
			'damage' => kohana::config('medeur.consume_medium'),
			'dig' => kohana::config('medeur.consume_medium'),
			'excommunicateplayer' => kohana::config('medeur.consume_verylow'),
			'feed' => kohana::config('medeur.consume_low'),
			'fish' => kohana::config('medeur.consume_verylow'),
			'gather' => kohana::config('medeur.consume_low'),
			'getwood' => kohana::config('medeur.consume_medium'),
			'harvest' => kohana::config('medeur.consume_medium'),
			'initiate' => kohana::config('medeur.consume_verylow'),
			'cure' => kohana::config('medeur.consume_low'),
			'move' => kohana::config('medeur.consume_verylow'),
			'pray' => kohana::config('medeur.consume_verylow'),			
			'searchdump' => kohana::config('medeur.consume_verylow'),
			'searchplant' => kohana::config('medeur.consume_verylow'),
			'seed' => kohana::config('medeur.consume_verylow'),
			'shopupgradeinventory' => kohana::config('medeur.consume_medium'),
			'repair' => kohana::config('medeur.consume_medium'),
			'shovel' => kohana::config('medeur.consume_medium'),
			'study' => kohana::config('medeur.consume_verylow'),
			'upgradestructureinventory' => kohana::config('medeur.consume_medium'),
			'upgradestructurelevel' => kohana::config('medeur.consume_medium'),
			'workonproject' => kohana::config('medeur.consume_medium'),
		);
		
		$equippeditems = Character_Model::get_equipment( $char -> id );
		
		foreach ( $equippeditems as $equippeditem )	
		{
			$i = ORM::factory('item', $equippeditem -> id );
			if ( 
				isset( $actionsconsume[ $action ]) 
				and 
				in_array( $i -> cfgitem -> parentcategory, array( 'clothes', 'armors' ))
				and
				$i -> cfgitem -> category != 'jewel' 
			)				
				$i -> consume( $actionsconsume[ $action ] * $multiplier, 'action-' . $action );
		}
		
		return;
		
	}
	
	/** 
	* Funzione per il calcolo del consumo di un attrezzo in base ad una stat del char
	* @param stat: tipo di statistica su sui eseguire il check
	* @param value: valore della stat del char
	* @return valore da consumare alla qualità dell'item 
	*/   
	
    public function get_proper_item_consume ($stat, $value)
	{
		$consume = 0;
		
		if ($stat == "dex")
		{
			switch ($value)
			{
				case ($value >= 14):
				$consume = rand(1,2);
				break;

				case ($value >= 10 AND $value < 14):
				$consume = rand(1,3);
				break;

				case ($value < 10):
				$consume = rand(2,4);
				break;
			}
		}
		
		return $consume;
	}

	
	/** 
	* Consuma un item
	* @param float $rate Consumo
	* @param string $reason motivo dell' usura
	* @return none
	*/
	
	public function consume( $rate, $reason )
	{
		$this -> quality -= ($rate / max(1, $this -> cfgitem -> wearfactor));
		
		kohana::log('debug', '-> Consuming Item: ' . $this -> id . ' - ' . $this -> cfgitem -> tag . ' - rate: ' . $rate . ' - reason: ' . $reason ); 
		
		if ( $this -> quality <= 0 )
		{
			Character_Event_Model::addrecord(
				$this->character->id, 
				'normal',  
				'__events.itemsbroke'.
				';__' . $this -> cfgitem -> name
				);
				
			$this -> destroy();
			
		}
		else
		{
			$this->save();
		}
		
		return;
		
	}
	
	/** Funzione per muovere un oggetto da un char all' altro	
	* @param item oggetto item
	* @param source oggetto char di chi possiede l' oggetto
	* @param dest   oggetto char di chi riceve l' oggetto
	*/
	
	function move( $item, $source, $dest )
	{
		//kohana::log( 'debug', '-- moving item ' . $item->cfgitem->name . ' from ' . $source->name . ' to ' . $dest->name );
		
		$item->removeitem( 'character', $source->id, $item->quantity);
		$item->additem( 'character', $dest->id, $item->quantity);
		/*
		$item->character_id = $dest->id ;
		$item->equipped = 'unequipped';
		$item->save();
		*/
		return;
	
	}
	
	/**
	* Calcola il costo ed il tempo per inviare n oggetti
	* @param int $quantity: quantità da inviare
	* @param int $item_id: ID item da inviare
	* @param obj $source: oggetto char che invia
	* @param obj $target: nome char che riceve
	* @param string $action: action che invoca l' invio
	* @return array $info ( price = prezzo, time = tempo in secondi, rc = OK o messaggio d'errore )
	*/
	
	function computesenddata ( $quantity, $item_id, $source, $targetname, $action )
	{
		
		kohana::log('info', '--- SEND ITEM ---');
		$info = array( 'cost' => 0, 'time' => 0, 'rc' => 'OK', 'message' => null );	
		$target = ORM::factory('character') -> where ( 'name',  $targetname) -> find( );	
		$item = ORM::factory('item', $item_id );
		$source_region = ORM::factory('region', $source -> position_id );
		if ( Character_Model::is_traveling( $target -> id ) )
		{
			kohana::log('debug', "-> Char {$target->name} is traveling.");
			$moveaction = Character_Model::get_currentpendingaction( $target -> id ); 			
			$target_region = ORM::factory('region', $moveaction['param2']	);			
		}
		else
		{
			kohana::log('debug', "-> Char {$target->name} is NOT traveling.");
			$target_region = ORM::factory('region', $target -> position_id );			
		}
		
		// Fai controlli di consistenza solo se l' action è send.
		
		if ( $action == 'send' )		
		{
			
			// Non si possono inviare messaggi a se stessi
			if ( $source -> id == $target -> id )
			{
				$info['rc'] = 'NOK';
				$info['message'] = kohana::lang('ca_senditem.error-sourceandreceiverarethesame');
				return $info;
			}
			// Il personaggio deve esistere
			if ( !$target -> loaded )
			{
				$info['rc'] = 'NOK';
				$info['message'] = kohana::lang('global.error-characterunknown');
				return $info;
			}		
			
			// Non puoi fare più invii
			
			$res = Database::instance() -> query( 
			"select * from character_actions 
			where action = 'senditem' 
			and   character_id = " . $source -> id . "
			and   status = 'running' 
			and   param5 is null ");
			
			if ( count( $res ) > 0 and $action != 'lend' )
			{
				$info['rc'] = 'NOK';
				$info['message'] = kohana::lang('ca_senditem.error-sourcealreadysending');
				return $info;
			}
			
			// L' item deve esistere
			
			if ( !$item -> loaded )
			{
				$info['rc'] = 'NOK';
				$info['message'] = kohana::lang('global.itemnotfound');
				return $info;
			}
			
			if (  Character_Model::has_item( $source -> id, $item -> cfgitem -> tag, $quantity ) == false )
			{
				$info['rc'] = 'NOK';
				$info['message'] = kohana::lang('charactions.itemsquantitynotowned');
				return $info;
			}		
							
			if ( $action == 'send' and $quantity > 50 and ! in_array( $item -> cfgitem -> tag, array( 'doubloon', 'silvercoin', 'coppercoin' ) ) )
			{
				$info['rc'] = 'NOK';
				$info['message'] = kohana::lang('ca_senditem.error-toomanyitems');
				return $info;
			}
			// gli oggetti locked o to be sent non possono essere inviati
			if ( $item -> cfgitem -> canbesent == false or $item -> locked )
			{
				$info['rc'] = 'NOK';
				$info['message'] = kohana::lang('ca_senditem.error-notsendableitem');
				return $info;
			}					
			
			// la quantità è > 0?
			if ( $quantity <= 0 )
			{
				$info['rc'] = 'NOK';
				$info['message'] = kohana::lang('charactions.negative_quantity');
				return $info;
			}
			
			// il char destinatario ed il sorgente, sono in prigione?
			if ( Character_Model::is_imprisoned( $target -> id ) or 
				 Character_Model::is_imprisoned( $source -> id ) )
			{
				$info['rc'] = 'NOK';
				$info['message'] = kohana::lang('ca_senditem.error-targetorsourceimprisoned');
				return $info;
			}		
			
		}
		
		$weight = $quantity * $item -> cfgitem -> 	weight / 1000;
		
		if ( $weight > 100 )
		{
			$info['rc'] = 'NOK';
			$info['message'] = kohana::lang('ca_senditem.error-toomuchweight');
			return $info;
		}
		
		kohana::log('debug', '-> Weight: ' . $weight );
		
		// Stabiliamo il prezzo su quantità oggetti inviati
		
		if ( $quantity == 1 )
			$price_quantity = 1;
		elseif ( $quantity < 11 )
			$price_quantity = 2 * $quantity;
		elseif ( $quantity >= 11 )
			$price_quantity = round( 2.5 * $quantity, 0);
		
		// Stabiliamo il prezzo sul peso
		
		if ( $weight < 11 )
			$price_weight = 2 * $weight;
		else
			$price_weight = round( 2.5 * $weight, 0);
		
		
		kohana::log('debug', '-> Weight price: ' . $price_weight . ' Quantity price: ' . $price_quantity );
		$finalcost = max( $price_weight, $price_quantity);
		kohana::log('debug', '-> Final cost: ' . $finalcost );
		
		// casi speciali, override del prezzo
		
		if ( in_array( $item -> cfgitem -> tag, array( 'silvercoin', 'coppercoin' ) ) )
			$finalcost = intval($quantity / 100) ;
		
		if ( $item -> cfgitem -> tag == 'doubloon' )
			$finalcost = 0;		
		
		kohana::log('debug', 'Action: ' . $action . ' Final Cost : ' . $finalcost . ' quantity : ' . $quantity );
		
		// Se è una send e il char sta inviando soldi, il char deve avere i soldi che sta inviando
		// più il costo di invio.
		
		if ( 
			$action == 'send' and 
			$item -> cfgitem -> tag == 'silvercoin' and 
			$source -> check_money ( $finalcost + $quantity ) == false )
		{
			$info['rc'] = 'NOK';
			$info['message'] = kohana::lang('charactions.global_notenoughmoney');
			return $info;
		}
		
		// Caso altri oggetti
		
		elseif ( 
			$action == 'send' and 
			$item -> cfgitem -> tag != 'silvercoin' and 
			$source -> check_money ( $finalcost ) == false )
		{
			$info['rc'] = 'NOK';
			$info['message'] = kohana::lang('charactions.global_notenoughmoney');
			return $info;
		}
		
		$info['cost'] = Utility_Model::number_format($finalcost,2);
		
		// tempo per inviare l' oggetto
		
		$distance = Region_Path_Model::compute_distance( $source_region -> name, $target_region -> name );
		
		kohana::log('debug', 'distance is : ' . $distance );
		
		// dobloni vengono inviati instantaneamente.
		
		if ( $item -> cfgitem -> tag == 'doubloon' )
		{			
			$info['time'] = time();
		}
		else
		{
			$info['time'] = time() + max( 3600, intval($distance * ( 25.5 + ( $weight / 50 ) ))) / kohana::config('medeur.serverspeed');
		}
		
		$info['timetext'] = Utility_Model::format_datetime($info['time']);

		kohana::log('debug', kohana::debug($info));		
		
		return $info;
		
	}
	
	/**
	* Calcola il peso totale dell' oggetto
	* @input quantity numero oggetti
	* @return peso totale dell' oggetto
	*/
	
	function get_totalweight( $quantity )
	{
		return ($this -> cfgitem -> weight * $quantity );
	}
	
	//////////////////////////////////////////////////////////////////////////////
	// metodi da overridare opportunamente nell'
	// oggetto di categoria item
	//////////////////////////////////////////////////////////////////////////////
	
	// controlli ed azioni proprietari dell' oggetto su acquisto
	public function buy_do_proprietary_check()  { return true; }	
	public function buy_do_proprietary_action() { return true; }
	
	// controlli ed azioni proprietari dell' oggetto su vendita
	public function sell_do_proprietary_check() { return true; }
	public function sell_do_proprietary_action() { return true; }
	
	// controlli ed azioni proprietari dell' oggetto su take
	public function take_do_proprietary_check() { return true; }
	public function take_do_proprietary_action() { return true; }
		
	/*
	* funzione che calcola la produzione di un item in base 
	* agli attributi
	* @input obj char che fa l' azione
	* @return numero di item prodotti
	*/
	
	public function computeproduction( $char )
	{
		$rangemin  = $this -> cfgitem -> spare5;
		$rangemax = $this -> cfgitem -> spare6;	
		$balancingfactor = 1;
		
		switch ( $this -> cfgitem -> tag )
		{
			case 'iron_piece' : 
				$rmin  = round ( $rangemin * ( 1 * $char -> get_attribute( 'str' ) + 1 * $char -> get_attribute( 'cost' ) ), 0 );
				$rmax = round ( $rangemax * ( 1 * $char -> get_attribute( 'str' ) + 1 * $char -> get_attribute( 'cost' ) ) , 0 );
				break;
			case 'gold_piece' : 				
					$rmin  = round ( $rangemin * ( 1 * $char -> get_attribute( 'str' ) + 1 * $char -> get_attribute( 'intel' ) ) , 0);
					$rmax = round ( $rangemax * ( 1 * $char -> get_attribute( 'str' ) + 1 * $char -> get_attribute( 'intel' ) )  , 0);
					break;								
			case 'stone_piece' : 
				$rmin  = round ( $rangemin * ( 1 * $char -> get_attribute( 'str' ) + 1 * $char -> get_attribute( 'cost' ) ) , 0);
				$rmax = round ( $rangemax * ( 1 * $char -> get_attribute( 'str' ) + 1 * $char -> get_attribute( 'cost' ) ) , 0);
				break;
			case 'wood_piece' : 
				$rmin  = round ( $rangemin * ( 1 * $char -> get_attribute( 'str' ) + 1 * $char -> get_attribute( 'dex' ) ) , 0);
				$rmax = round ( $rangemax * ( 1 * $char -> get_attribute( 'str' ) + 1 * $char -> get_attribute( 'dex' ) ) , 0);
				break;
			case 'medmushroom' : 
				$rmin  = round ( $rangemin * ( 1 * $char -> get_attribute( 'intel' ) ), 0);
				$rmax = round ( $rangemax * ( 1 * $char -> get_attribute( 'intel' ) ), 0);
				break;
			case 'mandragora' : 
				$rmin  = round ( $rangemin * ( 1 * $char -> get_attribute( 'intel' ) ), 0);
				$rmax = round ( $rangemax * ( 1 * $char -> get_attribute( 'intel' ) ), 0);
				break;
			case 'coal_piece' : 
				$rmin  = round ( $rangemin * ( 1 * $char -> get_attribute( 'str' ) + 1 * $char -> get_attribute( 'cost' ) ) , 0);
				$rmax = round ( $rangemax * ( 1 * $char -> get_attribute( 'str' ) + 1 * $char -> get_attribute( 'cost' ) ) , 0);
				break;
			case 'sand_heap' : 
				$rmin  = round ( $rangemin * ( 1 * $char -> get_attribute( 'str' ) + 1 * $char -> get_attribute( 'dex' ) ) , 0);
				$rmax = round ( $rangemax * ( 1 * $char -> get_attribute( 'str' ) + 1 * $char -> get_attribute( 'dex' ) ) , 0);
				break;
			case 'salt_heap' : 
				$rmin  = round ( $rangemin * ( 1 * $char -> get_attribute( 'str' ) + 1 * $char -> get_attribute( 'cost' ) ) , 0);
				$rmax = round ( $rangemax * ( 1 * $char -> get_attribute( 'str' ) + 1 * $char -> get_attribute( 'cost' ) ) , 0);
				break;
			case 'clay_piece' : 
				$rmin  = round ( $rangemin * ( 1 * $char -> get_attribute( 'str' ) + 1 * $char -> get_attribute( 'cost' ) ) , 0);
				$rmax = round ( $rangemax * ( 1 * $char -> get_attribute( 'str' ) + 1 * $char -> get_attribute( 'cost' ) ) , 0);
				break;
			case 'fish' : 
				$rmin  = round ( $rangemin * ( 1 * $char -> get_attribute( 'intel' ) + 1 * $char -> get_attribute( 'dex' ) ) , 0);
				$rmax = round ( $rangemax * ( 1 * $char -> get_attribute( 'intel' ) + 1 * $char -> get_attribute( 'dex' ) ) , 0);
				break;
			default: 
				$rmin = 0; $rmax = 0; break;				
		}
		$rmin /= $balancingfactor;
		$rmax /= $balancingfactor;
		
		kohana::log( 'debug', 'min: ' . $rmin . ', ' . 'max: ' . $rmax ); 
		
		srand( time() ); 
		
		$produceditems = rand( $rmin, $rmax); 
		
		kohana::log( 'debug', "-> produced items: {$produceditems}");
		
		return $produceditems;
	
	}
	
		
	/**
	* Clona un item
	* @param: nessuno
	* @return: oggetto item copiato
	**/
	
	function cloneitem ( )
	{
								
		$cloneditem = new Item_Model();		
		$cloneditem -> cfgitem_id = $this -> cfgitem_id ;		
		$cloneditem -> character_id = null;
		$cloneditem -> region_id = null;
		$cloneditem -> structure_id = null;
		$cloneditem -> npc_id = null;
		$cloneditem -> seller_id = null;
		$cloneditem -> lend_id = null;
		$cloneditem -> status = $this -> status;
		$cloneditem -> recipient_id = null;
		$cloneditem -> equipped = $this -> equipped;
		$cloneditem -> price = $this -> price;
		$cloneditem -> mindmg = $this -> mindmg;
		$cloneditem -> maxdmg = $this -> maxdmg;
		$cloneditem -> persistent = $this -> persistent;		
		$cloneditem -> defense = $this -> defense;	
		$cloneditem -> quantity = $this -> quantity;
		$cloneditem -> quality = $this -> quality;
		$cloneditem -> salepostdate = null;
		$cloneditem -> tax_citizen = null;
		$cloneditem -> tax_neutral = null;
		$cloneditem -> tax_friendly = null;
		$cloneditem -> tax_allied = null;
		$cloneditem -> sendorder = null;
		$cloneditem -> color = $this -> color;
		$cloneditem -> hexcolor = $this -> hexcolor;		
		$cloneditem -> locked = $this -> locked;
		$cloneditem -> param1 = $this -> param1;
		$cloneditem -> param2 = $this -> param2;
		$cloneditem -> param3 = $this -> param3;		
		$cloneditem -> createddate = date( "Y-m-d H:i:s", time());		
		
		//kohana::log( 'debug', kohana::debug( $cloneditem )); 
		return $cloneditem;
	
	}
	
	public function get_flatitemdata( $item_id )
	{
		$sql = 	"
			select i.id item_id, ci.*, i.*
			from items i, cfgitems ci
			where i.cfgitem_id = ci.id
			and   i.id = " . $item_id ;
		
		$item = Database::instance() -> query( $sql ) -> as_array();
		return $item[0];
			
	}
	
	/**
	* calcola il prezzo reale di un item
	* @param $item oggetto item
	* @param $char oggetto char che compra
	* @param $vat vat da applicare
	* @param $mode: se normal ci si aspetta che l' oggetto item 
	         sia un istanza della classe item altrimenti se è 'light' 
	         l' oggetto item è un vettore
	* @return prezzo		 
	*/
	
	public function compute_realprice( $item, $char, $vat, $mode = 'normal' )
	{
		
		kohana::log('debug', '------ COMPUTE PRICE ------');
		
		if ( $mode == 'normal' )		
			$flatitem = Item_Model::get_flatitemdata( $item -> id );
		else
			$flatitem = $item;
		
		$price = $flatitem -> price;
		
		
		// Applico la tassa a valore aggiunto
		// In caso di dobloni, la tassa non è applicata fino
		// al prezzo di 2 coins.
		
		
		if ( $flatitem -> tag == 'doubloons' )
			$vat = 0;

		kohana::log('debug', "-> Price: {$price}");
		
		$price = $price * ( 100 + $vat ) / 100 ; 
	
		kohana::log('debug', '-> Price after vat: ' . round($price, 2) ); 
		
		return round( $price, 2 );
		
	}		
	
	/**
	* Destroys an item
	* @param none
	* @return none
	*/
	
	function destroy()
	{
		kohana::log('debug', '-> Destroying item no: ' . $this -> id );
		$this -> delete();		
	}
	
	
	/********************************************************************
	* Consuma gli indumenti/items indossati dal char
	*
	* Come configurare le singole azioni:
	* $consumeitems = true/false (abilita o disabilita il consumo)
	*
	* $consumerate = array multidimensionale organizzato in:
	* ['bodypart'] = value
	* Per ogni ruolo è possibile specificare un array di zone del
	* corpo su cui eseguire il consumo.
	*
	* @param  array   $consume_rate     array con i rate di consumo
	* @param  obj     $char             oggetto char su cui eseguire il consumo items
	* @return int     $multiplier       multiplicatore per il pacchetto bonus
	********************************************************************/
	
	public function consume_equipment ($equipment, $char, $multiplier = 1)
	{
		
		
		$equipment_to_consume = array();
		
		// Prelevo il ruolo del char
		
		$role = $char -> get_current_role();
		
		// Check: il char ha un ruolo
		// Check: il ruolo è presente nella lista di quelli da controllare
		
		if ( !is_null($role) and array_key_exists($role->tag, $equipment) )
		{
			$equipment_to_consume = $equipment[$role->tag];
		}
		// Il char non ha un ruolo ma è definito un array
		// per il check di tutti i personaggi
		elseif ( array_key_exists('all', $equipment) )
		{
			$equipment_to_consume = $equipment['all'];
		}
		
		//var_dump($equipment_to_consume); exit;
		
		// Seleziono la parte del corpo. L'array viene prelevato nella modalità 
		// Key (parte del corpo) => Value (rate di consumo)
		
		foreach ( (array) $equipment_to_consume as $bodypart => $info)
		{
			// Carico l'item che il char ha attualmente
			// equippato nella parte del corpo
			$item_equipped = $char->get_bodypart_item($bodypart);

			// Check: esiste un item equipaggiato
			if ( ! is_null($item_equipped) )
			{
				// Calcolo il rateo di consumo
				$rate = Kohana::config('medeur.consume_'.$info['consume_rate']);
				// Consumo l'item in base al rate e la coda azioni
				$item_equipped -> consume ( $rate * $multiplier, 'consume-equipment');
			}
		}
		
	}
	
	/**
	* Helper che stampa una tabella con gli items
	* Per il mercato, sezione buy
	* @param obj $structure Structure_Model
	* @param obj $character Character_Model
	* @param obj $valueaddedtax Tax_Model
	* @param obj $role Character_Role_Model
	* @param array $items
	* @param string $category Categoria Oggetto
	* @return string $html Testo HTML
	*/
	
	static function helper_marketbuylistitems( 
		$structure, 
		$character,
		$role, 
		$valueaddedtax,
		$items, 
		$category = 'all' )	
	{
		
		$html = '';
		
		if ( !isset($items['items'][$category] ) )
			$html .="<div class='center' style='margin-top:20px'>" . kohana::lang('items.noitemfound') . '</div>';
		else
		{
			$html .= "<table class='small'>
			<th colspan='3' class='itemname' width='25%'>" . kohana::lang('items.item') . "</th>";
			
			$html .= "<th class='center' width='7%'>" . kohana::lang('items.sellingprice') . "</th>";
			
			$html .= "<th class='center' width='7%'>" . kohana::lang('global.quantity') . "</th>";
			
			$html .= "<th class='center' width='10%'>". kohana::lang('global.condition'). "</th>";
			
			$html .= "<th class='center' width='20%'>" . kohana::lang('items.sellername') . "</th>";
			
			$html .= "<th class='center' width='20%'>Reserved for</th>";
			
			$html .= "<th class='center' width='10%'>" . kohana::lang('global.quantity'). "</th>";
			
			$html .= "<th class='center' width='10%'>" . kohana::lang('items.totalprice'). "</th>";
			
			if ( !is_null ( $role ) and $role -> tag == 'sheriff' )
				$html .= "<th width='10px'>" .  kohana::lang('global.reason') .	"</th>";
			$html .= "<th></th>";
			
			$itemsweight = 0;
			$k = 0;
			foreach ( $items['items'][$category] as $item ) 				
			{	
			
				// se il recipient_id non è uguale al char id
				// si nasconde l' oggetto a meno che il seller non sia il char
				
				if ( 
					!is_null($item -> recipient_id) 
					and 
					$item -> recipient_id != $character -> id 
					and $item -> seller_id != $character -> id
					)
					continue;
			
				$class = ($k % 2 == 0) ? 'alternaterow_1' : '';
				$title = Item_Model::helper_tooltip( $item, 'market' ); 
				$html .= form::open('/market/marketaction');
				$html .= form::hidden('structure_id', $structure->id );
				$html .= form::hidden('item_id', $item -> id );	
		
				
				$html .= "<tr class='{$class}'  >";
				$html .= 	"<td>" . 
					html::image(
							'media/images/items/' . $item -> tag . '.png',
							array('class' => 'size25',
								'style' => 'vertical-align:middle')
							) . 	
					"</td>";
				
				$html .=  "<td colspan='2' class='itemname' data-description='{$title}'>" . 					
						"<span>" . kohana::lang($item -> name) . '</span>'  . 
					"</td>";
								
				$html .= "<td class='center'><div id='sellingprice_" . $item -> id."'>" . 
					number_format(
						Item_Model::compute_realprice( 
							$item, $character,
							$valueaddedtax, 'light' 
						), 
					2) . "</div></td>"; 	
				
				$html .= "<td class='center'>" . $item -> quantity . "</td>";
				
				if ($item -> quality < 25)
					$class = 'alert';
				else
					$class = 'info';
				
				$html .= "<td class='center $class'>" . Utility_Model::number_format($item -> quality, 2) . "%</td>"; 
				
				// venditore
				$html .= "<td class='center'>".html::anchor('character/publicprofile/' 
					. $item -> seller_id, 	$item -> seller_name) ."</td>";
					
				// destinatario
				$html .= "<td class='center'>" . Character_Model::create_publicprofilelink($item -> recipient_id) . "</td>";
				
				//$html .= "<td class='center'>" . Utility_Model::format_date($item->salepostdate) . "</td>";				
				
				$html .= "<td>" . form::input( array (
					'id'=> 'quantity_'. $item -> id, 	
					'name'=> 'quantity', 		
					'class'=> 'input-xxsmall right',
					'value'=> 1  ) ) . "</td>";	
		
				$html .= "<td class='center'><div id='totalprice_". $item -> id . "'></div></td>"; 
		
				if ( !is_null ( $role ) and $role -> tag == 'sheriff' and $item -> confiscable )
				{
					$html .= "<td class='right'>" . form::textarea(
					array( 
						'id' => 'confiscatereason', 
						'name' => 'confiscatereason', 
						'rows' => 2, 
						'cols' => 10 )) . "</td>";
				}
				
				if ( $item -> seller_id != $character -> id  )
				{
					$html .= "<td class='center'>" . 
						form::submit(
						array (
							'id'=>'submit-buy', 
							'class' => 'button button-small', 								
							'value' => kohana::lang( 'global.buy' ), 			
							'name'=>'buy')) ;
						
					// Lo sceriffo può confiscare oggetti		
					
					if ( !is_null ( $role ) and $role -> tag == 'sheriff' and $item -> confiscable )
						$html .= 
						'<br/>' . 
						form::submit(
						array (
							'id'=>'submit-confiscate', 
							'class' => 'button button-small breakword', 				
							'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')', 
							'value' => kohana::lang( 'global.confiscate' ), 			
							'name'=>'confiscate')) ;
										
					$html .= "</td>";
				}
				else
					$html .= "<td>" . 
					form::submit(	array ('id'=>'submit', 'class' => 'button button-small', 'name'=>'marketcancelsell', 'value'=> kohana::lang('structures.market_cancelsell')))."</td>";		
		
				$html .= '</tr>'; 
		
				$html .= form::close();
				$k++;
			}
			
			$html .= "</tbody></table>";			
			
		}
		
		return $html;
	}

/**
	* Helper che stampa una tabella con gli items
	* per una struttura
	* @param obj $structure Struttura da considerare
	* @param array $items Array di oggetti
	* @param string $mode se si renderizzano oggetti del char o della struttura [structure|character]
	* @param string $category Categoria oggetti da visualizzare
	*/
	
	static function helper_structurelistitems( 
		$structure, 
		$items,
		$mode,
		$category = 'all' )	
	{
		$html = '';
		
		if ( !isset($items['items'][$category] ) )
			$html .="<div class='center' style='margin-top:20px'>" . kohana::lang('items.noitemfound') . '</div>';
		else
		{
			
			$html .= "<table>";
			if ($mode == 'structure')
				$html .= "<th></th>";
			if ($mode == 'character')
				$html .= "<th width='5%'>" . form::checkbox( array( 'id' => 'checkallcharitems' )). "</th>";
			else
				$html .= "<th width='5%'>" . form::checkbox( array( 'id' => 'checkallstructureitems' )). "</th>";
			$html .= "<th width='5%'></th>";
			$html .= "<th width='37%'>" . kohana::lang('items.item'). "</th>";
			$html .= "<th width='5%'>" . kohana::lang('items.condition') . "</th>";
			$html .= "<th width='5%'>" . kohana::lang('global.quantity') . "</th>";
			$html .= "<th width='7%'>" . kohana::lang('items.drop_quantity') . "</th>";
			$html .= "<th width='5%'>" . kohana::lang('items.weight'). "</th>";
			if ($mode == 'character')
				$html .= "<th></th>";
			
			$r = 0;	
			foreach ($items['items'][$category] as $item ) 
			{	
			
				$class = ( $r % 2 == 0 ) ? '' : 'alternaterow_1';
				if ($item -> quality < 25)
					$class = 'poorqualityitem';
				
				// vengono mostrati solo gli item NON indossati
				
				if ( $item -> equipped == 'unequipped' )
				{	
					
					$title = Item_Model::helper_tooltip( $item, 'structureinventory' ); 					
					$html .= "<tr class='itemnamerow {$class}'>";
					if ($mode == 'character' )
						$html .= form::open('/structure/drop');
					else
						$html .= form::open('/structure/take');
					$html .= form::hidden('structure_id', $structure->id );
					$html .= form::hidden('item_id', $item -> item_id );
					$html .= form::hidden('w-' . $item -> item_id, $item -> weight);
					$html .= form::hidden('sc-' . $item -> item_id, $item -> subcategory);
						
					$html .= "<tr class='itemnamerow {$class}'>";
					if ($mode == 'structure' )					
						$html .= "<td class='right'>" . 
							form::submit( 
							array 
							(
								'id' => 'submit', 
								'class' => 'submit', 									
								'value' => '<<' 
							)) . 
							'</td>'; 
					if ($mode == 'character')
						$html .= "<td>" . form::checkbox( array( 'id' => $item -> item_id, 'name' => 'charitemcheckbox', 'value' => $item -> item_id )) . "</td>";					
					else
						$html .= "<td>" . form::checkbox( array( 'id' => $item -> item_id, 'name' => 'structureitemcheckbox', 'value' => $item -> item_id )) . "</td>";					
					$html .= 	"<td>" . 
					html::image(
							'media/images/items/' . $item -> tag . '.png',
							array('class' => 'size25',
								'style' => 'vertical-align:middle')
							) . 	
					"</td>";
					$html .= "<td class='breakword center'>" .					
					"<span class='itemname' data-description='{$title}'>".kohana::lang( $item -> name)."</span></td>";						
					$html .= "<td class='right'>" . Utility_Model::number_format($item -> quality, 2) . "%</td>";
					$html .= "<td class='right'>". $item -> quantity . "</td>";
					$html .= "<td class='right'>" . form::input( 
						array( 
							'class' => 'quantity', 
							'id' => 'q-'. $item -> item_id, 
							'name' => 'quantity', 
							'value' => $item -> quantity,
							'class' => 'input-xxsmall right' ) )
						. "</td>"; 
					$html .= "<td class='right'>" . Utility_Model::number_format( $item -> totalweight/1000,1) . "  </td>"; 
					if ($mode == 'character' )					
						$html .= "<td class='right'>" . 
							form::submit( 
							array 
							(
								'id' => 'submit', 
								'class' => 'submit', 									
								'value' => ' >> ' 
							)) . 
							'</td>'; 
					$html .= form::close();			
					$html .= "</tr>";			
					
					
					$r++;
				}
			}	
			$html .= "</table>";			
		}				
		return $html;
	}	
	
	/**
	* Helper che stampa una tabella con gli items
	* Per la inventory del personaggio
	* @param obj $char Character_Model
	* @param array $items
	* @return string $html Testo HTML
	*/
	
	static function helper_characterlistitems( 
	$character,
	$items, 
	$category = 'consumables' )
	{
		$html = '';
				
		if ( !isset($items['items'][$category] ) )
			$html .="<div class='center' style='margin-top:20px'>" . kohana::lang('items.noitemfound') . '</div>';
		else
		{
			$html .= "<table class='small border'>";
			$html .= "<th class='center' width='50%' colspan='2'>".kohana::lang('items.item')."</th>";	
			$html .= "<th class='center' width='15%'>" . ucwords(kohana::lang('items.equipped')) . "</th>";	
			$html .= "<th class='center' width='5%'>N.</th>";	
			$html .= "<th class='center' width='5%'>". kohana::lang('global.condition')."</th>";	
			$html .= "<th class='center' width='25%'>".kohana::lang('items.weight')."</th>";	
			
			$k = 0;
			foreach ( $items['items'][$category] as $item )
			{		
				$class = ( $k %2 == 0 ) ? 'alternaterow_1' : 'alternaterow2' ;
				$title = Item_Model::helper_tooltip( $item, 'inventory' ); 
								
				$html .= "<tr class='itemnamerow $class'>";
				
				$html .= "<td width='10%'>". html::image(
					'media/images/items/'. $item -> tag.'.png',
					array('class' => 'size25')
					) . "</td>";
				
				// Se l'oggetto è uno scroll generico allora visualizzo il titolo al posto del
				// nome dell'oggetto
				
				if ( $item -> category=='scroll' && $item -> subcategory=='generic')
				{ $html .= "<td><span class='itemname' data-description='{$title}'>".substr($item -> param1,0,40)."...</span></td>"; } 
				else
				{ 		
					$html .= "<td><span class='itemname' data-description='{$title}'>" . kohana::lang($item -> name) . "</span></td>";								
				}
				
				$html .= "<td class='center'>";
				if ( $item -> equipped != 'unequipped' )
					$html .= kohana::lang('items.' . $item -> equipped);
				$html .= "</td>";
				$html .= "<td style='text-align:right'>".$item -> quantity . "</td>";
				if ($item -> quality < 25)
					$class = 'poorqualityitem';				
				$html .= "<td style='text-align:right' class='$class'>". Utility_Model::number_format($item -> quality, 2) . "%</td>";
				$html .= "<td style='text-align:right'>".number_format($item -> totalweight/1000,1) . " Kg.</td>";
				
				$html .= "</tr>";
				$k++;
			}
			
			$html .= "</table>";
		}
		
		return $html;
		
	}
	
	/**
	* Helper che stampa una tabella con gli items
	* Per il mercato, sezione sale
	* #param obj $structure Structure_Model
	* @param array $items
	* @param string $category Categoria Oggetto
	* @return string $html Testo HTML
	*/
	
	static function helper_marketsalelistitems(	$structure, $items, $category = 'all' )	
	{
		$html = "";
				
		if ( !isset($items['items'][$category] ) )
			$html .="<div class='center' style='margin-top:20px'>" . kohana::lang('items.noitemfound') . '</div>';
		else
		{
			$html .=
			"<br/>
			<table class='small'>"
			. "<th width='10%'>" . kohana::lang('global.quantity'). "</th>" 
			. "<th width='25%' colspan='3'>" . kohana::lang('items.item') . "</th>" 
			. "<th width='10%'>" . kohana::lang('items.sell_quantity'). "</th>"
			. "<th width='20%'>" . kohana::lang('items.salerecipient'). "</th>"
			. "<th width='10%'>" . kohana::lang('global.price'). "</th>" 
			. "<th width='20%'>" . kohana::lang('items.sellingprice') . "</th>"
			.  "<th></th>";
			
			$k = 0;
			foreach ( $items['items'][$category] as $items ) 				
			{
				
				if ( $items -> marketsellable and $items -> equipped == 'unequipped' )
				{	
					
					$class = ($k % 2 == 0) ? 'alternaterow_1' : '';
					$title = Item_Model::helper_tooltip( $items, 'market' ); 					
					$html .=  form::open('/market/sell');
					$html .=  form::hidden('structure_id', $structure->id );	
					$html .=  form::hidden('item_id', $items -> item_id );	
					
					if ($items -> quality < 25)
						$class .= ' poorqualityitem';
					
					$html .=  "<tr class='{$class}'>";					
					$html .=  "<td class='center'>".$items -> quantity . "</td>";
					$html .= "<td>" . 
					html::image(
							'media/images/items/' . $items -> tag . '.png',
							array('class' => 'size25',
								'style' => 'vertical-align:middle')
							) .
					"</td>";
					$html .=  "<td colspan='2' class='itemname' data-description='{$title}'>" .						
						"<span>" . kohana::lang($items -> name) . '</span>' . 	
					"</td>";
					
					$html .=  "<td class='center'>" . form::input( 
					array (
						'id'=>'quantity_'.$items -> item_id, 
						'name'=>'quantity', 												
						'class'=> 'input-xxsmall right',
						'value'=> 1  ) ) . "</td>";					
					$html .=  "<td class='center'>" . 
						form::input( 
							array (
								'id' => 'recipient_'.$items -> item_id, 
								'name' => 'recipient', 								
								'class' => 'input-medium left character'
							) 
						) . "</td>";
					$html .=  "<td class='center'>" . form::input( array (
						'id'=>'sellingprice_'.$items -> item_id, 
						'name'=>'sellingprice', 
						'data' => $items -> tag,
						'class'=> 'input-xsmall right'			
						) ) . "</td>";	
					
					$html .= "<td class='center'>";
					
					if ( $items -> taxable == true )
					{
						$html .= "<div id='totalprice_".$items -> item_id."_citizen'></div>";
						$html .= "<div id='totalprice_".$items -> item_id."_neutral'></div>";
						$html .= "<div id='totalprice_".$items -> item_id."_friendly'></div>";
						$html .= "<div id='totalprice_".$items -> item_id."_allied'></div>";						
					}
					
					else
					{
						$html .=  "-";						
					
					}
					
					$html .= "</td>";
					
					$html .=  "<td class='center'>" . form::submit( array (
						'id' => 'submit', 
						'class' => 'button button-xsmall'),
						kohana::lang('global.sell')) .
					"</td>";
					$html .=  "</tr>";
					$html .=  form::close();
					$k++;
				}
			}
			$html .= "</table>";
		}		
		return $html;
	}
}
