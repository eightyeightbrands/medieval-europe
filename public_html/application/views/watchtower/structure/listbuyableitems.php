<div class="pagetitle"><?php echo kohana::lang("structures.listbuyableitems")?></div>

<?php echo $submenu ?>

<div id="helper"><?php echo kohana::lang('structures.listbuyableitems_helper'); ?></div>

<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<br/><br/>

<?php 
if ( count( $items ) == 0 )
	echo kohana::lang('items.noitemfound');
else
{
?>
<table style='font-size:12px' border=1>
<th width="5%"><?php echo kohana::lang('items.item') ?></th>
<th width="30%" class='center'><?php echo kohana::lang('global.description') ?></th>
<th width="5%" class='center'><?php echo kohana::lang('global.quantity') ?></th>
<th width="10%" class='center'><?php echo kohana::lang('global.price') ?></th>
<th width="10%"></th>

<?php
$r = 0;
foreach ( $items as $item ) 
{
$class = ( $r % 2 == 0 ) ? 'alternaterow_1' : '' ;
//var_dump( $item ); exit; 
?>

<?php 
echo form::open();
echo form::hidden( 'item_id', $item -> id );
echo form::hidden( 'structure_id', $structure -> id );
?>
<tr class='<?php echo $class?>'>
<td>
	<?php echo html::image(array('src' => 'media/images/items/'. $item -> tag . '.png'), 
			array( 'class' => 'size25'))?>
</td>
<td class='center'><?php echo kohana::lang($item -> name) ?> </td>
<td class='right'><?php echo $item -> quantity ?> </td>
<td class='center'><?php echo form::input( array ('id' => 'price', 'name' => 'price', 'value' => $item -> price, 'style' => 'width: 70px;text-align:right'))?></td>
<td class='center'>
<?php echo form::submit( array (
				'id' => 'submit', 
				'class' => 'submit', 			
				'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')' ), kohana::lang('global.set'));
?>				
</td>				
</tr>
<?php 
$r++;
} ?>
</table>

<?php } ?>

<br style="clear:both;" />
