<div class="pagetitle"><?php echo Kohana::lang('groups.create_pagetitle') ?></div>

<?php echo $submenu ?> 
<div class="submenu">
	<?= $secondarymenu; ?>
</div>
<br/>

<div id="helper"><?php echo Kohana::lang('groups.create_helper'); ?></div>

<?php echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<?php echo form::open(url::current(), array('class'=>'creategroup_form')); ?>

<table>
	<tr>
		<td width="140px"><?php echo form::label('group_name', Kohana::lang('groups.create_name'));?></td>
		<td><?php echo form::input('group_name',($form['group_name']), 'style="width:300px"' );?>
		<?php if (!empty ($errors['group_name'])) echo "<div class='error_msg'>".$errors['group_name']."</div>";?></td>
	</tr>

	<tr>
		<td><?php echo form::label('group_description', Kohana::lang('groups.create_description'));?></td>
		<td><?php echo form::textarea( array( 'name'=>'group_description', 'value' => $form['group_description'], 'rows' => 4, 'cols' => 70) ); ?>
		<?php if (!empty ($errors['group_description'])) echo "<div class='error_msg'>".$errors['group_description']."</div>";?></td>
	</tr>

	<tr>
		<td><?php echo form::label('type', Kohana::lang('groups.type'));?></td>
		<td><?php echo form::dropdown('type', $combo_type, $form['type']);?></td>
	</tr>

	<tr>
		<td><?php echo form::label('secret', Kohana::lang('groups.create_secret'));?></td>
		<td><?php echo form::dropdown('secret', $combo_secret, $form['secret']);?></td>
	</tr>
	
	<tr>
		<td colspan=2 class="center" style="padding-top:10px;"><?php 
		echo form::submit( array (
			'id' => 'submit', 
			'class' => 'button button-medium', 			
			'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')' ), kohana::lang('groups.create_a_group'));
		?></td>
	</tr>
</table>

<?php echo form::close(); ?>

<br style="clear:both;" />
