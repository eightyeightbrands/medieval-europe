<script type="text/javascript">

$(document).ready(function()
{	
	$(".quantity").blur(function(event){	
		$(this).val($(this).val()); 
	}); 
	
	$('#checkallcharitems').click(function()
	{
		$('[name="charitemcheckbox"]').attr('checked', $('#checkallcharitems').is(':checked'));    
	}),		
	$('#masswithdrawal').click(function (e)
	{								
		e.preventDefault();
		var data = { 'items' : [], 'targetchar_id': []}; 
		var item = {};		
		var targetchar_id = $('[name=targetchar_id]').val();		
		
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
			
		data['targetchar_id'].push(targetchar_id);				
		$('input[name=itemstotransfer]').val( JSON.stringify(data));
		$('form#massitemtransfer').submit();
		
	})
})	
</script>

</head>

<div class="pagetitle"><?php echo kohana::lang('global.inventory')?></div>

<div id='helper'>
<?php echo kohana::lang('ca_loot.helper', $targetchar -> name ) ?>
</div>
<br/>
<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>
<br/>
<br/>

<?php
if ( count( $items['items'] ) == 0  )
{
?>
<p style='text-align:center;'><?php echo kohana::lang('items.noitemfound');?></p>
<?php
}
else
{
?>
	<div style='text-align:right'>
	<?php echo form::open('character/loot', array( 'id' => 'massitemtransfer' ) )?>
	<?php echo form::hidden('itemstotransfer') ?> 
	<?php echo form::hidden('targetchar_id', $targetchar -> id) ?> 
	<?php echo form::submit( 
		array( 
		'id' => 'masswithdrawal', 
		'value' => kohana::lang('structures.masswithdrawalitems'), 		
		)); 
	?>
	<?php echo form::close() ?>
	</div>
	
	<br/>

	<table>
	<th width="5%" class='center'><?php echo form::checkbox( array( 'id' => 'checkallcharitems' ))?></th>
	<th width="20%" colspan="2" ><?php echo kohana::lang('items.item') ?></th>
	<th width="5%" class='right'><?php echo kohana::lang('global.quantity') ?></th>
	<th width="20%" class='right'><?php echo kohana::lang('items.take_quantity') ?></th>
	<th width="15%" class='right'><?php echo kohana::lang('items.weight')?></th>	
	<th width="15%" class='right'><?php echo kohana::lang('character.inventory_totalweight')?></th>	
	<tbody>
	<?php 
	$r=0;
	foreach ($items['items'] as $item ) 
	{	
		$class = ( $r % 2 == 0 ) ? '' : 'alternaterow_1';
		$title = Utility_Model::createitemtooltip( $item ); 
		echo form::open('/character/loot');
		echo form::hidden('targetchar_id', $item -> character_id );
		echo form::hidden('item_id', $item -> item_id );		
		echo form::hidden('w-' . $item -> item_id , Utility_Model::number_format( $item -> totalweight/1000,3));
		echo form::hidden('sc-' . $item -> item_id , $item -> subcategory);
		echo "<tr class='$class'>";
		echo "<td class='center'>" . form::checkbox( array( 'id' => $item -> item_id, 'name' => 'charitemcheckbox', 'value' => $item -> item_id )) . "</td>";
		
		echo "<td>" . html::image( 'media/images/items/'. $item -> tag .'.png', 
				array('class' => 'size25')) . "</td>";
		echo  "<td class='desc' title='" . $title . "'>" . kohana::lang($item -> name) . "</td>";	
		echo "<td class='right'>" . $item -> quantity . "</td>";
		echo "<td class='right'>" .
				form::input( 
					array( 
						'class' => 'quantity', 
						'id' => 'q-'. $item -> item_id, 
						'name' => 'quantity', 
						'value' => $item -> quantity,
						'style' => 'margin:0px;padding:0px;width:40px;text-align:right' ) )
		. "</td>"; 
		echo "<td style='text-align:right'>" . Utility_Model::number_format( $item -> weight/1000,3) . " Kg. </td>"; 
		echo "<td style='text-align:right'>" . Utility_Model::number_format( $item -> totalweight/1000,1) . " Kg. </td>"; 
		echo "</tr>";
		echo form::close();
		$r++;
	}
	?>
	<tr><td colspan='7'><hr class="top10 bottom10"></td><tr>
	
	<tr>
		<td colspan='6' style='text-align:right'><?php echo kohana::lang('character.inventory_totalweight')?></td>
		<td class='right' colspan='1'><?php echo Utility_Model::number_format($items['totalitemsweight']/1000,1)?> Kg.</td>
	</tr>
	
	<tr>
		<td colspan='6' style='text-align:right'><?php echo kohana::lang('character.charleftweightcapacity')?></td>
		<td class='right' colspan='1'><?php echo Utility_Model::number_format($transportableweight/1000,1)?> Kg.</td>
	</tr>
	
	
	</tbody>
	</table>
	<?php } ?>
