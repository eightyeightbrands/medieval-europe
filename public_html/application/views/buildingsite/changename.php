<div class="pagetitle"><?php echo Kohana::lang("structures.change_name")?></div>

<?php echo $submenu; ?>

<fieldset>
<div id="helper"><?php echo Kohana::lang("structures.change_name_helper") ?></div>

<?php echo form::open(url::current()); ?>
<?php echo form::hidden('structure_id', $structure -> id ); ?>
<?php echo form::label('slogan', Kohana::lang('global.name'));?>
&nbsp;
<?php echo form::input( array( 
	'name'=>'name', 
	'value' => $form['name'], 
	'class' => 'input-large',
	'maxlegth' => 50)
);
?>
<?php if (!empty ($errors['name'])) echo "<div class='error_msg'>".$errors['name']."</div>";?>
<br/><br/>
<?php 
echo form::submit( array (
			'id' => 'submit', 
			'class' => 'submit', 			
			'onclick' => 'return confirm(\''.kohana::lang('global.confirm_operation').'\')' ), kohana::lang('global.edit'))."</td>";
?>
<?php echo form::close(); ?>

</fieldset>


