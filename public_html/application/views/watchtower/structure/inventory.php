<head>
<script type="text/javascript">
$(document).ready(function()
{
	var structureinventorylasttab = localStorage.getItem("structureinventorylasttab");
	if ( structureinventorylasttab === null )
		structureinventorylasttab = 'tab-all';
	
	$( ".itemdata").hide();	
	$("#c-" + structureinventorylasttab + ", #s-" + structureinventorylasttab).show();	
		
	$('#categorytabs img').click( function(e) {		
		e.preventDefault();			
		localStorage.setItem("structureinventorylasttab", $(this).data('link'));
		$(".itemdata").hide();
		$('#c-'+$(this).data('link')).show();
		$('#s-'+$(this).data('link')).show();
	});
	
	$(".quantity").blur(function(event){	
		$(this).val($(this).val()); 
	}), 
	
	$('#checkallcharitems').click(function()
	{
	 $('[name="charitemcheckbox"]').attr('checked', $('#checkallcharitems').is(':checked'));    
	}),
	
	$('#checkallstructureitems').click(function()
	{
	 $('[name="structureitemcheckbox"]').attr('checked', $('#checkallstructureitems').is(':checked'))    
	}),	
	
	$('#massdeposit, #masswithdrawal').click(function (e)
	{				
		e.preventDefault();
		var data = { 'items' : [], 'structureid': []}; 
		var item = {};		
		var structureid = $('[name=structure_id]').val();
		
		if ( this.id == 'massdeposit' )
		{
			console.log('massdeposit');
			$('[name="charitemcheckbox"]:checked').each(function() 
			{ 
				
				item = {
					id: $(this).val(),
					quantity: $("#q-"+$(this).val()).val(),
					weight: $("[name=w-"+$(this).val()+"]").val(),
					subcategory: $("[name=sc-"+$(this).val()+"]").val(),
				}
				
				data['items'].push(item);
			});
			
			data['action'] = 'drop';
		}
		else
		{
			console.log('masswithdrawal');			
			$('[name="structureitemcheckbox"]:checked').each(function() 
			{ 
				console.log('adding:' + $(this).val());
				item = {
					id: $(this).val(),
					quantity: $("#q-"+$(this).val()).val(),
					weight: $("[name=w-"+$(this).val()+"]").val(),
					subcategory: $("[name=sc-"+$(this).val()+"]").val(),
				}
				
				data['items'].push(item);
				
			});
			
			data['action'] = 'take';
		}
		
		data['structureid'].push(structureid);
		
		$('input[name=itemstotransfer]').val( JSON.stringify(data));
		$('form#massitemtransfer').submit();
		
	});
	
	// Costruisco Dialog
	
	$("#dialog")
	.dialog({
		autoOpen: false,
		title: 'Info',				
		closeOnEscape: true,
		dialogClass: 'myuidialog',
		modal:false,		
	});
	
	// Se si clicca, il dialog viene chiuso.
	
	$('body')
		.bind(
		'click',
			function(e){
				
				if(
					$('#dialog').dialog('isOpen')
					&& 
					!$(e.target).is('.ui-dialog, .itemname')
					&& 
					!$(e.target).closest('.ui-dialog').length
				)
				{					
					$('#dialog').dialog('close');
				}
		});

	$(".itemname").click(function() 
	{				
		$('#dialog').html($(this).data('description'));		
		$('#dialog').dialog('open');
	});		
});

</script>

</head>
<div id="dialog"></div>

<div class="pagetitle"><?php echo $structure -> getName()?></div>

<?php echo $submenu ?>
<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<div id='helper'><?php echo kohana::lang('structures.inventory_helper', $structure->id ); ?></div>

<table class='small'>
<tr>
<td colspan="2" class="center">
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
</td>

</tr>

<tr>
	<?php echo form::open('structure/massitemtransfer', array( 'id' => 'massitemtransfer' ) )?>
	<?php echo form::hidden('itemstotransfer') ?> 
		<td width='50%' class="center">
		<?php echo form::submit( 
			array( 
			'id' => 'massdeposit', 
			'value' => kohana::lang('structures.massdeposititems'), 
			'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')'
			)); 
		?>
		</td>
		<td width='50%' class="center">
		<?php echo form::submit( 
			array( 
			'id' => 'masswithdrawal', 
			'value' => kohana::lang('structures.masswithdrawalitems'), 
			'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')'
			)); 
		?>
		</td>
	<?php echo form::close() ?>
</tr>

<tr>
	<td width='50%' class="right">
		<?
			echo kohana::lang('character.charweightcapacity') . ": <span class='value'>" . 
				Utility_Model::number_format($char_maxweightcapacity/1000,1) . 'Kg.' . '</span>';
		?>
		<br/>
		<?
			echo kohana::lang('character.inventory_totalweight') . ": <span class='value'>" . 
				Utility_Model::number_format(($char_transportedweight)/1000,1) . 'Kg.'. '</span>';
		?>
		<br/>
		<?
			$charweightcapacity = ($char_maxweightcapacity-$char_transportedweight)/1000;
			if ($charweightcapacity < 0 )	
				$charweightcapacity = 0;
			echo kohana::lang('character.charleftweightcapacity') . ": <span class='value'>" . 
				Utility_Model::number_format($charweightcapacity,1) . 'Kg.' . '</span>';
		?>		
	</td>
	<td width='50%' class="right">
		<?
			echo kohana::lang('structures.weightcapacity') . ": <span class='value'>" . 
				Utility_Model::number_format($structure_maxweightcapacity/1000,1) . 'Kg.' . '</span>';
		?>
		<br/>
		<?
			echo kohana::lang('structures.totalweight') . ": <span class='value'>" . 
				Utility_Model::number_format(($structure_maxweightcapacity-$structure_weightcapacity)/1000,1) . 'Kg.'. '</span>';
		?>
		<br/>
		<?
			echo kohana::lang('structures.leftweightcapacity')  . ": <span class='value'>" . 
				Utility_Model::number_format($structure_weightcapacity/1000,1) . 'Kg.'. '</span>';
		?>
	</td>
</tr>

<tr>

	<td valign='top'>
		<div id='c-tab-all' class='itemdata'>	
			<?= Item_Model::helper_structurelistitems($structure, $char_items, 'character', 'all');?>
		</div>
		
		<div id='c-tab-currencies' class='itemdata'>
			<?= Item_Model::helper_structurelistitems($structure, $char_items, 'character', 'currencies');?>
		</div>
		<div id='c-tab-consumables' class='itemdata'>
			<?= Item_Model::helper_structurelistitems($structure, $char_items, 'character', 'consumables');?>
		</div>
		<div id='c-tab-resources' class='itemdata'>
			<?= Item_Model::helper_structurelistitems($structure, $char_items, 'character', 'resources');?>
		</div>
		<div id='c-tab-tools' class='itemdata'>
			<?= Item_Model::helper_structurelistitems($structure, $char_items, 'character', 'tools');?>
		</div>
		<div id='c-tab-structuretool' class='itemdata'>
			<?= Item_Model::helper_structurelistitems($structure, $char_items, 'character', 'structuretool');?>
		</div>
		<div id='c-tab-weapons' class='itemdata'>
			<?= Item_Model::helper_structurelistitems($structure, $char_items, 'character', 'weapons');?>
		</div>
		<div id='c-tab-armors' class='itemdata'>
			<?= Item_Model::helper_structurelistitems($structure, $char_items, 'character', 'armors');?>
		</div>
		<div id='c-tab-clothes' class='itemdata'>
			<?= Item_Model::helper_structurelistitems($structure, $char_items, 'character', 'clothes');?>
		</div>
		<div id='c-tab-scrolls' class='itemdata'>
			<?= Item_Model::helper_structurelistitems($structure, $char_items, 'character', 'scrolls');?>
		</div>
		<div id='c-tab-others' class='itemdata'>
			<?= Item_Model::helper_structurelistitems($structure, $char_items, 'character', 'others');?>
		</div>
	</td>
	
	<td valign='top'>
		<div id='s-tab-all' class='itemdata'>	
			<?= Item_Model::helper_structurelistitems($structure, $structure_items, 'structure', 'all');?>
		</div>
		
		<div id='s-tab-currencies' class='itemdata'>
			<?= Item_Model::helper_structurelistitems($structure, $structure_items, 'structure', 'currencies');?>
		</div>
		<div id='s-tab-consumables' class='itemdata'>
			<?= Item_Model::helper_structurelistitems($structure, $structure_items, 'structure', 'consumables');?>
		</div>
		<div id='s-tab-resources' class='itemdata'>
			<?= Item_Model::helper_structurelistitems($structure, $structure_items, 'structure', 'resources');?>
		</div>
		<div id='s-tab-tools' class='itemdata'>
			<?= Item_Model::helper_structurelistitems($structure, $structure_items, 'structure', 'tools');?>
		</div>
		<div id='s-tab-structuretool' class='itemdata'>
			<?= Item_Model::helper_structurelistitems($structure, $structure_items, 'structure', 'structuretool');?>
		</div>
		<div id='s-tab-weapons' class='itemdata'>
			<?= Item_Model::helper_structurelistitems($structure, $structure_items, 'structure', 'weapons');?>
		</div>
		<div id='s-tab-armors' class='itemdata'>
			<?= Item_Model::helper_structurelistitems($structure, $structure_items, 'structure', 'armors');?>
		</div>
		<div id='s-tab-clothes' class='itemdata'>
			<?= Item_Model::helper_structurelistitems($structure, $structure_items, 'structure', 'clothes');?>
		</div>
		<div id='s-tab-scrolls' class='itemdata'>
			<?= Item_Model::helper_structurelistitems($structure, $structure_items, 'structure', 'scrolls');?>
		</div>
		<div id='s-tab-others' class='itemdata'>
			<?= Item_Model::helper_structurelistitems($structure, $structure_items, 'structure', 'others');?>
		</div>
	</td>
</tr>	
</td>
</tr>
</table>
