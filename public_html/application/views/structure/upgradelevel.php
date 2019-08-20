<div class="pagetitle"><?php echo $structure -> getName()?></div>

<?php echo $submenu?>
<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>



<div id='helper'>
 	<?php  echo kohana::lang('structures.upgradelevel_helper'); ?>
</div>

<br/>

<? if ( $structure -> status == 'upgrading') 
{
?>
<p class='center'>
	
	<?= kohana::lang('structures.info-structurecurrentlyupgradingtolevel', ($upgradeinfo['currentlevel']+1)); ?>
</p>

<? 
}
elseif ($upgradeinfo['currentlevel'] == $structure -> getMaxlevel() ) 
{
?>

<p class='center'>
	<?php echo kohana::lang('structures.maxlevelreached') ?>
</p>

<?
}
else
{
?>
<table style='width:80%;margin:auto'>
<tr class='alternaterow_1'>
	<td><?= kohana::lang('structures.currentlevel');?></td> 
	<td>
	<span class='value'><?= $upgradeinfo['currentlevel']; ?></span>
	/ <span class='value'><?= $upgradeinfo['maxlevel']; ?></span>
	</td>
</tr>
<tr class='alternaterow_2'>
<td><?= kohana::lang('structures.hoursfornextlevel'); ?></td>
<td>
<span class='value'><?= $upgradeinfo['hours'];?></span>
</td>
</tr>
<tr class='alternaterow_1'>
<td><?= kohana::lang('structures.neededmaterialfornextlevel'); ?></td>

<td>
<? 
foreach ($upgradeinfo['neededmaterialfornextlevel'] as $material => $quantity )
{
	$_text[] = $quantity . '&nbsp;' . kohana::lang('items.' . $material . '_name');
}
echo "<span class='value'>" . implode ( ', ', $_text) . "</span>";
?>
</td>
</tr>
<tr class='alternaterow_2'>
<td><?= kohana::lang('structures.upgradebonus'); ?></td>

<td>
<span class='value'><?= kohana::lang('structures.upgradebonus_' . $structure -> getParenttype() . '_' . ($upgradeinfo['currentlevel']+1)); ?></span>	
</td>
</tr>
</table>

<br/>
<div class='center'>
<?php echo form::open();?>
<?= form::hidden('structure_id', $structure -> id); ?>
<?php 
echo form::submit( array (
			'id' => 'submit', 
			'class' => 'button button-medium',			
			'name' => 'upgradelevel', 
			'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')' ), kohana::lang('structures.upgradelevel'));
?>
<?php echo form::close(); ?>
</div>
</fieldset>

<? } ?>

<br style= 'clear:both'/>

