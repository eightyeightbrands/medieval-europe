<div class="pagetitle"><?php echo kohana::lang('structures_barracks.armory_pagetitle')?></div>

<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<?php echo $submenu ?>

<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<div id='helper'>
<?= kohana::lang('structures_barracks.armory_lendreport_helper'); ?>
</div>

<div class='submenu'>
<?php echo html::anchor('barracks/armory/'. $structure->id, kohana::lang('structures_barracks.armory'));?>
<?php echo html::anchor('barracks/viewlends/'. $structure->id, kohana::lang('structures_barracks.lendsreport'), array('class' => 'selected' ));?>
<?php 
if ( !is_null ( $bonus ) )
	echo html::anchor('barracks/givearmoryaccess/'. $structure->id, kohana::lang('structures.manageaccess'));?>
</div>
<br/>
<?php 
if ( $lends -> count() == 0 )
	echo "<p class='center'>" . kohana::lang('structures_barracks.nolentitems') . '</p>' ;
else
{ 
?>

<?php echo $pagination -> render(); ?>

<br/>

<table class='small'>
<th class='center' width='5%'>Id</th>
<th class='center' width='20%'><?php echo kohana::lang('items.item')?></th>
<th class='center' width='15%'><?php echo kohana::lang('structures_barracks.lendtime')?></th>
<th class='center' width='15%'><?php echo kohana::lang('structures_barracks.lentby')?></th>
<th class='center' width='15%'><?php echo kohana::lang('global.receiver')?></th>
<th class='center' width='15%'><?php echo kohana::lang('structures_barracks.lenddeliverytime')?></th>

<?php 
$r = 0;
foreach ( $lends as $lend ) 
{ 
	$class = ( $r % 2 == 0 ) ? '' : 'alternaterow_1' ;
?>
	<tr class='<?php echo $class?>'>
		<td class='center'><?php echo $lend -> id; ?></td>
		<td class='center'><?php echo kohana::lang($lend -> item_name); ?></td>		
		<td class='center' ><?php echo Utility_Model::format_datetime($lend -> lendtime) ; ?></td>
		<td class='center' ><?php echo $lend -> lender ; ?></td>
		<td class='center' ><?php echo $lend -> char_name ; ?></td>
		<td class='center' ><?php echo Utility_Model::countdown($lend -> deliverytime) ; ?></td>				
	</tr>
<?php 
$r++;
} ?>
</table>
<br/>
<?php echo $pagination->render(); ?>
<?php } ?>


<br style="clear:both;" />
