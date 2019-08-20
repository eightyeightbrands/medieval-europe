<div class="pagetitle"><?php echo kohana::lang('structures_house.house_list')?></div>

<?php echo html::image('media/images/template/hruler.png'); ?>

<div id='helper'><?php echo kohana::lang('structures_house.helper'); ?></div>

<br/>

<table>
<th colspan='2' width='30%'><?php echo kohana::lang('structures_house.name');?></th>
<th width='10%' class='center'><?php echo kohana::lang('structures_house.price');?></th>
<th width='10%' class='center'><?php echo kohana::lang('structures_house.holdcapacity');?></th>
<th width='20%' class='center'><?= kohana::lang('structures.energyrestoredperhour');?></th>
<th width='10%'></th>

<?php
$k = 0;

foreach ( $houses as $house )
{
	$structureinstance = StructureFactory_Model::create( $house -> type ); 
	$class = ( $k % 2 == 0 ) ? 'alternaterow_1' : 'alternaterow_2';
?>

<tr class="<?= $class; ?>">
<td><?= html::image('media/images/structures/'. $structureinstance -> structure_type -> image, 
	array(
		'class' => 'border size75',
	));?>
</td>
<td class='center'><?= kohana::lang($structureinstance -> structure_type -> name); ?></td>
<td class='center'><?= $structureinstance -> getPrice( $char, $region ); ?></td>
<td class='center'><?= $structureinstance -> getStorage() / 1000; ?> Kg.</td>
<td class='center'><?= $structureinstance -> getRestfactor(); ?>%</td>
<td>
	<?= html::anchor( 
			'/structure/buy/' . $structureinstance -> structure_type -> type, 
			kohana::lang('global.buy'),
			array( 
				'class' => 'button button-small', 
				'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')' )
			);
	?>
</td>	
</tr>

<?
$k++;
}
?>
</table>

<br style="clear:both;" />
