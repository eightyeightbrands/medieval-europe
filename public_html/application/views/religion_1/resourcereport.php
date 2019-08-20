<div class="pagetitle"><?php echo kohana::lang('structures_religion_1.resourcereport_pagetitle') ?></div>

<?php echo $submenu ?>
<?php	echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<div id='helper'><?php echo kohana::lang('structures_religion_1.resourcereport_helper');?></div>


<br/>

<table class='small'>
<th width="40%"><?= kohana::lang('global.type'); ?></th>
<th><?= kohana::lang('global.regions'); ?></th>
<th><?= kohana::lang('global.owner'); ?></th>
<th><?= kohana::lang('items.silvercoin_name'); ?></th>
<th><?= kohana::lang('religion.faithpoints'); ?></th>
<?php 

	$r = 0;
	$totalsilvercoins = $totalfaithpoints = 0;
	foreach ( $info as $record )
	{	
		$class = ( $r % 2 == 0 ) ? 'alternaterow_1' : 'alternaterow_2';
		$totalsilvercoins += $record['silvercoins'];
		$totalfaithpoints += $record['faithpoints'];
?>

	<tr class='<?= $class; ?>'>
	<td><?= kohana::lang($record['structure_name']); ?></td>
	<td class="center"><?= kohana::lang($record['region_name']) ; ?></td>
	<td class="center"><?= Character_Model::create_publicprofilelink( $record['owner_id'], null  );  ?></td>
	<td class="right"><?= $record['silvercoins'] ; ?></td>
	<td class="right"><?= $record['faithpoints'] ; ?></td>
	</tr>

<?php
		$r++;
	}
?>
	<tr>
		<td colspan='3'></td>
		<td class='right'><?= kohana::lang('global.total'); ?>&nbsp;<span class='value'><?=$totalsilvercoins; ?></span></td>
		<td class='right'><?= kohana::lang('global.total'); ?>&nbsp;<span class='value'><?=$totalfaithpoints; ?></span></td>
	</tr>
</table>


<br style='clear:both'/>
