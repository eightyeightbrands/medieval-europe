<head>
<script type='text/javascript'>
$(document).ready(function(){ 
	
	
	$("#dialog")
	.dialog({
		autoOpen: false,
		title: 'Info',				
		dialogClass: 'myuidialog',
		closeOnEscape: true,
		modal:false,		
	});

	/*$('[title]').tooltipster({
		theme: 'tooltipster-borderless',
		contentAsHTML: true,
		maxWidth: 400,
		trigger: 'click',
		interactive: true,
		}
	),
	*/
	$("#customtabs").tabs({
		active: localStorage.getItem("marketbuylasttab"),
		beforeActivate: function(event, ui) {
			$(this).data('scrollTop', $(window).scrollTop()); // save scrolltop
		},
		activate: function(event, ui) {			
			localStorage.setItem("marketbuylasttab",ui.newTab.index() );			
			if (!$(this).data('scrollTop')) { // there was no scrolltop before
				jQuery('html').css('height', 'auto'); // reset back to auto...			
				return;
			}
			if ($(window).scrollTop() == $(this).data('scrollTop')) 
				return;
			var min_height = $(this).data('scrollTop') + $(window).height();
			if ($('html').outerHeight() < min_height) {
				$('html').height(min_height - ($('html').outerHeight() - $('html').height()));
			}
			$(window).scrollTop($(this).data('scrollTop'));
            

		}			
	}),	
	$("input[id^='quantity']").blur(function(event){	    
		$v = $(this).attr("id").split("_");
		$item_id = $v[1];
		quantity = $(this).val();
		//console.log('N.: ' + quantity);
		var price = $("#sellingprice_"+$item_id).text();
		//console.log('Price: ' + price );
		totalprice = quantity * price;		
		$("#totalprice_"+$item_id).text( totalprice );
	}),
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
</head>
<div id="dialog"></div>
<div class="pagetitle"><?php echo kohana::lang($structure->region->name) . " - " . kohana::lang( $structure->structure_type->name ) ?></div>

<?php echo $submenu ?>

<p>
<?php echo kohana::lang('structures.carryingweightcapacityleft', Utility_Model::number_format($char_transportableweight/1000, 1 ) ); ?>
<br/>
<?php echo kohana::lang('structures_market.valueaddedtax', $valueaddedtax ) ?>
<br/>
<?php echo kohana::lang('structures_market.currentcategory', kohana::lang('items.category_' . $currentcategory ) ) ?>
</p>

<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<div id="customtabs">
	<ul id="containertabs">		
		<li>
		<?= html::anchor(
			'#tab-all',
			html::image(
				'media/images/template/all.png',
				array(
					'class' => 'size25',
					'title' => kohana::lang('items.category_all'),					
				) 
			)
		);
		?>
		</li>
		<li>
		<?= html::anchor(
			'#tab-currencies',
			html::image(
				'media/images/items/silvercoin.png',
				array(
					'class' => 'size25',
					'title' => kohana::lang('items.category_currencies'),					
				) 
			)			
		);
		?>
		</li>
		<li>
		<?= html::anchor(
			'#tab-consumables',
			html::image(
				'media/images/items/bread.png',
				array(
					'class' => 'size25',
					'title' => kohana::lang('items.category_consumables'),					
				)
			)
		);
		?>
		</li>
		
		<li>
		<?= html::anchor(
			'#tab-resources',
			html::image(
				'media/images/items/wood_piece.png',
				array(
					'class' => 'size25',
					'title' => kohana::lang('items.category_resources'),					
				) 
			)
		);
		?>
		</li>
		
		<li>
		<?= html::anchor(
			'#tab-tools',
			html::image('media/images/items/hammer.png',
			array(
				'class' => 'size25',
				'title' => kohana::lang('items.category_tools'),				
			)
		)
		);
		?>
		</li>
		<li>
		<?= html::anchor(
			'#tab-structuretool',
			html::image('media/images/items/distiller.png',
			array(
				'class' => 'size25',
				'title' => kohana::lang('items.category_structuretool'),				
			)
		)
		);
		?>
		</li>		
		<li>
		<?= html::anchor(
			'#tab-weapons',
			html::image('media/images/items/halberd.png',
			array(
				'class' => 'size25',
				'title' => kohana::lang('items.category_weapons'),				
			)
		)		
		);
		?>
		</li>			
		<li>
		<?= html::anchor(
			'#tab-armors',
			html::image('media/images/items/chainmail_armor_shield.png',
			array(
				'class' => 'size25',
				'title' => kohana::lang('items.category_armors'),			
			)
		)	
		);
		?>
		</li>
		<li>
		<?= html::anchor(
			'#tab-clothes',
			html::image('media/images/items/hat_cairo.png',
			array(
				'class' => 'size25',
				'title' => kohana::lang('items.category_clothes'),				
			)
			)
		);
		?>
		</li>
		
		<li>
		<?= html::anchor(
			'#tab-scrolls',
			html::image('media/images/items/scroll_generic.png',
			array(
				'class' => 'size25',
				'title' => kohana::lang('items.category_scrolls'),				
			)
		)	
		);
		?>
		</li>
		
		<li>
		<?= html::anchor(
			'#tab-others',
			html::image('media/images/template/other.png',
			array(
				'class' => 'size25',
				'title' => kohana::lang('items.category_others'),				
			)
		)	
		);
		?>
		</li>
	</ul>
	
	<div id='tab-all'>
	<?= Item_Model::helper_marketbuylistitems($structure, $character, $role, $valueaddedtax, $items, 'all');?>
	</div>
	
	<div id='tab-currencies'>
	<?= Item_Model::helper_marketbuylistitems($structure, $character, $role, $valueaddedtax, $items, 'currencies');?>
	</div>
	
	<div id='tab-consumables'>
	<?= Item_Model::helper_marketbuylistitems($structure, $character, $role, $valueaddedtax, $items, 'consumables');?>
	</div>
	
	
	<div id='tab-resources'>
	<?= Item_Model::helper_marketbuylistitems($structure, $character, $role, $valueaddedtax, $items, 'resources');?>
	</div>
	
	<div id='tab-tools'>
	<?= Item_Model::helper_marketbuylistitems($structure, $character, $role, $valueaddedtax, $items, 'tools');?>
	</div>
	
	<div id='tab-structuretool' class="itemdata">
	<?= Item_Model::helper_marketbuylistitems($structure, $character, $role, $valueaddedtax, $items, 'structuretool');?>
	</div>
	
	<div id='tab-weapons'>
	<?= Item_Model::helper_marketbuylistitems($structure, $character, $role, $valueaddedtax, $items, 'weapons');?>
	</div>
	
	<div id='tab-armors'>
	<?= Item_Model::helper_marketbuylistitems($structure, $character, $role, $valueaddedtax, $items, 'armors');?>
	</div>

	<div id='tab-clothes'>
	<?= Item_Model::helper_marketbuylistitems($structure, $character, $role, $valueaddedtax, $items, 'clothes');?>
	</div>
	
	<div id='tab-scrolls'>
	<?= Item_Model::helper_marketbuylistitems($structure, $character, $role, $valueaddedtax, $items, 'scrolls');?>
	</div>
	
	<div id='tab-others'>
	<?= Item_Model::helper_marketbuylistitems($structure, $character, $role, $valueaddedtax, $items, 'others');?>
	</div>
	
</div>

<br style="clear:both;" />
