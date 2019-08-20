<script type='text/javascript'>

$(document).ready(function(){   
	 $("input[id^='quantity']").blur(function(event){				
			$v = $(this).attr("id").split("_");
			$item_id = $v[1];
     $("#totalprice_"+$item_id).text( $(this).attr("value") * $("#sellingprice_"+$item_id).text() );
   });
 });
</script>
</head>
<div class="pagetitle"><?php echo kohana::lang("structures.buyitems")?></div>

<div id="helper"><?php echo kohana::lang('structures.buyitems_helper'); ?></div>

<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<br/><br/>

<?php 
if ( count( $buyableitems ) == 0 )
	echo "<p class='center'>" . kohana::lang('items.noitemfound') . '</p>';
else
{
?>
<table style='font-size:12px'>
<th width="5%"><?php echo kohana::lang('items.item') ?></th>
<th width="30%" class='center'><?php echo kohana::lang('global.description') ?></th>
<th width="5%" class='center'><?php echo kohana::lang('global.quantity') ?></th>
<th width="10%" class='center'><?php echo kohana::lang('global.price') ?></th>
<th width="10%" class='center'><?php echo kohana::lang('global.quantity') ?></th>
<th width="10%" class='center'><?php echo kohana::lang('items.totalprice') ?></th>
<th width="10%"></th>

<?php
$r = 0;
foreach ( $buyableitems as $buyableitem ) 
{
$class = ( $r % 2 == 0 ) ? '' : 'alternaterow_1';
//var_dump( $item ); exit; 
?>

<?php 
echo form::open();
echo form::hidden( 'item_id', $buyableitem -> id );
echo form::hidden( 'structure_id', $structure -> id );
?>
<tr class='<?php echo $class?>'>
<td>
	<?php echo html::image(array('src' => 'media/images/items/'. $buyableitem -> tag . '.png'), 
			array( 'class' => 'size25'))?>
</td>
<td class='center'><?php echo kohana::lang($buyableitem -> name) ?> </td>
<td class='right'><?php echo $buyableitem -> quantity ?> </td>
<?php $price = Item_Model::compute_realprice( $buyableitem, $char, $vat ) ?>	
<td class='right'><div id='sellingprice_<?php echo $buyableitem -> id ?>'><?php echo $price ?></div></td>
<td class='center'>
<?php echo form::input( 
	array (
		'class' => 'input-xsmall right',
		'id'=> 'quantity_'. $buyableitem -> id,
		'name' => 'quantity', 
		'value' => 1))?>
</td>
<td class='right'><div id='totalprice_<?php echo $buyableitem -> id ?>'><?php echo $price * 1 ?></div></td>
<td class='center'>
<?php echo form::submit( array (
				'id' => 'submit', 
				'class' => 'submit', 			
				'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')' ), kohana::lang('global.buy'));
?>				
</td>				
</tr>
<?php 
$r++;
} ?>
</table>

<?php } ?>

<br style="clear:both;" />
