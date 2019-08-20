<div class="pagetitle"><?php echo kohana::lang('character.myquests_pagetitle') ?></div>

<br/>

<?php echo $submenu; ?>
<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<div id='helper'>
<?= kohana::lang('quests.myquestshelper'); ?>
</div>

<br/>

<table class='small border equalrows'>
<th class='center' width='10%' ><?php echo kohana::lang('global.path') ?></th>
<th class='center' width='25%' ><?php echo kohana::lang('global.name') ?></th>
<th class='center' width='25%' ><?php echo kohana::lang('global.description') ?></th>
<th class='center' width='25%' ><?php echo kohana::lang('quests.rewards') ?></th>
<th width='10%' ><?php echo kohana::lang('global.status') ?></th>
<th width='5%' class='center'></th>

<?php 
$k = 0;
foreach ($infos as $info )
{
$class = ( $k % 2 == 0 ) ? 'alternaterow_1' : 'alternaterow_2' ;
?>
<tr class='<?php echo $class?>'>
<td width="5%" class='center' ><?php echo $info['path'] ?></td>
<td width="10%" class='center' >
<?php
	if ( $info['status'] == 'active' )
		echo html::anchor( 'quests/view/' . $info['name'], $info['descriptivename'] );
	else
		echo $info['descriptivename'];
?>
</td>
<td width="30%" class='center' >
<?php echo $info['description'] ?>

</td>

<td class='center'>
<?php echo kohana::lang($info['rewards']) ?>
</td>

<td width="10%" class='center' ><?php echo kohana::lang('global.status_' . $info['status']) ?></td>
</td>
<td class='center'>
<?
if ( $info['status'] == 'inactive' )
	echo html::anchor('quests/activate/' . $info['name'], 'Activate', array('class' => 'button'));
?>
</td>
</tr>

<?php
$k++;
}
?>


</table>

<br style="clear:both;" />
