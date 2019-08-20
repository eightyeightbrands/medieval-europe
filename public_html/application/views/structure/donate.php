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
	$('#massdeposit').click(function (e)
	{				
		e.preventDefault();
		var data = { 'items' : [], 'structureid': []}; 
		var item = {};
		var	structureid = $('[name=structure_id]').val();
		
		if ( this.id == 'massdeposit' )
		{
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
			data['action'] = 'donate';
		}
		
		data['structureid'].push(structureid);
		
		$('input[name=itemstotransfer]').val( JSON.stringify(data));
		$('form#massitemtransfer').submit();
		
	})
}
);
</script>

<div class="pagetitle"><?php echo $structure -> getName() . ' - ' . kohana::lang("global.drop")?></div>
<div id="helper"><?php 
	echo kohana::lang('structures.generic_drophelper');
	echo kohana::lang('structures.carryingweightcapacityleft', 
		Utility_Model::number_format( $char_transportableweight/1000) );
	echo kohana::lang('structures.storableweight', Utility_Model::number_format( $structure_storableweight/1000));			
?>
</div>

<br/>
<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>
<br/>
<br/>

<?php 

if ( count ( $items['items']['all'] ) == 0 )
	echo kohana::lang('items.noitemfound');
else
{
?>
<table>
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
	<?php echo form::close() ?>
</tr>
</table>
<br/>
<table style='font-size:12px'>
<th width="5%"><?php echo form::checkbox( array( 'id' => 'checkallcharitems' ))?></th>
<th width="30%" colspan="2"><?php echo kohana::lang('items.item') ?></th>
<th width="10%" class='right'><?php echo kohana::lang('global.quantity') ?></th>
<th width="20%" class='right'><?php echo kohana::lang('items.weight') ?></th>
<th width="10%" class='right'><?php echo kohana::lang('items.drop_quantity') ?></th>
<th width="10%"></th>
<?php 

$r = 0;
foreach ($items['items']['all'] as $item) 
{

	$class = ( $r % 2 == 0 ) ? '' : 'alternaterow_1' ;
	
	if ( $item -> equipped == 'unequipped' )
	{
		echo form::open('/structure/donate');
		echo form::hidden('structure_id', $structure -> id );
		echo form::hidden('item_id', $item -> item_id );
		echo form::hidden('w-' . $item -> item_id , $item -> weight);
		echo form::hidden('sc-' . $item -> item_id , $item -> subcategory);
		
		$title = Item_Model::helper_tooltip( $item, $structure -> id ); 
		
		echo "<tr class='$class'>";				
		echo "<td>" . form::checkbox( array( 'id' => $item -> item_id, 'name' => 'charitemcheckbox', 'value' => $item -> item_id )) . "</td>";
		echo "<td width='5%'>". html::image(array('src' => 'media/images/items/'.$item -> tag.'.png'), 
			array( 'class' => 'size25')) . "</td>";

		// Se l'oggetto è uno scroll generico allora visualizzo il titolo al posto del
		// nome dell'oggetto

		if ( $item -> category=='scroll' && $item -> subcategory=='generic')
		{ echo "<td style='text-align:left' title= '$title' >".$item -> param1."</td>"; } 
		else
		{ echo "<td style='text-align:left' title= '$title' >".kohana::lang($item -> name) . "</td>"; }	

		
		echo "<td class='right'>". $item -> quantity . "</td>";	
		echo "<td class='right'>". Utility_Model::number_format( $item -> totalweight / 1000, 1) . " Kg</td>";
		
		echo "<td class='right'>" . form::input( 
			array( 
				'class' => 'quantity', 
				'id' => 'q-'. $item -> item_id,
				'name' => 'quantity', 
				'value' => $item -> quantity,
				'style' => 'margin:0px;padding:0px;width:40px;text-align:right' ) )		
		. "</td>"; 
		echo "<td class='right'>" . 
			form::submit( array (
				'id' => 'submit', 
				'class' => 'submit', 			
				 'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')'
				 ), 
		kohana::lang('global.drop'))."</td>";
		echo "</tr>";
		echo form::close();
		$r++;
	}
	
}
?>
</table>
<?php } ?>

<br style="clear:both;" />
