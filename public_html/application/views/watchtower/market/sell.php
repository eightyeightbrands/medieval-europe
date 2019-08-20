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
	
	$(".character").autocomplete({
			source: "index.php/jqcallback/listallchars",
			minLength: 2
		});	
		
	$("#customtabs").tabs({
		active: localStorage.getItem("marketselllasttab"),
		beforeActivate: function(event, ui) {
			$(this).data('scrollTop', $(window).scrollTop()); // save scrolltop
		},
		activate: function(event, ui) {
			localStorage.setItem("marketselllasttab",ui.newTab.index() );
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
	});
	
	$("input[id^='quantity']").blur(function(event)
	{
			$v = $(this).attr("id").split("_");
			$charitems_id = $v[1];			
		
		if ( $(this).val() * $("#sellingprice_"+$charitems_id).val() > 1000000 )
		{
			alert( "<?php echo kohana::lang( 'structures.maxsellingpricereached');?>" );
			this.value = '1';
			$("#totalprice_"+$charitems_id).text('');
			return false;
		}
		 $("#totalprice_"+$charitems_id).text( $(this).val() * $("#sellingprice_"+$charitems_id).val() );
	});
	
	$("input[id^='sellingprice']").blur(function(event)
	{
		
		$v = $(this).attr("id").split("_");
		$charitems_id = $v[1];
		$tag = $(this).attr("data");

		if ( $(this).val() * $("#quantity_"+$charitems_id).val() > 1000000 )
		{
			alert( "<?php echo kohana::lang( 'structures.maxsellingpricereached'); ?>" );
			this.value = '';
			$("#totalprice_"+$charitems_id).text('');
			return false;
		}
		
		if ( $tag == 'doubloon' && $(this).val() <= 2 )
		{
		
			totalprice_citizen = $(this).val() * 1.0;			
			totalprice_neutral = $(this).val()* 1.0;			
			totalprice_friendly =  $(this).val()* 1.0;			
			totalprice_allied = $(this).val()* 1.0;			
		}
		else
		{
			console.log("Base Price is: " + $(this).val());
			totalprice_citizen = $(this).val() * ($("#quantity_"+$charitems_id).val()) * 
				(100 + <?php echo $valueaddedtax -> citizen; ?>)/100;
			totalprice_neutral = $(this).val()  * ($("#quantity_"+$charitems_id).val()) * (100 + <?php echo $valueaddedtax -> neutral; ?>)/100;
			totalprice_friendly = $(this).val() * ($("#quantity_"+$charitems_id).val()) * (100 + <?php echo $valueaddedtax -> friendly; ?>)/100;
			totalprice_allied = $(this).val()   * ($("#quantity_"+$charitems_id).val()) * (100 + <?php echo $valueaddedtax -> allied; ?>)/100;	
		}	
		
		console.log("Prize for Citizens: " + totalprice_citizen);
		
		$("#totalprice_"+$charitems_id + "_citizen").text( "Citizens: " + totalprice_citizen.toFixed(2) );
		$("#totalprice_"+$charitems_id + "_neutral").text( "Neutral: " + totalprice_neutral.toFixed(2) );
		$("#totalprice_"+$charitems_id + "_friendly").text( "Friendly: " + totalprice_friendly.toFixed(2) );
		$("#totalprice_"+$charitems_id + "_allied").text( "Allied: " + totalprice_allied.toFixed(2) );

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
</head>
<div id="dialog"></div>
<div class="pagetitle">
<?php echo kohana::lang($structure->region->name) . " - " . kohana::lang( $structure->structure_type->name ) ?>
</div>

<?php echo $submenu ?>

<p>
<?php echo kohana::lang('structures.carryingweightcapacityleft', Utility_Model::number_format($char_transportableweight/1000, 1 ) ); ?>
<br/>

<?php echo kohana::lang( 'structures_market.valueaddedtaxall', 
	$valueaddedtax -> citizen,
	$valueaddedtax -> neutral,
	$valueaddedtax -> friendly,
	$valueaddedtax -> allied
); ?>
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
	
	<div id='tab-all' class="itemdata">
	<?= Item_Model::helper_marketsalelistitems($structure, $charitems, 'all');?>
	</div>
	
	<div id='tab-currencies' class="itemdata">
	<?= Item_Model::helper_marketsalelistitems($structure, $charitems, 'currencies');?>
	</div>
	
	<div id='tab-consumables' class="itemdata">
	<?= Item_Model::helper_marketsalelistitems($structure, $charitems, 'consumables');?>
	</div>						
	
	<div id='tab-resources' class="itemdata">
	<?= Item_Model::helper_marketsalelistitems($structure, $charitems, 'resources');?>
	</div>
	
	<div id='tab-tools' class="itemdata">
	<?= Item_Model::helper_marketsalelistitems($structure, $charitems, 'tools');?>
	</div>
	
	<div id='tab-structuretool' class="itemdata">
	<?= Item_Model::helper_marketsalelistitems($structure, $charitems, 'structuretool');?>
	</div>
	
	<div id='tab-weapons' class="itemdata">
	<?= Item_Model::helper_marketsalelistitems($structure, $charitems, 'weapons');?>
	</div>

	<div id='tab-armors' class="itemdata">
	<?= Item_Model::helper_marketsalelistitems($structure, $charitems, 'armors');?>			
	</div>
	
	<div id='tab-clothes' class="itemdata">
	<?= Item_Model::helper_marketsalelistitems($structure, $charitems, 'clothes');?>			
	</div>
	
	<div id='tab-scrolls' class="itemdata">
	<?= Item_Model::helper_marketsalelistitems($structure, $charitems, 'scrolls');?>			
	</div>
	
	<div id='tab-others' class="itemdata">
	<?= Item_Model::helper_marketsalelistitems($structure, $charitems, 'others');?>			
	</div>
			
</div>

<br style="clear:both;" />