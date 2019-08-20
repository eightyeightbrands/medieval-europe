<div class="pagetitle"><?php echo kohana::lang('user.edit_pagetitle') ?></div>

<?php echo $submenu ?>

<div id='helper'><?php echo kohana::lang('user.edit_helper')?></div>

<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<br/>
<center>
<table border="0" width="80%">
<tr><td align="right"><?php echo form::open() ?></td></tr>
<tr><td align="right"><?php echo form::label('old_password', Kohana::lang('global.old_password'));?></td><td align="left"><?php echo form::password('old_password', '', 'style="width:200px"');?></td></tr>
<?php if (!empty ($errors['old_password'])) echo "<tr><td></td><td colspan='2'><div class='error_msg'>".$errors['old_password']."</div></td></tr>";?>
<tr><td align="right"><?php echo form::label('password', Kohana::lang('global.password'));?></td><td align="left"><?php echo form::password('password', '', 'style="width:200px"');?></td></tr>
<?php if (!empty ($errors['password'])) echo "<tr><td></td><td colspan='2'><div class='error_msg'>".$errors['password']."</div></td></tr>";?>
<tr><td align="right"><?php echo form::label('password_confirm', Kohana::lang('global.password_confirm'));?></td><td align="left"><?php echo form::password('password_confirm', '', 'style="width:200px"');?></td></tr>
<?php if (!empty ($errors['password_confirm'])) echo "<tr><td></td><td colspan='2'><div class='error_msg'>".$errors['password_confirm']."</div></td></tr>";?>
<tr><td colspan="2" style="text-align:center">
<?php 
echo form::submit( array (
			'id' => 'submit', 
			'class' => 'submit', 			
			'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')' ), kohana::lang('global.edit'))."</td>";
?>
</td></tr>
<?php echo form::close(); ?>
</table>
</center>

<br style="clear:both">

