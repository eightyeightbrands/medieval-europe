<fieldset>
<legend><?= kohana::lang('structures.change_name');?></legend>
<div class='center'>
<?php echo form::open(url::current()); ?>
<?php echo form::hidden('structure_id', $structure -> id ); ?>
<?php echo form::label('slogan', Kohana::lang('global.name'));?>
&nbsp;
<?php echo form::input( 
	array( 
		'name' => 'name', 
		'value' => $form['name'], 
		'class' => 'input-large', 
		'maxlength' => 50)); 
?>
&nbsp;
<?php 
echo form::submit( array (
			'id' => 'submit',
			'name' => 'setstructurename',
			'class' => 'button button-medium', 			
			'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')' ), kohana::lang('global.edit'))."</td>";
?>
<?php echo form::close(); ?>
</center>

<br style="clear:both;" />
</fieldset>