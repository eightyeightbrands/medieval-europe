<div class="pagetitle"><?php echo kohana::lang('character.bonuspurchases') ?></div>

<?php echo $submenu ?>

<div id='helper'><?php echo kohana::lang('bonus.bonus-purchaseshelper') ?> </div>

<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<div>		

<?php
if ($purchasedbonuses -> count() == 0 )
	echo "<p class='center' style='padding:30px 0px'>".kohana::lang('bonus.bonus-nobonuspurchases') . "</p>";
else
{
?>
	<div class="pagination"><?php echo $pagination->render('extended'); ?></div>			
	<table class='small'>			
	<th class='center' width='30%'><?php echo kohana::lang('bonus.bonus-type') ?></th>
	<th class='center' width='20%'><?php echo kohana::lang('bonus.targetchar') ?></th>
	<th class='center' width='20%'><?php echo kohana::lang('bonus.bonus-start') ?></th>
	<th class='center' width='20%'><?php echo kohana::lang('bonus.bonus-end') ?></th>
	<th class='center' width='5%'><?php echo kohana::lang('bonus.bonus-active') ?></th>
	<th class='center' width='10%'><?php echo kohana::lang('items.doubloon_name')?></th>							
	<?php
	$i=0;
	$total = 0;
	foreach ( $purchasedbonuses as $b )
	{		
		($i % 2 == 0) ? $class = '' : $class = 'alternaterow_1';	
		if ($b -> cutunit == 'quantity')
		{
			$active = 'notapplicable' ;			
		}
		else
		{
			$active = ( time() > $b -> starttime and time() < $b -> endtime ) ? 'yes' : 'no' ;				
		}
	
		if ($active == 'yes' ) 
			$style = 'color:darkgreen;font-weight:bold';
		else
			$style = '';
	?>

		<tr class='<?php echo $class; ?>'>
		<td class ='center'> <?php echo kohana::lang('bonus.'. $b -> name . '_name'); ?></td>
		<td class ='center'> <?php echo Character_Model::create_publicprofilelink( null, $b -> targetcharname ) ?></td>
		<td class ='center'> <?php echo date('d/m/Y H:i:s', $b -> starttime); ?></td>
		<td class ='center'> <?php echo date('d/m/Y H:i:s', $b -> endtime); ?></td>
		<td class ='center' style="<?=$style?>"> <?php echo kohana::lang('global.' . $active ); ?></td>
		<td class ='center'> <?php echo $b -> doubloons; ?></td>
		</tr>
	<?php
		$i++;
	}
	?>
	</table>
			
	<div class="pagination"><?php echo $pagination->render('extended'); ?></div>
<?php 
} 
?>	
</div>

<br style="clear:both;" />
