<div class="pagetitle"><?php echo Kohana::lang('groups.edit_pagetitle');?></div>

<?php echo $submenu ?>

<?php	echo html::image(array('src' => 'media/images/template/hruler.png')); ?>

<div class="submenu">
	<?= $secondarymenu; ?>
</div>

<div id="helper"><?php echo Kohana::lang('groups.edit_helper', $group -> name); ?></div>


<?php echo form::open(url::current(), array('class'=>'creategroup_form')); ?>
<?php echo form::hidden( 'group_id', $group -> id ); ?>


<table>
	<tr>
		<td class='text-right'><?php echo form::label('group_name', Kohana::lang('global.name'));?></td>
		<td><?php echo form::input( array( 'name' => 'group_name', 'value' => $form['group_name'], 'style' => 'width:350px') ); ?>		
	</tr>
	<tr>
		<td class='text-right'><?php echo form::label('group_description', Kohana::lang('groups.create_description'));?></td>
		<td><?php echo form::textarea( array( 'name'=>'group_description', 'value' => $form['group_description'], 'rows' => 10, 'cols' => 70) ); ?>
	</tr>

	<tr>
		<td colspan=2 class="center"><?php 
		echo form::submit( array (			
			'class' => 'button button-medium', 			
			'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')' ), kohana::lang('global.save'));
		?></td>
	</tr>
</table>

<?php echo form::close(); ?>

<br style="clear:both;" />
