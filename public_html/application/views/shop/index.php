<div class="pagetitle"><?php echo kohana::lang('structures.shop_list')?></div>

<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<div id='helper'>
  <div id='helpertext'>
		<?php echo kohana::lang('structures_shop.shophelper')?>
	</div>
	<div id='wikisection'>
		<?php echo html::anchor( 
			'https://wiki.medieval-europe.eu/index.php?title=Workshops',
			kohana::lang('global.wikisection'), 
			array( 'target' => 'new', 'class' => 'button' ) ) 
		?>
	</div>
	<div style='clear:both'></div>
</div>

<br/>

<table>
<th width="10%" class='center'><?php echo kohana::lang('structures.shop_name');?></th>
<th width="35%" class='center'><?php echo kohana::lang('structures.shop_description');?></th>
<th width="25%" class='center'><?php echo kohana::lang('structures.shop_price');?></th>
<th width="10%" class='center'></th>

<?php
$k = 0;
foreach ( $shops as $shop )
{
	$class = ( $k % 2 == 0 ) ? 'alternaterow_1' : 'alternaterow_2';	
	$structureinstance = StructureFactory_Model::create( $shop -> type );
?>
	<tr class='<?= $class; ?>'>
	<td class='center'><?= html::image('media/images/structures/'. $structureinstance -> structure_type -> image, 
	array(
		'class' => 'border size75',
	));?>
	</td>
	<td class='left'>
	<b><?= $structureinstance -> getName(); ?></b>
	<br/>
	<?= $structureinstance -> getDescription(); ?>
	</td>
	<td class='center'>
	<?= $structureinstance->getPrice($char, $region); ?>
	</td>
	<td class='center'>
	<?= html::anchor( 
			'/structure/buy/' . $structureinstance -> structure_type -> type, 
			kohana::lang('global.buy'),
			array( 
				'class' => 'button button-medium', 
				'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')' )
			);
	?>
</td>	
	
<?
$k++;
}
?>
</table>

<br style="clear:both;" />
