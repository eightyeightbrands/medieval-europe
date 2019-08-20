<div class="pagetitle"><?php echo kohana::lang('gameevents.allgameevents') ?></div>

<div id='helper'><?= kohana::lang('gameevents.gameevents_helper'); ?></div>

<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>
<br/>
<br/>
<table>
<th width='5%' class='center' >ID</th>
<th width='20%' class='center' ><?= kohana::lang('global.name'); ?></th>
<th width='10%' class='center' ><?= kohana::lang('gameevents.subscriptionstartdate'); ?></th>
<th width='10%' class='center' ><?= kohana::lang('gameevents.subscriptionenddate'); ?></th>
<th width='50%' class='center' ><?= kohana::lang('global.description'); ?></th>
<th></th>

<? 
$k = 0;
foreach ($gameevents as $gameevent) {
$class = ($k%2==0) ? '' : 'alternaterow_1';
	?>
<tr class='<?= $class; ?>'>
<td class='center'><?= $gameevent->id; ?></td>
<td class='center'><?= $gameevent->name; ?></td>
<td class='center'><?=Utility_Model::format_datetime($gameevent->subscriptionstartdate) ?></td>
<td class='center'><?=Utility_Model::format_datetime($gameevent->subscriptionenddate)	 ?></td>
<td class='left'><?= Utility_Model::truncateHtml(
		$gameevent->description, 
		$length = 100, 
		$ending = '...', 
		$exact = false, 
		$considerHtml = false) ?></td>
<td class='center'>
	<?= html::anchor('gameevent/view/' . $gameevent ->id, 
		'Details') ?>
</td>	

</tr>
<? 
$k++;
} ?>
</table>

<br style='clear:both'/>
