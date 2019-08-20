<div class="pagetitle"><?php echo kohana::lang('user.purchases') ?></div>

<?php echo $submenu ?>

<div id='helper'><?php echo kohana::lang('user.purchaseshelper') ?> </div>

<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>
<br/>

<? 
	if (count($purchases) == 0 )
	{
?>

<p class='center'> <?= kohana::lang('user.nopurchases'); ?></p>

<?
	}
	else
	{
?>

	<div class="pagination"><?php echo $pagination->render('extended'); ?></div>		

	<table>
	<th>Id</th>
	<th><?= kohana::lang('user.paymentmethod');?></th>
	<th><?= kohana::lang('global.doubloons');?></th>
	<th><?= kohana::lang('global.date');?></th>

	<? 
	$k = 0;
	foreach ( $purchases as $purchase )
	{
		($k % 2 == 0) ? $class = '' : $class = 'alternaterow_1';	
	?>
		<tr class='<?= $class; ?>'>
		<td class='center'>
			<?
			if ( $purchase -> item_name == 'gourl' )
			{
				echo html::anchor(
					'user/viewpurchasereceipt/'.$purchase->txn_id, 
					$purchase -> id,
					array('title' => kohana::lang('user.viewpurchasereceipt'))
					);
			}
			else
			{
				echo $purchase -> id;
			}
			?>
		</td>
		<td class='center'><?= ucfirst($purchase -> item_name); ?></td>
		<td class='right'	><?= $purchase -> quantity; ?></td>
		<td class='center'><?= $purchase -> timestamp; ?></td>
		</tr>
		
	<? 
		$k++;
	}
	?>
	</table>
	
	<div class="pagination"><?php echo $pagination->render('extended'); ?></div>		
	
<?
	}
?>	
	
<br style="clear:both;" />
