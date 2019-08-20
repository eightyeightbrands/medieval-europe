<script type="text/javascript">	
$(document).ready(function() {			
		
	var charinventorylasttab = localStorage.getItem("charinventorylasttab");	
	if ( charinventorylasttab === null )
		charinventorylasttab = '#tab-all';
	
	$( ".itemdata").hide();		
	$( charinventorylasttab ).show();
	
	$("#dialog")
	.dialog({
		autoOpen: false,
		title: 'Info',				
		dialogClass: 'myuidialog',
		closeOnEscape: true,
		modal:false,		
	});

	$("#categorytabs img").click( function(e) {
		e.preventDefault();			
		localStorage.setItem("charinventorylasttab", '#' + $(this).data('link'));		
		$(".itemdata").hide();
		$('#'+$(this).data('link')).show();
	});
		
	$(".itemname").click(function() 
	{		
		var target = $(this);        
		$('#dialog').html($(this).data('description'));				
		$("#dialog").dialog('option', 'title', 'Item');		
		$("#dialog").dialog("option", "position", {		  		  
		  my: "left bottom",
		  at: "top right",
		  of: target,
		}).dialog("open");		
	});
	
});
</script>

<div class="pagetitle"><?php echo kohana::lang('character.inventory_pagetitle') ?></div>

<?php echo $submenu ?>	

<div class='center'>
	<?php	echo html::image(array('src' => 'media/images/template/hruler.png')); ?>
</div>

<br/>
<div id="dialog"></div>
<div>

	<div style='width:50%;float:left;margin-left:2%'>
		<div class='center'>
			<?= html::anchor('/character/unequip_all', 'Unequip all',
			array(
			'class' => 'button button-medium',
			'style' => 'display:inline-block;margin-bottom:2px')); ?>
		</div>
		
		<div style='margin-top:10px' class='center' id='tab-base' >	
			<ul id="categorytabs">
			<li>
			<?= 
			html::image('media/images/template/all.png',
			array(
				'class' => 'size25',
				'title' => kohana::lang('items.category_all'),
				'data-link' => 'tab-all'
			)
			)		
			?>		
			</li>
			<li>
			<?= 
			html::image('media/images/items/silvercoin.png',
			array(
				'class' => 'size25',
				'title' => kohana::lang('items.category_currencies'),
				'data-link' => 'tab-currencies'
			)
			)		
			?>								
			</li>
			<li>
			<?= 
			html::image('media/images/items/bread.png',
			array(
				'class' => 'size25',
				'title' => kohana::lang('items.category_consumables'),
				'data-link' => 'tab-consumables'
			)
			)		
			?>	
			</li>

			<li>
			<?= 
			html::image('media/images/items/wood_piece.png',
			array(
				'class' => 'size25',
				'title' => kohana::lang('items.category_resources'),
				'data-link' => 'tab-resources'
			)
			)		
			?>	
			</li>
			<li>
			<?= 
			html::image('media/images/items/hammer.png',
			array(
				'class' => 'size25',
				'title' => kohana::lang('items.category_tools'),
				'data-link' => 'tab-tools'
			)
			)		
			?>			
			</li>
			<li>
			<?= 
				html::image('media/images/items/distiller.png',
				array(
					'class' => 'size25',
					'title' => kohana::lang('items.category_structuretool'),				
					'data-link' => 'tab-structuretool'
				)
			)			
			?>
			</li>	
			<li>
			<?= 
			html::image('media/images/items/halberd.png',
			array(
				'class' => 'size25',
				'title' => kohana::lang('items.category_weapons'),
				'data-link' => 'tab-weapons'
			)
			)		
			?>
			</li>			
			<li>
			<?= 
			html::image('media/images/items/chainmail_armor_shield.png',
			array(
				'class' => 'size25',
				'title' => kohana::lang('items.category_armors'),
				'data-link' => 'tab-armors'
			)
			)		
			?>
			</li>
			<li>
			<?= 
			html::image('media/images/items/hat_cairo.png',
			array(
				'class' => 'size25',
				'title' => kohana::lang('items.category_clothes'),
				'data-link' => 'tab-clothes'
			)
			)		
			?>	
			</li>

			<li>
			<?= 
			html::image('media/images/items/scroll_generic.png',
			array(
				'class' => 'size25',
				'title' => kohana::lang('items.category_scrolls'),
				'data-link' => 'tab-scrolls'
			)
			)		
			?>	
			</li>

			<li>
			<?= 
			html::image('media/images/template/other.png',
			array(
				'class' => 'size25',
				'title' => kohana::lang('items.category_others'),
				'data-link' => 'tab-others'
			)
			)		
			?>	
			</li>
			</ul>
			
			<div id='tab-all' class='itemdata'>
			<?= Item_Model::helper_characterlistitems($character, $items,  'all');?>
			</div>
			
			<div id='tab-currencies' class='itemdata'>
			<?= Item_Model::helper_characterlistitems($character, $items, 'currencies');?>
			</div>
			
			<div id='tab-consumables' class='itemdata'>
			<?= Item_Model::helper_characterlistitems($character, $items,  'consumables');?>
			</div>						
			
			<div id='tab-resources' class='itemdata'>
			<?= Item_Model::helper_characterlistitems($character, $items,  'resources');?>
			</div>
			
			<div id='tab-tools' class='itemdata'>
			<?= Item_Model::helper_characterlistitems($character, $items,  'tools');?>
			</div>
			
			<div id='tab-structuretool' class="itemdata">
			<?= Item_Model::helper_characterlistitems($character, $items, 'structuretool');?>
			</div>
			
			<div id='tab-weapons' class='itemdata'>
			<?= Item_Model::helper_characterlistitems($character, $items,  'weapons');?>
			</div>
		
			<div id='tab-armors' class='itemdata'>
			<?= Item_Model::helper_characterlistitems($character, $items,  'armors');?>			
			</div>
			
			<div id='tab-clothes' class='itemdata'>
			<?= Item_Model::helper_characterlistitems($character, $items,  'clothes');?>			
			</div>
			
			<div id='tab-scrolls' class='itemdata'>
			<?= Item_Model::helper_characterlistitems($character, $items,  'scrolls');?>			
			</div>
			
			<div id='tab-others' class='itemdata'>
			<?= Item_Model::helper_characterlistitems($character, $items,  'others');?>			
			</div>
			
		</div>

	</div>

	<div style='width:44%;margin-left:2%;margin-right:2%;float:left'>			
	<fieldset id="characterstatistics">
		<legend><?php echo kohana::lang('character.stats');?></legend>
		<?php
		echo kohana::lang('character.basecharweightcapacity') .  ": <span class='value'>" . number_format($char_baseweightcapacity/1000,1) . " Kg.</span>" . '<br/>';
		echo kohana::lang('character.charweightcapacity') .  ": <span class='value'>" . number_format($char_maxweightcapacity/1000,1) . " Kg.</span>" . '<br/>';
		echo kohana::lang('character.inventory_totalweight').": <span class='value'>" . number_format($char_transportedweight/1000,1) . " Kg.</span>". '<br/>';
		$char_weightcapacity = ( $char_maxweightcapacity - $char_transportedweight );
		if ( $char_weightcapacity <= 0 ) 
		{	
			$char_weightcapacity = 0 ;		
			$color = '#cc0000';
		}
		else
			$color = '#347C2C';
		
		echo kohana::lang('character.charleftweightcapacity').": <span class=\'value\'><font color='" . $color . "'>" . number_format($char_weightcapacity/1000,1)." Kg.</font></span>" . '<br/>';
		?>
		<?php
		
		
		echo Kohana::lang( 'items.damage') . ': <span class=\'value\'>' . $charcopy['char']['wpn_mindamage'] . '-' . $charcopy['char']['wpn_maxdamage'] . '</span>';
		echo '<br/>';
		
		echo Kohana::lang( 'character.encumbrance') . ': <span class=\'value\'>' . $encumbrance . '%</span>';
		echo '<br/>';
		
		echo Kohana::lang( 'battle.encumbrance') . ': <span class=\'value\'>' . $charcopy['char']['armorencumbrance'] . '%</span>';
		echo '<br/>';
		
		foreach ( array( 'head', 'torso', 'left_hand', 'right_hand', 'legs', 'feet' ) as $part )
			if (isset($partinfo[$part]))
				echo kohana::lang('items.defense') . ' ' . 
					kohana::lang('battle.part_' . $part ) .  ": <span class=\'value\'>" . 
					$partinfo[$part]['totaldefense'] . "</span></br>";
			else
				echo kohana::lang('items.defense') . ' ' . 
					kohana::lang('battle.part_' . $part ) .  ": <span class=\'value\'>" . 
					0 . "</span></br>";
		
		?>
		</fieldset>
		
		
		<div class ='submenu center' class='center' style='margin:5px 0px;'> 
		<?php
			if ( Character_Model::get_premiumbonus( $character -> id, 'wardrobe') !== false )
				echo html::anchor( 'wardrobe/configureequipment', kohana::lang('wardrobe.configurelook'));
		?>
		</div>		
		<div id='charpic' class='center'>
		<?php 			
			$character -> render_char ( $equippeditems, 'wardrobe', 'medium' ); 
		?>
		</div>
		
	</div>
</div>

<br style="clear:both;" />
